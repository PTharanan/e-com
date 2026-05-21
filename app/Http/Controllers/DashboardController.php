<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderStatusMail;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = Order::with(['delivery', 'returns'])->where('user_id', $user->id)->get();

        $totalCashSpent = $orders->sum('total_price');
        $totalItemsBought = $orders->sum('total_items');
        
        // Count unique products by checking all items_json across orders
        $uniqueProductNames = [];
        foreach ($orders as $order) {
            if (is_array($order->items_json)) {
                foreach ($order->items_json as $item) {
                    if (isset($item['name'])) {
                        $uniqueProductNames[] = $item['name'];
                    }
                }
            }
        }
        $totalProductsCount = count(array_unique($uniqueProductNames));

        return view('dashboard', compact('totalCashSpent', 'totalItemsBought', 'totalProductsCount', 'orders'));
    }

    public function cancelOrder(Request $request, $id)
    {
        $order = Order::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status !== 'completed') {
            return back()->with('error', 'This order cannot be cancelled anymore.');
        }

        // Update status
        $order->status = 'cancelled';
        $order->save();

        // Restore stock
        if (is_array($order->items_json)) {
            foreach ($order->items_json as $item) {
                $this->restoreOrderItemStock($item);
            }
        }

        // Send email notification to user
        try {
            Mail::to(Auth::user()->email)->send(new OrderStatusMail($order, 'cancelled'));
            
            // Notify Admin/Seller (Store Owner)
            $admin = \App\Models\User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new \App\Notifications\OrderCancelledNotification($order));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send order cancellation notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Order cancelled successfully. Please wait for admin to process your refund.');
    }

    private function restoreOrderItemStock(array $item): void
    {
        $product = null;

        if (isset($item['id'])) {
            $product = Product::find($item['id']);
        }

        if (!$product && isset($item['name'])) {
            $product = Product::where('name', $item['name'])->first();
        }

        if (!$product) {
            return;
        }

        $qty = (int) ($item['qty'] ?? 0);
        if ($qty <= 0) {
            return;
        }

        $product->stock_quantity += $qty;
        $product->save();

        if (!empty($item['color'])) {
            $colorVariant = $product->variants()
                ->where('variant_type', 'color')
                ->where('value', $item['color'])
                ->first();

            if ($colorVariant) {
                $colorVariant->stock_quantity += $qty;
                $colorVariant->save();
            }
        }

        if (!empty($item['size'])) {
            $sizeVariant = $product->variants()
                ->where('variant_type', 'size')
                ->where('value', $item['size'])
                ->first();

            if ($sizeVariant) {
                $sizeVariant->stock_quantity += $qty;
                $sizeVariant->save();
            }
        }
    }

    public function returnOrder(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order = Order::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Only delivered orders can be returned.'], 400);
        }

        // Check 14-day return window using delivered_at (fallback to updated_at for older orders)
        $deliveryDate = $order->delivered_at ?? $order->updated_at;
        if ($deliveryDate->diffInDays(now()) > 14) {
            return response()->json(['success' => false, 'message' => 'The return window for this order has expired (14 days max).'], 400);
        }

        // Check if already returned
        $exists = OrderReturn::where('order_id', $id)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Return request already submitted for this order.'], 400);
        }

        DB::transaction(function () use ($order, $request) {
            // Update order status to 'returning'
            $order->status = 'returning';
            $order->save();

            // Create return record
            OrderReturn::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            // --- NOTIFICATIONS ---
            $notification = new \App\Notifications\OrderReturnNotification($order, $request->reason);

            // 1. Notify Original Delivery Boy (who delivered it)
            if ($order->delivery_boy_id) {
                $deliveryBoy = \App\Models\User::find($order->delivery_boy_id);
                if ($deliveryBoy) {
                    $deliveryBoy->notify($notification);
                }
            }

            // 1b. Notify ALL Approved Delivery Boys for this store/admin
            $storeDeliveryBoys = \App\Models\DeliveryApplication::where('store_owner_id', $order->admin_id)
                ->where('status', 'approved')
                ->pluck('delivery_boy_id');
            
            foreach ($storeDeliveryBoys as $dbId) {
                if ($dbId != $order->delivery_boy_id) { // Avoid double notification
                    $dbUser = \App\Models\User::find($dbId);
                    if ($dbUser) {
                        $dbUser->notify($notification);
                    }
                }
            }

            // 2. Notify Admin
            if ($order->admin_id) {
                $admin = \App\Models\User::find($order->admin_id);
                if ($admin) {
                    $admin->notify($notification);
                }
            }

            // 3. Notify Sellers (from items_json)
            if (is_array($order->items_json)) {
                $sellerIds = [];
                foreach ($order->items_json as $item) {
                    if (isset($item['seller_id'])) {
                        $sellerIds[] = $item['seller_id'];
                    }
                }
                $sellerIds = array_unique($sellerIds);
                
                foreach ($sellerIds as $sId) {
                    $seller = \App\Models\User::find($sId);
                    if ($seller && $sId != $order->admin_id) { // Avoid double notification if admin is also seller
                        $seller->notify($notification);
                    }
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Return request submitted successfully.']);
    }
}
