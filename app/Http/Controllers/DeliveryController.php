<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\DeliveryApplication;
use App\Notifications\DeliveryApplicationNotification;
use App\Models\Order;

use App\Models\OrderReturn;

class DeliveryController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role' => 'delivery_boy'], $request->remember)) {
            $request->session()->regenerate();
            return redirect()->route('delivery.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or you are not a delivery partner.',
        ])->onlyInput('email');
    }

    public function dashboard(Request $request)
    {
        if (Auth::user()->role !== 'delivery_boy') {
            return redirect('/');
        }

        $stats = [
            'total_income' => Auth::user()->balance,
            'delivered_count' => Order::where('delivery_boy_id', Auth::id())->where('status', 'delivered')->count(),
            'returned_count' => OrderReturn::where('delivery_boy_id', Auth::id())->where('status', 'completed')->count(), 
            'active_deliveries' => Order::where('delivery_boy_id', Auth::id())->whereIn('status', ['processing', 'shipped'])->count()
        ];

        $recentReturns = OrderReturn::where('delivery_boy_id', Auth::id())
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(3)
            ->get();

        // Chart Data Calculation
        $timeRange = $request->query('range', '1M');
        $startDate = match($timeRange) {
            '1D' => now()->startOfDay(),
            '5D' => now()->subDays(4)->startOfDay(),
            '1M' => now()->subDays(29)->startOfDay(),
            '1Y' => now()->subMonths(11)->startOfMonth(),
            default => now()->subDays(29)->startOfDay(),
        };

        $format = ($timeRange === '1Y') ? 'Y-m' : (($timeRange === '1D') ? 'H:00' : 'Y-m-d');
        
        $deliveries = Order::where('delivery_boy_id', Auth::id())
            ->where('status', 'delivered')
            ->where('delivered_at', '>=', $startDate)
            ->get();

        $returns = OrderReturn::where('delivery_boy_id', Auth::id())
            ->where('status', 'completed')
            ->where('updated_at', '>=', $startDate)
            ->get();

        $app = DeliveryApplication::where('delivery_boy_id', Auth::id())->where('status', 'approved')->first();
        $fee = $app ? $app->delivery_fee : 0;

        $chartLabels = [];
        $chartIncome = [];
        $chartCount = [];
        $chartReturnCount = [];

        // Pre-fill labels
        $current = clone $startDate;
        $now = now();
        
        while ($current <= $now) {
            $key = $current->format($format);
            $chartLabels[] = $key;
            $chartIncome[$key] = 0;
            $chartCount[$key] = 0;
            $chartReturnCount[$key] = 0;
            
            if ($timeRange === '1Y') {
                $current->addMonth();
            } else if ($timeRange === '1D') {
                $current->addHour();
            } else {
                $current->addDay();
            }
        }

        foreach ($deliveries as $order) {
            $date = $order->delivered_at->format($format);
            if (isset($chartIncome[$date])) {
                $chartIncome[$date] += $fee;
                $chartCount[$date] += 1;
            }
        }

        foreach ($returns as $ret) {
            $date = $ret->updated_at->format($format);
            if (isset($chartReturnCount[$date])) {
                $chartReturnCount[$date] += 1;
            }
        }

        $chartData = [
            'labels' => array_values($chartLabels),
            'income' => array_values($chartIncome),
            'count' => array_values($chartCount),
            'returns' => array_values($chartReturnCount),
            'fee' => $fee
        ];

        if ($request->ajax()) {
            return response()->json($chartData);
        }

        return view('delivery.dashboard', compact('stats', 'recentReturns', 'chartData', 'timeRange'));
    }

    public function work()
    {
        if (Auth::user()->role !== 'delivery_boy') {
            return redirect('/');
        }

        // Check if the delivery partner is approved by any store
        $approvedApplication = DeliveryApplication::with('storeOwner')
            ->where('delivery_boy_id', Auth::id())
            ->where('status', 'approved')
            ->first();

        $isJoined = $approvedApplication ? true : false;
        if ($isJoined) {
            $storeName = $approvedApplication->storeOwner->role === 'admin' ? 'E-Shop' : $approvedApplication->storeOwner->name;
        } else {
            $storeName = null;
        }
        $activeOrders = collect();
        $availableOrders = collect();
        $availableReturns = collect();
        $acceptedReturns = collect();

        if ($isJoined) {
            // 1. Orders already assigned to this partner
            $activeOrders = Order::with('user')
                ->where('delivery_boy_id', Auth::id())
                ->whereIn('status', ['processing', 'shipped'])
                ->orderBy('updated_at', 'desc')
                ->get();

            // 2. Orders from the store that are "Payment Complet" (status: completed) 
            // and NOT yet assigned to anyone
            $availableOrders = Order::with('user')
                ->whereNull('delivery_boy_id')
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();

            // 3. Returns assigned to this partner for pickup
            $availableReturns = OrderReturn::with(['user', 'order'])
                ->where('delivery_boy_id', Auth::id())
                ->where('status', 'pending')
                ->get();

            $acceptedReturns = OrderReturn::with(['user', 'order'])
                ->where('delivery_boy_id', Auth::id())
                ->whereIn('status', ['accepted', 'picked_up'])
                ->get();
        }

        return view('delivery.work', compact(
            'availableOrders', 
            'activeOrders', 
            'availableReturns', 
            'acceptedReturns',
            'isJoined', 
            'storeName'
        ));
    }

    public function stores()
    {
        if (Auth::user()->role !== 'delivery_boy') {
            return redirect('/');
        }

        $storeOwners = User::where('role', 'admin')
            ->whereHas('subSellers')
            ->get();
        
        $myApplications = DeliveryApplication::where('delivery_boy_id', Auth::id())
            ->pluck('status', 'store_owner_id')
            ->toArray();

        // Get full work history for display to new store owners
        $workHistory = DeliveryApplication::where('delivery_boy_id', Auth::id())
            ->with('storeOwner')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('delivery.stores', compact('storeOwners', 'myApplications', 'workHistory'));
    }

    public function history()
    {
        if (Auth::user()->role !== 'delivery_boy') {
            return redirect('/');
        }

        $completedOrders = Order::with('user')
            ->where('delivery_boy_id', Auth::id())
            ->where('status', 'delivered')
            ->orderBy('updated_at', 'desc')
            ->paginate(6, ['*'], 'delivery_page');

        $completedReturns = OrderReturn::with(['user', 'order'])
            ->where('delivery_boy_id', Auth::id())
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->paginate(6, ['*'], 'returns_page');

        return view('delivery.history', compact('completedOrders', 'completedReturns'));
    }

    public function apply(Request $request, $storeOwnerId)
    {
        if (Auth::user()->role !== 'delivery_boy') {
            return redirect('/');
        }

        $request->validate([
            'phno' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        // Check if already has a pending application at another store
        $pendingApp = DeliveryApplication::where('delivery_boy_id', Auth::id())
            ->where('status', 'pending')
            ->where('store_owner_id', '!=', $storeOwnerId)
            ->with('storeOwner')
            ->first();

        if ($pendingApp) {
            $storeName = $pendingApp->storeOwner->name ?? 'another store';
            return back()->with('error', "You already have a pending application at {$storeName}. Please wait for that application to be accepted or rejected before applying elsewhere.");
        }

        // Check if already working at a store
        $activeJob = DeliveryApplication::where('delivery_boy_id', Auth::id())
            ->where('status', 'approved')
            ->with('storeOwner')
            ->first();

        if ($activeJob) {
            $storeName = $activeJob->storeOwner->role === 'admin' ? 'E-Shop' : $activeJob->storeOwner->name;
            return back()->with('error', "You are currently working for {$storeName}. You must be released from your current job before applying to another store.");
        }

        // Save/Update user info
        Auth::user()->info()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'phno' => $request->phno,
                'address' => $request->address,
            ]
        );

        $storeOwner = User::whereIn('role', ['admin', 'seller'])->findOrFail($storeOwnerId);

        DeliveryApplication::updateOrCreate(
            ['delivery_boy_id' => Auth::id(), 'store_owner_id' => $storeOwner->id],
            ['status' => 'pending']
        );

        $storeOwner->notify(new DeliveryApplicationNotification(Auth::user()));

        return back()->with('success', 'You have successfully applied to ' . $storeOwner->name . '. They have been notified.');
    }

    public function takeOrder(Request $request, $orderId)
    {
        if (Auth::user()->role !== 'delivery_boy') {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $order = Order::findOrFail($orderId);

        if ($order->status !== 'completed' && $order->status !== 'processing') {
            return response()->json(['success' => false, 'message' => 'Order is not ready for pickup.']);
        }

        if ($order->delivery_boy_id !== null && $order->delivery_boy_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Order already taken by another partner.']);
        }

        $request->validate([
            'pickup_image' => 'required|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('pickup_image')) {
            $file = $request->file('pickup_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/delivery/pickup'), $filename);
            $imagePath = 'media/delivery/pickup/' . $filename;
        }

        $secretCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $updateData = [
            'delivery_boy_id' => Auth::id(),
            'status' => 'shipped'
        ];

        if (empty($order->assignment_type)) {
            $updateData['assignment_type'] = 'self';
        }

        $order->update($updateData);

        // Create or Update OrderDelivery record
        \App\Models\OrderDelivery::updateOrCreate(
            ['order_id' => $order->id, 'delivery_boy_id' => Auth::id()],
            [
                'pickup_image' => $imagePath,
                'secret_code' => $secretCode,
                'status' => 'picked_up'
            ]
        );

        try {
            \Illuminate\Support\Facades\Mail::to($order->user->email)
                ->send(new \App\Mail\OrderStatusMail($order, 'shipped'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Shipped Email Failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Order picked up! Status changed to Shipped.']);
    }

    public function verifyDelivery(Request $request, $id)
    {
        $order = Order::where('delivery_boy_id', Auth::id())
            ->where('status', 'shipped')
            ->findOrFail($id);

        $delivery = \App\Models\OrderDelivery::where('order_id', $order->id)
            ->where('delivery_boy_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'code' => 'required|string|size:6',
            'delivery_image' => 'required|image|max:5120',
        ]);

        if ($request->code !== $delivery->secret_code) {
            return response()->json(['success' => false, 'message' => 'Invalid secret code. Please ask the customer for the correct code.']);
        }

        $imagePath = null;
        if ($request->hasFile('delivery_image')) {
            $file = $request->file('delivery_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/delivery/delivery'), $filename);
            $imagePath = 'media/delivery/delivery/' . $filename;
        }

        // --- PAYMENT LOGIC ---
        $feePaid = $order->processDeliveryPayment();

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
        
        $delivery->update([
            'delivery_image' => $imagePath,
            'status' => 'delivered'
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($order->user->email)
                ->send(new \App\Mail\OrderStatusMail($order, 'delivered'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Delivered Email Failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Order delivered successfully! Payment of $' . $feePaid . ' added to your balance.']);
    }

    public function acceptReturn(Request $request, $id)
    {
        $return = OrderReturn::where('delivery_boy_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $return->update([
            'status' => 'accepted'
        ]);

        // Notify Buyer via Email
        try {
            \Illuminate\Support\Facades\Mail::to($return->user->email)
                ->send(new \App\Mail\ReturnStatusMail($return));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Return Accepted Email Failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Return task accepted! Go to the customer address.']);
    }

    public function pickupReturn(Request $request, $id)
    {
        $return = OrderReturn::where('delivery_boy_id', Auth::id())
            ->where('status', 'accepted')
            ->findOrFail($id);

        $request->validate([
            'pickup_image' => 'required|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('pickup_image')) {
            $file = $request->file('pickup_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/returns/pickup'), $filename);
            $imagePath = 'media/returns/pickup/' . $filename;
        }

        $return->update([
            'status' => 'picked_up',
            'pickup_image' => $imagePath
        ]);

        // Notify Buyer via Email
        try {
            \Illuminate\Support\Facades\Mail::to($return->user->email)
                ->send(new \App\Mail\ReturnStatusMail($return));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Return Picked Up Email Failed: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Return item picked up! Now deliver it to the store.']);
    }

    public function dropoffReturn(Request $request, $id)
    {
        $return = OrderReturn::where('delivery_boy_id', Auth::id())
            ->where('status', 'picked_up')
            ->findOrFail($id);

        $request->validate([
            'store_image' => 'required|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('store_image')) {
            $file = $request->file('store_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('media/returns/store'), $filename);
            $imagePath = 'media/returns/store/' . $filename;
        }

        $return->update([
            'status' => 'completed',
            'store_image' => $imagePath
        ]);

        // Notify Buyer via Email
        try {
            \Illuminate\Support\Facades\Mail::to($return->user->email)
                ->send(new \App\Mail\ReturnStatusMail($return));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Return Completed Email Failed: " . $e->getMessage());
        }

        // Mark notification as read for the store owner
        $storeOwner = $return->storeOwner;
        if ($storeOwner) {
            $storeOwner->unreadNotifications()
                ->where('type', 'App\Notifications\OrderReturnNotification')
                ->where('data->order_id', $return->order_id)
                ->get()
                ->markAsRead();
        }

        return response()->json(['success' => true, 'message' => 'Return completed! Product returned to store.']);
    }
}
