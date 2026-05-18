<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\Product;
use App\Mail\OrderStatusMail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $adminId = auth()->user()->role === 'admin' ? auth()->id() : auth()->user()->admin_id;
        $query = Order::with(['user', 'deliveryBoy', 'delivery'])->where('admin_id', $adminId);

        if (auth()->check() && auth()->user()->role === 'seller') {
            $sellerId = auth()->id();
            // Additionally filter by seller items inside the order if needed, 
            // but for now admin_id scopes it to the store.
            $query->whereJsonContains('items_json', ['seller_id' => (int)$sellerId]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        $deliveryBoys = \App\Models\User::where('role', 'delivery_boy')
            ->whereHas('applications', function($q) {
                $q->where('store_owner_id', Auth::id())
                  ->where('status', 'approved');
            })->get(['id', 'name']);

        // If a seller has no delivery boys of their own, let them assign their admin's delivery boys
        if (auth()->check() && auth()->user()->role === 'seller' && $deliveryBoys->isEmpty()) {
            $deliveryBoys = \App\Models\User::where('role', 'delivery_boy')
                ->whereHas('applications', function($q) {
                    $q->where('store_owner_id', auth()->user()->admin_id)
                      ->where('status', 'approved');
                })->get(['id', 'name']);
        }

        // Pre-build JSON for the details modal (avoids Blade parse issues with closures)
        $ordersJson = $orders->map(function($order) {
            return [
                'id' => $order->id,
                'customer_name' => $order->user->name,
                'customer_email' => $order->user->email,
                'items' => $order->items_json,
                'total_price' => $order->total_price,
                'total_items' => $order->total_items,
                'status' => $order->status,
                'pickup_image' => $order->delivery?->pickup_image ? asset($order->delivery->pickup_image) : null,
                'delivery_image' => $order->delivery?->delivery_image ? asset($order->delivery->delivery_image) : null,
                'secret_code' => $order->delivery?->secret_code,
                'delivery_boy_id' => $order->delivery_boy_id,
                'delivery_boy_name' => $order->deliveryBoy?->name ?? 'Not Assigned',
                'assignment_type' => $order->assignment_type,
                'date' => $order->created_at->format('M d, Y h:i A'),
            ];
        });

        $viewPrefix = auth()->check() && auth()->user()->role === 'seller' ? 'seller' : 'admin'; return view("$viewPrefix.orders", compact('orders', 'ordersJson', 'deliveryBoys'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('user')->findOrFail($id);
        
        $request->validate([
            'status' => 'required|string|in:completed,shipped,delivered,cancelled,processing'
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;

        // If status changed to delivered, process delivery partner payment
        if ($oldStatus !== 'delivered' && $request->status === 'delivered') {
            $order->processDeliveryPayment();
        }

        $order->save();

        // If status changed to cancelled, restore stock
        if ($oldStatus !== 'cancelled' && $request->status === 'cancelled') {
            if (is_array($order->items_json)) {
                foreach ($order->items_json as $item) {
                    $product = null;
                    if (isset($item['id'])) {
                        $product = Product::find($item['id']);
                    }
                    if (!$product && isset($item['name'])) {
                        $product = Product::where('name', $item['name'])->first();
                    }
                    if ($product) {
                        $product->stock_quantity += $item['qty'];
                        $product->save();
                    }
                }
            }
        }

        // Send email notification to the customer if the status actually changed
        if ($oldStatus !== $request->status) {
            try {
                Mail::to($order->user->email)->send(new OrderStatusMail($order, $request->status));
            } catch (\Exception $e) {
                // Log the error but don't block the status update
                \Log::error('Failed to send order status email: ' . $e->getMessage());
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated, but email notification failed to send.'
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Order status updated & customer notified via email.']);
    }

    public function assignPartner(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'delivery_boy_id' => 'required|exists:users,id'
        ]);

        $oldStatus = $order->status;

        $order->update([
            'delivery_boy_id' => $request->delivery_boy_id,
            'status' => 'processing', // Automatically move to processing when assigned
            'assignment_type' => 'admin'
        ]);

        if ($oldStatus !== 'processing') {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)
                    ->send(new \App\Mail\OrderStatusMail($order, 'processing'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Assign Partner Email Failed: " . $e->getMessage());
            }
        }

        try {
            \Illuminate\Support\Facades\Mail::to($order->deliveryBoy->email)
                ->send(new \App\Mail\WorkAssignedMail($order));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Delivery Boy Assigned Email Failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Delivery partner assigned successfully!']);
    }
    public function refundOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Only cancelled orders can be refunded.']);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. Process Stripe Refund if payment_intent_id exists
            if ($order->payment_intent_id) {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                \Stripe\Refund::create([
                    'payment_intent' => $order->payment_intent_id,
                ]);
            }

            // 2. Update Internal Balances (Optional, depending on if you still want to track)
            $buyer = $order->user;
            $admin = \Illuminate\Support\Facades\Auth::user();

            if ($buyer) {
                // Add money back to buyer balance (virtual wallet)
                $buyer->increment('balance', $order->total_price);
                
                // Subtract from admin balance
                $admin->decrement('balance', $order->total_price);
            }

            $order->update(['status' => 'refunded']);

            // Mark notification as read
            Auth::user()->unreadNotifications()
                ->where('type', 'App\Notifications\OrderCancelledNotification')
                ->where('data->order_id', $order->id)
                ->get()
                ->markAsRead();

            // 3. Inform User via Email
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)
                    ->send(new \App\Mail\OrderStatusMail($order, 'refunded'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Refund Email Failed: " . $e->getMessage());
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true, 'message' => 'Refund processed via Stripe successfully!']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Stripe Refund Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Stripe Refund Failed: ' . $e->getMessage()]);
        }
    }
}
