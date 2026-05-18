<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class NotificationController extends Controller
{
    public function poll()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        // 1. Order Notifications (Paid/Completed)
        $newOrders = Order::with('user')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $newOrdersCount = Order::where('status', 'completed')->count();

        $mappedOrders = $newOrders->map(function ($o) {
            return [
                'id' => $o->id,
                'customer' => $o->user->name ?? 'Guest',
                'time' => $o->created_at->diffForHumans(),
            ];
        });

        // 2. Delivery Partner Notifications
        $deliveryNotifications = $user->unreadNotifications()
            ->where('type', 'like', '%DeliveryApplicationNotification')
            ->take(5)
            ->get();
        $deliveryNotifCount = $user->unreadNotifications()
            ->where('type', 'like', '%DeliveryApplicationNotification')
            ->count();

        $mappedDelivery = $deliveryNotifications->map(function ($n) {
            return [
                'id' => $n->id,
                'name' => $n->data['delivery_boy_name'] ?? 'Partner',
                'time' => $n->created_at->diffForHumans(),
            ];
        });

        // 3. Stock Notifications
        $stockNotifications = $user->unreadNotifications()
            ->where('type', 'like', '%ProductOutOfStockNotification')
            ->take(5)
            ->get();
        $stockNotifCount = $user->unreadNotifications()
            ->where('type', 'like', '%ProductOutOfStockNotification')
            ->count();

        $mappedStock = $stockNotifications->map(function ($n) {
            return [
                'id' => $n->id,
                'product_name' => $n->data['product_name'] ?? 'Product',
                'message' => $n->data['message'] ?? 'Out of stock',
                'time' => $n->created_at->diffForHumans(),
            ];
        });
        
        // 4. Return Notifications
        $returnNotifications = $user->unreadNotifications()
            ->where('type', 'like', '%OrderReturnNotification')
            ->take(5)
            ->get();
        $returnNotifCount = $user->unreadNotifications()
            ->where('type', 'like', '%OrderReturnNotification')
            ->count();
            
        $mappedReturns = $returnNotifications->map(function ($n) {
            return [
                'id' => $n->id,
                'order_id' => $n->data['order_id'] ?? 'Order',
                'customer' => $n->data['customer_name'] ?? 'Customer',
                'time' => $n->created_at->diffForHumans(),
            ];
        });

        // 5. Delivery Boy (Work Assigned)
        if ($user->role === 'delivery_boy') {
            $newWork = Order::with('user')
                ->where('delivery_boy_id', $user->id)
                ->where('status', 'processing') // assigned but not yet picked up
                ->orderBy('updated_at', 'desc')
                ->get();
            
            $newWorkCount = $newWork->count();

            $mappedWork = $newWork->map(function ($w) {
                return [
                    'id' => $w->id,
                    'customer' => $w->user->name ?? 'Guest',
                    'time' => $w->updated_at->diffForHumans(),
                ];
            });

            return response()->json([
                'work' => [
                    'count' => $newWorkCount,
                    'items' => $mappedWork
                ]
            ]);
        }

        return response()->json([
            'orders' => [
                'count' => $newOrdersCount,
                'items' => $mappedOrders
            ],
            'delivery' => [
                'count' => $deliveryNotifCount,
                'items' => $mappedDelivery
            ],
            'stock' => [
                'count' => $stockNotifCount,
                'items' => $mappedStock
            ],
            'returns' => [
                'count' => $returnNotifCount,
                'items' => $mappedReturns
            ]
        ]);
    }

    public function dismiss($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }
}
