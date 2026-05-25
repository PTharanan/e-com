<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSEController extends Controller
{
    public function stream()
    {
        return new StreamedResponse(function () {
            // Force clear any pre-existing output buffers
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            // Important: Close session to allow other requests (like navigations) 
            // while the stream is open. Otherwise, the site will feel frozen.
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            set_time_limit(0);
            ignore_user_abort(false);

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $user = Auth::user();
                if (!$user) {
                    break;
                }

                $data = [];

                if ($user->role === 'admin' || $user->role === 'seller') {
                    $query = Order::with('user');
                    if ($user->role === 'admin') {
                        $query->where('admin_id', $user->id);
                    } else {
                        $sellerId = $user->id;
                        $query->where('admin_id', $user->admin_id)->where(function($q) use ($sellerId) {
                            $q->where('items_json', 'like', '%"seller_id":'.$sellerId.'%')
                              ->orWhere('items_json', 'like', '%"seller_id": '.$sellerId.'%');
                        });
                    }

                    $newOrders = (clone $query)->where('status', 'completed')->orderBy('created_at', 'desc')->take(5)->get();
                    $cancelledCount = (clone $query)->where('status', 'cancelled')->count();
                    $recentOrders = (clone $query)->with(['deliveryBoy', 'delivery'])->orderBy('updated_at', 'desc')->take(20)->get();

                    $data['orders'] = [
                        'count' => (clone $query)->where('status', 'completed')->count(),
                        'items' => $newOrders->map(fn($o) => ['id' => $o->id, 'customer' => $o->user->name ?? 'Guest', 'time' => $o->created_at->diffForHumans()]),
                        'cancelled_count' => $cancelledCount,
                        'cancelled_items' => (clone $query)->where('status', 'cancelled')->orderBy('updated_at', 'desc')->take(5)->get()->map(fn($o) => ['id' => $o->id, 'customer' => $o->user->name ?? 'Guest', 'time' => $o->updated_at->diffForHumans()]),
                        'all_statuses' => $recentOrders->map(fn($o) => [
                            'id' => $o->id, 
                            'status' => $o->status,
                            'delivery_boy_id' => $o->delivery_boy_id,
                            'delivery_boy_name' => $o->deliveryBoy?->name,
                            'assignment_type' => $o->assignment_type,
                            'pickup_image' => $o->delivery?->pickup_image ? asset($o->delivery->pickup_image) : null,
                            'delivery_image' => $o->delivery?->delivery_image ? asset($o->delivery->delivery_image) : null
                        ])
                    ];
                }
                if ($user->role === 'admin' || $user->role === 'seller' || $user->role === 'delivery_boy') {
                    $deliveryNotifications = $user->unreadNotifications()->where('type', 'like', '%DeliveryApplicationNotification')->take(5)->get();
                    $data['delivery'] = [
                        'count' => $user->unreadNotifications()->where('type', 'like', '%DeliveryApplicationNotification')->count(),
                        'items' => $deliveryNotifications->map(fn($n) => ['id' => $n->id, 'name' => $n->data['delivery_boy_name'] ?? 'Partner', 'time' => $n->created_at->diffForHumans()])
                    ];

                    // Stock Notifications (Admin/Seller only)
                    if ($user->role === 'admin' || $user->role === 'seller') {
                        $stockNotifications = $user->unreadNotifications()->where('type', 'like', '%ProductOutOfStockNotification')->take(5)->get();
                        $data['stock'] = [
                            'count' => $user->unreadNotifications()->where('type', 'like', '%ProductOutOfStockNotification')->count(),
                            'items' => $stockNotifications->map(fn($n) => [
                                'id' => $n->id, 
                                'product_name' => $n->data['product_name'] ?? 'Product', 
                                'message' => $n->data['message'] ?? 'Out of stock',
                                'time' => $n->created_at->diffForHumans()
                            ])
                        ];
                    }

                    // Return Notifications (Admin/Seller only)
                    if ($user->role === 'admin' || $user->role === 'seller') {
                        $returnNotifications = $user->unreadNotifications()->where('type', 'like', '%OrderReturnNotification')->take(5)->get();
                        $data['returns'] = [
                            'count' => $user->unreadNotifications()->where('type', 'like', '%OrderReturnNotification')->count(),
                            'items' => $returnNotifications->map(fn($n) => [
                                'id' => $n->id,
                                'order_id' => $n->data['order_id'] ?? 'Order',
                                'customer' => $n->data['customer_name'] ?? 'Customer',
                                'reason' => $n->data['reason'] ?? 'Reason not specified',
                                'time' => $n->created_at->diffForHumans()
                            ])
                        ];
                    }
                }

                if ($user->role === 'delivery_boy') {
                    // Fetch newly assigned orders for this partner
                    $data['assigned_work'] = Order::where('delivery_boy_id', $user->id)
                        ->where('status', 'processing')
                        ->where('updated_at', '>', now()->subSeconds(5))
                        ->exists();
                }

                if ($user->role === 'client') {
                    $userOrders = Order::with(['delivery', 'returns'])->where('user_id', $user->id)->orderBy('created_at', 'desc')->take(20)->get();
                    $data['user_orders'] = $userOrders->map(function($o) {
                        $displayStatus = $o->status;
                        if ($o->status == 'completed') $displayStatus = 'payment complet';
                        if ($o->status == 'refunded') $displayStatus = 'Refund';
                        
                        $returnStatus = null;
                        if ($o->returns && $o->returns->count() > 0) {
                            $latestReturn = $o->returns->sortByDesc('created_at')->first();
                            $returnStatus = $latestReturn->status;
                            // If we have an active return, use its status for display
                            $displayStatus = 'Return: ' . ucfirst(str_replace('_', ' ', $latestReturn->status));
                        }

                        return [
                            'id' => $o->id, 
                            'status' => $o->status, 
                            'return_status' => $returnStatus,
                            'display_status' => $displayStatus,
                            'secret_code' => $o->delivery?->secret_code,
                            'pickup_image' => $o->delivery?->pickup_image ? asset($o->delivery->pickup_image) : null,
                            'delivery_image' => $o->delivery?->delivery_image ? asset($o->delivery->delivery_image) : null
                        ];
                    });
                }

                \Illuminate\Support\Facades\Log::info("SSE Update Sent to User: " . $user->id . " (Role: " . $user->role . ")");
                
                echo "event: update\n";
                echo 'data: ' . json_encode($data) . "\n\n";

                ob_flush();
                flush();

                sleep(3); // Update every 3 seconds within the same connection
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Content-Encoding' => 'none',
            'Pragma' => 'no-cache',
        ]);
    }
}
