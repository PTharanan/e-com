<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $adminId = auth()->id();

        // Basic Stats
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();
        $totalUsers = User::where('role', 'client')->count();
        $totalRevenue = Order::whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])
            ->sum('total_price');
        $totalProducts = Product::whereNull('seller_id')->count();
        $totalRefunds = Order::where('status', 'refunded')->count();

        // Default: 5D view (matching active button)
        $analytics = $this->fetchAnalyticsData($adminId, '5D');

        // Product Sales Data for Pie Chart (Total Quantity Sold)
        $orders = Order::whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])
            ->get();

        $productSales = [];
        foreach ($orders as $order) {
            $items = is_string($order->items_json) ? json_decode($order->items_json, true) : $order->items_json;
            if (is_array($items)) {
                foreach ($items as $item) {
                    $name = $item['name'] ?? 'Unknown';
                    $qty = (int) ($item['qty'] ?? 0);
                    if (!isset($productSales[$name])) {
                        $productSales[$name] = 0;
                    }
                    $productSales[$name] += $qty;
                }
            }
        }

        arsort($productSales);
        $top5 = array_slice($productSales, 0, 5, true);
        $productLabels = array_keys($top5);
        $productCounts = array_values($top5);

        $days = $analytics['labels'];
        $revenueData = $analytics['data'];

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalUsers',
            'totalRevenue',
            'totalProducts',
            'totalRefunds',
            'days',
            'revenueData',
            'productLabels',
            'productCounts'
        ));
    }

    public function getAnalytics(Request $request)
    {
        $range = $request->get('range', '5D');
        $adminId = auth()->id();
        return response()->json($this->fetchAnalyticsData($adminId, $range));
    }

    private function fetchAnalyticsData($adminId, $range)
    {
        $labels = [];
        $data = [];

        if ($range === '1D') {
            for ($i = 23; $i >= 0; $i--) {
                $time = Carbon::now()->subHours($i);
                $labels[] = $time->format('H:00');
                $rev = Order::whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])
                    ->whereBetween('created_at', [$time->copy()->startOfHour(), $time->copy()->endOfHour()])
                    ->sum('total_price');
                $data[] = (float) $rev;
            }
        } elseif ($range === '5D' || $range === '1M') {
            $count = ($range === '5D') ? 5 : 30;
            for ($i = $count - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('D j M');
                $rev = Order::whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('total_price');
                $data[] = (float) $rev;
            }
        } elseif ($range === '1Y') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');
                $rev = Order::whereIn('status', ['completed', 'processing', 'shipped', 'delivered'])
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_price');
                $data[] = (float) $rev;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
