<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Product;
use App\Models\Order;

class PayPalController extends Controller
{
    /**
     * Initialize a PayPal checkout session.
     * Security: Auth required, rate limiting, server-side price calculation, session tracking.
     */
    public function checkout(Request $request)
    {
        // 1. Authentication check - only logged-in users can checkout
        if (!Auth::check()) {
            return response()->json(['error' => 'Please login to proceed with payment.'], 401);
        }

        // 2. Rate limiting - prevent abuse (max 5 attempts per minute per user)
        $rateLimitKey = 'paypal-checkout:' . Auth::id();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json(['error' => 'Too many checkout attempts. Please try again later.'], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        // 3. Validate input
        $cartItems = $request->input('cart_items', []);
        if (empty($cartItems) || !is_array($cartItems)) {
            return response()->json(['error' => 'Cart is empty or invalid.'], 400);
        }

        // 4. Server-side price calculation - NEVER trust client-side prices
        $orderPayload = $this->buildOrderPayload($cartItems);

        if ($orderPayload['total_amount'] <= 0 || empty($orderPayload['items'])) {
            return response()->json(['error' => 'Invalid cart items or total amount.'], 400);
        }

        $totalAmount = $orderPayload['total_amount'];

        // 5. Currency conversion (matching Stripe logic)
        $paypalAmount = $totalAmount;
        $currentCurrency = \App\Models\SiteSetting::where('key', 'site_currency')->value('value') ?? 'USD';

        if (strtoupper($currentCurrency) !== 'USD') {
            $apiKey = env('EXCHANGE_RATE_API_KEY');
            if ($apiKey) {
                try {
                    $response = \Illuminate\Support\Facades\Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/" . strtoupper($currentCurrency));
                    if ($response->successful()) {
                        $rates = $response->json()['conversion_rates'];
                        if (isset($rates['USD'])) {
                            $paypalAmount = $totalAmount * $rates['USD'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Exchange rate fetch failed for PayPal: " . $e->getMessage());
                }
            }
        }

        try {
            // 6. Create PayPal order
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('paypal.success'),
                    "cancel_url" => route('paypal.cancel'),
                    "brand_name" => config('app.name', 'E-Shop'),
                    "shipping_preference" => "NO_SHIPPING",
                ],
                "purchase_units" => [
                    0 => [
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => number_format($paypalAmount, 2, '.', '')
                        ],
                        "description" => "Order from " . config('app.name', 'E-Shop')
                    ]
                ]
            ]);

            if (isset($response['id']) && $response['id'] != null) {
                // 7. Store order data securely in session (server-side only)
                $totalItemsCount = $orderPayload['total_items'];

                session([
                    'paypal_order_id' => $response['id'],
                    'last_order' => $orderPayload['items'],
                    'order_groups' => $orderPayload['groups'],
                    'last_total' => $totalAmount,
                    'last_count' => $totalItemsCount,
                    'order_time' => now()->timestamp,
                    'payment_method' => 'paypal',
                    'order_admin_id' => count($orderPayload['groups']) > 0 ? $orderPayload['groups'][0]['admin_id'] : null
                ]);

                foreach ($response['links'] as $links) {
                    if ($links['rel'] == 'approve') {
                        return response()->json([
                            'approve_link' => $links['href']
                        ]);
                    }
                }
                return response()->json(['error' => 'Could not get approval link from PayPal.'], 500);
            } else {
                Log::error('PayPal order creation failed', ['response' => $response]);
                return response()->json([
                    'error' => 'PayPal order creation failed. Please try again.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('PayPal checkout error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'total' => $totalAmount
            ]);
            return response()->json(['error' => 'Payment initialization failed. Please try again.'], 500);
        }
    }

    /**
     * Handle PayPal payment success callback.
     * Security: Verifies PayPal order ID, captures payment, validates session, creates order.
     */
    public function success(Request $request)
    {
        // 1. Authentication check
        if (!Auth::check()) {
            return redirect()->route('sign-in')->with('error', 'Please login to complete your order.');
        }

        // 2. Validate session - check order time (15 minute window)
        $orderTime = session('order_time');
        if (!$orderTime || (now()->timestamp - $orderTime > 900)) {
            session()->forget(['paypal_order_id', 'last_order', 'last_total', 'last_count', 'order_time', 'payment_method', 'order_admin_id']);
            return redirect()->route('cart')->with('error', 'Payment session expired. Please try again.');
        }

        // 3. Verify PayPal order ID matches session
        $sessionOrderId = session('paypal_order_id');
        $token = $request->input('token');

        if (!$sessionOrderId || !$token || $sessionOrderId !== $token) {
            Log::warning('PayPal order ID mismatch', [
                'session_id' => $sessionOrderId,
                'request_token' => $token,
                'user_id' => Auth::id()
            ]);
            return redirect()->route('cart')->with('error', 'Invalid payment session. Please try again.');
        }

        try {
            // 4. Capture payment from PayPal
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->capturePaymentOrder($token);

            // 5. Verify payment status
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $orderData = session('last_order');
                $orderGroups = session('order_groups', []);
                $total = session('last_total');

                // 6. Prevent duplicate orders - check if PayPal order ID already exists
                $existingOrder = Order::where('payment_intent_id', $token)->first();
                if ($existingOrder) {
                    Log::warning('Duplicate PayPal order attempt', [
                        'paypal_order_id' => $token,
                        'user_id' => Auth::id()
                    ]);
                    session()->forget(['paypal_order_id', 'last_order', 'last_total', 'last_count', 'order_time', 'payment_method', 'order_admin_id']);
                    return redirect()->route('payment.success');
                }

                if ($orderData) {
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
                            'user_id' => Auth::id(),
                            'payment_intent_id' => $token,
                            'payment_method' => 'paypal',
                            'total_price' => $group['total_price'] ?? $total,
                            'total_items' => $group['total_items'] ?? session('last_count', count($orderData)),
                            'items_json' => $group['items'] ?? $orderData,
                            'status' => 'completed'
                        ]);
                    }

                    // 8. Send confirmation email
                    try {
                        \Illuminate\Support\Facades\Mail::to(Auth::user()->email)
                            ->send(new \App\Mail\OrderSuccessMail($orderData, $total));
                    } catch (\Exception $e) {
                        Log::error("PayPal order email failed: " . $e->getMessage());
                    }

                    // 9. Clear all session data
                    session()->forget(['paypal_order_id', 'last_order', 'last_total', 'last_count', 'order_time', 'payment_method', 'order_admin_id', 'order_groups']);
                }

