<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Order;

class CustomerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isSeller = $user->role === 'seller';
        $sellerId = $user->id;

        // Base query for users who have orders (regardless of role)
        $customersQuery = User::query();

        if ($isSeller) {
            // Filter customers who have ordered products from THIS seller
            $customersQuery->whereHas('orders', function($q) use ($sellerId) {
                $q->whereJsonContains('items_json', ['seller_id' => (int)$sellerId]);
            });
        } else {
            $customersQuery->whereHas('orders');
        }

        $customers = $customersQuery
            ->withCount(['orders' => function($q) use ($isSeller, $sellerId) {
                if ($isSeller) {
                    $q->whereJsonContains('items_json', ['seller_id' => (int)$sellerId]);
                }
            }])
            ->with(['orders' => function($q) use ($isSeller, $sellerId) {
                if ($isSeller) {
                    $q->whereJsonContains('items_json', ['seller_id' => (int)$sellerId]);
                }
                $q->orderBy('created_at', 'desc');
            }])
            ->get()
            ->map(function($c) use ($isSeller, $sellerId) {
                // Calculate total spent for THIS seller
                if ($isSeller) {
                    $c->total_spent = $c->orders->sum('total_price'); // This is already filtered orders
                } else {
                    $c->total_spent = $c->orders->sum('total_price');
                }
                return $c;
            });

        // Pre-build JSON for the history modal
        $customersJson = $customers->map(function($c) use ($isSeller, $sellerId) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'joined' => $c->created_at->format('M d, Y'),
                'orders_count' => $c->orders_count,
                'total_spent' => $c->total_spent,
                'orders' => $c->orders->map(function($o) use ($isSeller, $sellerId) {
                    $items = $o->items_json;
                    if ($isSeller) {
                        // Filter items to show only THIS seller's items
                        $items = array_values(array_filter($items, function($item) use ($sellerId) {
                            return ($item['seller_id'] ?? null) == $sellerId;
                        }));
                    }
                    return [
                        'id' => $o->id,
                        'total_price' => $o->total_price,
                        'total_items' => $o->total_items,
                        'status' => $o->status,
                        'items' => $items,
                        'date' => $o->created_at->format('M d, Y'),
                    ];
                })
            ];
        });

        $viewPrefix = $isSeller ? 'seller' : 'admin';
        return view("$viewPrefix.customers", compact('customers', 'customersJson'));
    }
}
