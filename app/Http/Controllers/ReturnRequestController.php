<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderReturn;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $adminId = $user->role === 'admin' ? $user->id : $user->admin_id;
        
        $query = OrderReturn::with(['order', 'user', 'deliveryBoy'])
            ->whereHas('order', function($q) use ($adminId) {
                $q->where('admin_id', $adminId);
            });

        if ($user->role === 'seller') {
            $sellerId = $user->id;
            $query->whereHas('order', function($q) use ($sellerId) {
                $q->whereJsonContains('items_json', ['seller_id' => (int)$sellerId]);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->orderBy('created_at', 'desc')->paginate(15);

        $deliveryBoys = \App\Models\User::where('role', 'delivery_boy')
            ->whereHas('applications', function($q) use ($user) {
                $q->where('store_owner_id', $user->id)
                  ->where('status', 'approved');
            })->get(['id', 'name']);

        if ($user->role === 'seller' && $deliveryBoys->isEmpty()) {
            $deliveryBoys = \App\Models\User::where('role', 'delivery_boy')
                ->whereHas('applications', function($q) use ($user) {
                    $q->where('store_owner_id', $user->admin_id)
                      ->where('status', 'approved');
                })->get(['id', 'name']);
        }

        // Pre-build JSON for the details modal
        $returnsJson = $returns->map(function($return) {
            return [
                'id' => $return->id,
                'order_id' => $return->order_id,
                'customer_name' => $return->user->name,
                'customer_email' => $return->user->email,
                'reason' => $return->reason,
                'status' => $return->status,
                'items' => $return->order->items_json,
                'total_price' => $return->order->total_price,
                'date' => $return->created_at->format('M d, Y h:i A'),
                'delivery_boy_id' => $return->delivery_boy_id,
                'delivery_boy_name' => $return->deliveryBoy?->name ?? 'Not Assigned',
                'pickup_image' => $return->pickup_image ? asset($return->pickup_image) : null,
                'store_image' => $return->store_image ? asset($return->store_image) : null,
                'buyer_address' => $return->order->address ?? $return->user->info->address ?? 'N/A',
                'rejection_reason' => $return->rejection_reason,
            ];
        });

        $viewPrefix = $user->role === 'seller' ? 'seller' : 'admin';
        return view("$viewPrefix.returns", compact('returns', 'returnsJson', 'deliveryBoys'));
    }

    public function assignPartner(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);

        $request->validate([
            'delivery_boy_id' => 'required|exists:users,id'
        ]);

        $return->update([
            'delivery_boy_id' => $request->delivery_boy_id,
            'assignment_type' => Auth::user()->role === 'admin' ? 'admin' : 'seller'
        ]);

        // Notify delivery boy
        $deliveryBoy = \App\Models\User::find($request->delivery_boy_id);
        if ($deliveryBoy) {
            $deliveryBoy->notify(new \App\Notifications\ReturnAssignedNotification($return));
        }

        // Mark notification as read
        $admin = Auth::user();
        $admin->unreadNotifications()
            ->where('type', 'App\Notifications\OrderReturnNotification')
            ->where('data->order_id', $return->order_id)
            ->get()
            ->markAsRead();

        return response()->json(['success' => true, 'message' => 'Delivery partner assigned for pickup!']);
    }

    public function updateStatus(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);
        
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,completed',
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $return->status = $request->status;
        if ($request->status === 'rejected') {
            $return->rejection_reason = $request->rejection_reason;
            
            // Mark notification as read
            Auth::user()->unreadNotifications()
                ->where('type', 'App\Notifications\OrderReturnNotification')
                ->where('data->order_id', $return->order_id)
                ->get()
                ->markAsRead();
        }
        $return->save();

        // Notify Buyer via Email
        try {
            \Illuminate\Support\Facades\Mail::to($return->user->email)
                ->send(new \App\Mail\ReturnStatusMail($return));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Return Status Email Failed: " . $e->getMessage());
        }

        // If approved, we might want to automatically update the order status or process refund
        // But for now, just updating the return request status as requested.

        return response()->json(['success' => true, 'message' => 'Return request status updated.']);
    }
}