                return redirect()->route('payment.success');
            } else {
                Log::error('PayPal payment not completed', [
                    'status' => $response['status'] ?? 'unknown',
                    'user_id' => Auth::id()
                ]);
                return redirect()->route('cart')->with('error', 'Payment was not completed. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('PayPal capture error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'token' => $token
            ]);
            return redirect()->route('cart')->with('error', 'Payment processing error. Please contact support.');
        }
    }

    /**
     * Handle PayPal payment cancellation.
     */
    public function cancel()
    {
        // Clean up session data
        session()->forget(['paypal_order_id', 'last_order', 'last_total', 'last_count', 'order_time', 'payment_method', 'order_admin_id', 'order_groups']);

        return redirect()->route('cart')->with('error', 'Payment was cancelled.');
    }

    private function buildOrderPayload(array $cartItems): array
    {
        $items = [];
        $groups = [];
        $totalAmount = 0;
        $totalItems = 0;

        foreach ($cartItems as $id => $data) {
            $productId = is_array($data) && isset($data['productId']) ? intval($data['productId']) : intval(explode('_', $id)[0]);

            $product = Product::availableForBuyers()->find($productId);
            if (!$product) {
                continue;
            }

            $quantity = is_array($data) ? intval($data['qty'] ?? 0) : intval($data);
            if ($quantity <= 0 || $quantity > 100) {
                continue;
            }

            if ($product->stock !== null && $product->stock < $quantity) {
                return ['items' => [], 'groups' => [], 'total_amount' => 0, 'total_items' => 0];
            }

            $color = is_array($data) && isset($data['color']) ? strip_tags($data['color']) : null;
            $size = is_array($data) && isset($data['size']) ? strip_tags($data['size']) : null;

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
                'path' => $product->main_image_url
            ];

            $items[] = $item;
            $totalAmount += ($product->final_price * $quantity);
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
            $groups[$storeOwnerId]['total_price'] += ($product->final_price * $quantity);
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
