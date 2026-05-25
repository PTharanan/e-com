<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Product;
use App\Models\Order;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Please login to proceed with payment.'], 401);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $cartItems = $request->input('cart_items');
        $orderPayload = $this->buildOrderPayload($cartItems);

        if (empty($orderPayload['items'])) {
            return response()->json(['error' => 'Cart is empty or invalid.'], 400);
        }

        $totalAmount = $orderPayload['total_amount'];

        $currentCurrency = \App\Models\SiteSetting::where('key', 'site_currency')->value('value') ?? 'USD';
        $stripeAmount = $totalAmount;

        if (strtoupper($currentCurrency) !== 'USD') {
            $apiKey = env('EXCHANGE_RATE_API_KEY');
            if ($apiKey) {
                try {
                    $response = \Illuminate\Support\Facades\Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/" . strtoupper($currentCurrency));
                    if ($response->successful()) {
                        $rates = $response->json()['conversion_rates'];
                        if (isset($rates['USD'])) {
                            $stripeAmount = $totalAmount * $rates['USD'];
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to fetch exchange rate for Stripe checkout: " . $e->getMessage());
                }
            }
        }

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => round($stripeAmount * 100), // Cents in USD
                'currency' => 'usd',
                'payment_method_types' => ['card'],
            ]);

            // Save order info to session for the success page
            $totalItemsCount = $orderPayload['total_items'];

            session([
                'last_order' => $orderPayload['items'],
                'order_groups' => $orderPayload['groups'],
                'last_total' => $totalAmount,
                'last_count' => $totalItemsCount,
                'order_time' => now()->timestamp,
                'payment_intent_id' => $paymentIntent->id,
                'order_admin_id' => count($orderPayload['groups']) > 0 ? $orderPayload['groups'][0]['admin_id'] : null
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'total' => $totalAmount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success()
    {
        $orderTime = session('order_time');

        // If no order time or more than 5 minutes have passed, return 404
        if (!$orderTime || (now()->timestamp - $orderTime > 300)) {
            abort(404);
        }

        $orderData = session('last_order');
        $orderGroups = session('order_groups', []);
        $total = session('last_total');

        if ($orderData && auth()->check()) {
            try {
                $paymentIntentId = session('payment_intent_id');

                if ($paymentIntentId && Order::where('payment_intent_id', $paymentIntentId)->exists()) {
                    session()->forget(['last_order', 'last_total', 'last_count', 'order_time', 'payment_intent_id', 'order_admin_id', 'order_groups']);
                    return redirect()->route('payment.success');
                }

                if (empty($orderGroups)) {
                    $orderGroups = [
                        [
                            'admin_id' => session('order_admin_id'),
                            'items' => $orderData,
                            'total_price' => $total,
                            'total_items' => session('last_count', count($orderData)),
                        ]
                    ];
                }

                foreach ($orderGroups as $group) {
                    Order::create([
                        'admin_id' => $group['admin_id'] ?? session('order_admin_id'),
                        'user_id' => auth()->id(),
                        'payment_intent_id' => $paymentIntentId,
                        'payment_method' => 'stripe',
                        'total_price' => $group['total_price'] ?? $total,
                        'total_items' => $group['total_items'] ?? session('last_count', count($orderData)),
                        'items_json' => $group['items'] ?? $orderData,
                        'status' => 'completed'
                    ]);
                }

                \Illuminate\Support\Facades\Mail::to(auth()->user()->email)
                    ->send(new \App\Mail\OrderSuccessMail($orderData, $total));

                // Clear order data
                session()->forget(['last_order', 'last_total', 'last_count', 'order_time', 'payment_intent_id', 'order_admin_id', 'order_groups']);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Mail failed: " . $e->getMessage());
            }
        }

        return redirect()->route('payment.success');
    }

    public function cancel()
    {
        return redirect()->route('cart')->with('error', 'Payment was cancelled.');
    }

    private function buildOrderPayload(array $cartItems): array
    {
        $items = [];
        $groups = [];
        $totalAmount = 0;
        $totalItems = 0;

        foreach ($cartItems as $id => $data) {
            $productId = is_array($data) && isset($data['productId']) ? (int) $data['productId'] : (int) $id;
            $product = Product::availableForBuyers()->find($productId);

            if (!$product) {
                continue;
            }

            $quantity = is_array($data) ? (int) ($data['qty'] ?? 0) : (int) $data;
            if ($quantity <= 0) {
                continue;
            }

            $color = is_array($data) && isset($data['color']) ? $data['color'] : null;
            $size = is_array($data) && isset($data['size']) ? $data['size'] : null;

            $storeOwnerId = $product->seller_id ?: $product->admin_id;
            if (!$storeOwnerId) {
                continue;
            }

            $storeName = 'E-Shop';
            if ($product->seller_id) {
                $storeName = $product->seller->name ?? 'E-Shop';
            } elseif ($product->admin_id) {
                $adminUser = \App\Models\User::find($product->admin_id);
                $storeName = ($adminUser && $adminUser->role === 'admin') ? 'E-Shop' : ($adminUser->name ?? 'E-Shop');
            }

            $productName = $product->name;
            if ($color || $size) {
                $variants = array_filter([$color, $size]);
                $productName .= ' (' . implode(', ', $variants) . ')';
            }

            $item = [
                'id' => $product->id,
                'name' => $productName,
                'price' => $product->final_price,
                'qty' => $quantity,
                'color' => $color,
                'size' => $size,
                'seller_id' => $product->seller_id,
                'admin_id' => $product->admin_id,
                'store_name' => $storeName,
                'path' => $product->main_image_url,
            ];

            $items[] = $item;
            $totalAmount += $product->final_price * $quantity;
            $totalItems += $quantity;

            if (!isset($groups[$storeOwnerId])) {
                $groups[$storeOwnerId] = [
                    'admin_id' => $storeOwnerId,
                    'store_name' => $storeName,
                    'items' => [],
                    'total_price' => 0,
                    'total_items' => 0,
                ];
            }

            $groups[$storeOwnerId]['items'][] = $item;
            $groups[$storeOwnerId]['total_price'] += $product->final_price * $quantity;
            $groups[$storeOwnerId]['total_items'] += $quantity;
        }

        return [
            'items' => $items,
            'groups' => array_values($groups),
            'total_amount' => $totalAmount,
            'total_items' => $totalItems,
        ];
    }
}
