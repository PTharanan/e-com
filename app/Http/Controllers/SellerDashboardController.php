<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SellerDashboardController extends Controller
{
    public function index()
    {
        $sellerId = auth()->id();
        
        $orders = Order::whereJsonContains('items_json', [['seller_id' => (int)$sellerId]])
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalOrders = $orders->count();
        $totalUsers = User::where('role', 'client')->count();
        $totalProducts = Product::where('seller_id', $sellerId)->count();
        
        $totalRevenue = 0;
        $totalRefunds = 0;
        $productSales = [];

        foreach ($orders as $order) {
            $items = is_string($order->items_json) ? json_decode($order->items_json, true) : $order->items_json;
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (isset($item['seller_id']) && $item['seller_id'] == $sellerId) {
                        $qty = (int)($item['qty'] ?? 0);
                        $price = $item['price'] ?? 0;
                        $amount = $qty * $price;

                        if (in_array($order->status, ['completed', 'processing', 'shipped', 'delivered'])) {
                            $totalRevenue += $amount;
                            
                            $name = $item['name'] ?? 'Unknown';
                            if (!isset($productSales[$name])) {
                                $productSales[$name] = 0;
                            }
                            $productSales[$name] += $qty;
                        }
                        
                        if ($order->status === 'refunded') {
                            $totalRefunds++;
                        }
                    }
                }
            }
        }

        arsort($productSales);
        $top5 = array_slice($productSales, 0, 5, true);
        $productLabels = array_keys($top5);
        $productCounts = array_values($top5);

        // Default: 5D view
        $analytics = $this->fetchAnalyticsData($sellerId, '5D');
        $days = $analytics['labels'];
        $revenueData = $analytics['data'];

        return view('seller.dashboard', compact(
            'totalOrders', 'totalUsers', 'totalRevenue', 'totalProducts', 'totalRefunds',
            'days', 'revenueData', 'productLabels', 'productCounts'
        ));
    }

    public function getAnalytics(Request $request)
    {
        $range = $request->get('range', '5D');
        $sellerId = auth()->id();
        return response()->json($this->fetchAnalyticsData($sellerId, $range));
    }

    private function fetchAnalyticsData($sellerId, $range)
    {
        $labels = [];
        $data = [];

        // For sellers, we need to filter the orders that contain their products
        $allOrders = Order::whereJsonContains('items_json', [['seller_id' => (int)$sellerId]])
            ->whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])
            ->get();

        if ($range === '1D') {
            for ($i = 23; $i >= 0; $i--) {
                $time = Carbon::now()->subHours($i);
                $labels[] = $time->format('H:00');
                
                $rev = 0;
                foreach ($allOrders as $order) {
                    if ($order->created_at->between($time->copy()->startOfHour(), $time->copy()->endOfHour())) {
                        $items = is_string($order->items_json) ? json_decode($order->items_json, true) : $order->items_json;
                        foreach ($items as $item) {
                            if (isset($item['seller_id']) && $item['seller_id'] == $sellerId) {
                                $rev += ($item['qty'] ?? 0) * ($item['price'] ?? 0);
                            }
                        }
                    }
                }
                $data[] = (float)$rev;
            }
        } elseif ($range === '5D' || $range === '1M') {
            $count = ($range === '5D') ? 5 : 30;
            for ($i = $count - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('D j M');
                
                $rev = 0;
                foreach ($allOrders as $order) {
                    if ($order->created_at->isSameDay($date)) {
                        $items = is_string($order->items_json) ? json_decode($order->items_json, true) : $order->items_json;
                        foreach ($items as $item) {
                            if (isset($item['seller_id']) && $item['seller_id'] == $sellerId) {
                                $rev += ($item['qty'] ?? 0) * ($item['price'] ?? 0);
                            }
                        }
                    }
                }
                $data[] = (float)$rev;
            }
        } elseif ($range === '1Y') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');
                
                $rev = 0;
                foreach ($allOrders as $order) {
                    if ($order->created_at->month === $date->month && $order->created_at->year === $date->year) {
                        $items = is_string($order->items_json) ? json_decode($order->items_json, true) : $order->items_json;
                        foreach ($items as $item) {
                            if (isset($item['seller_id']) && $item['seller_id'] == $sellerId) {
                                $rev += ($item['qty'] ?? 0) * ($item['price'] ?? 0);
                            }
                        }
                    }
                }
                $data[] = (float)$rev;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
