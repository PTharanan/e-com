<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteSetting;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function index()
    {
        $currentCurrency = SiteSetting::where('key', 'site_currency')->first()->value ?? 'USD';
        $currentSymbol = SiteSetting::where('key', 'site_currency_symbol')->first()->value ?? '$';

        return view('admin.currency', compact('currentCurrency', 'currentSymbol'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|min:3|max:3|alpha',
        ]);

        $newCurrency = strtoupper($request->currency);
        $oldCurrency = SiteSetting::where('key', 'site_currency')->first()->value ?? 'USD';

        if ($newCurrency === $oldCurrency) {
            return back()->with('info', 'Currency is already set to ' . $newCurrency);
        }

        // Fetch exchange rate from free API
        try {
            $response = Http::timeout(15)->get("https://api.exchangerate-api.com/v4/latest/{$oldCurrency}");

            if (!$response->successful()) {
                return back()->with('error', 'Failed to fetch exchange rates. Please check your internet connection.');
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];
            $rate = $rates[$newCurrency] ?? null;

            if (!$rate) {
                return back()->with('error', "Currency code \"{$newCurrency}\" not found. Please enter a valid ISO currency code (e.g. USD, LKR, EUR, GBP, INR).");
            }

            // Detect the symbol for the new currency
            $symbol = $this->detectSymbol($newCurrency);

            // 1. Convert all product prices
            $count = 0;
            Product::chunk(100, function ($products) use ($rate, &$count) {
                foreach ($products as $product) {
                    $product->price = round($product->price * $rate, 2);
                    $product->save();
                    $count++;
                }
            });

            // 2. Convert users balances
            \App\Models\User::chunk(100, function ($users) use ($rate) {
                foreach ($users as $user) {
                    if ($user->balance > 0) {
                        $user->balance = round($user->balance * $rate, 2);
                        $user->save();
                    }
                }
            });

            // 3. Convert delivery application fees
            \App\Models\DeliveryApplication::chunk(100, function ($apps) use ($rate) {
                foreach ($apps as $app) {
                    if ($app->delivery_fee > 0) {
                        $app->delivery_fee = round($app->delivery_fee * $rate, 2);
                        $app->save();
                    }
                }
            });

            // 4. Convert all existing orders total prices & items_json
            $orderCount = 0;
            \App\Models\Order::chunk(100, function ($orders) use ($rate, &$orderCount) {
                foreach ($orders as $order) {
                    $order->total_price = round($order->total_price * $rate, 2);
                    
                    $items = $order->items_json;
                    if (is_array($items)) {
                        foreach ($items as &$item) {
                            if (isset($item['price'])) {
                                $item['price'] = round($item['price'] * $rate, 2);
                            }
                            // Just in case final_price is stored
                            if (isset($item['final_price'])) {
                                $item['final_price'] = round($item['final_price'] * $rate, 2);
                            }
                        }
                        $order->items_json = $items;
                    }

                    $order->save();
                    $orderCount++;
                }
            });

            // Save new currency settings
            SiteSetting::updateOrCreate(['key' => 'site_currency'], ['value' => $newCurrency]);
            SiteSetting::updateOrCreate(['key' => 'site_currency_symbol'], ['value' => $symbol]);

            // Clear cached symbol
            Cache::forget('site_currency_symbol');

            return back()->with('success', "Currency changed from {$oldCurrency} to {$newCurrency} (rate: 1 {$oldCurrency} = {$rate} {$newCurrency}). Successfully updated {$count} products and {$orderCount} orders.");
        } catch (\Exception $e) {
            Log::error('Currency Update Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function detectSymbol(string $code): string
    {
        $symbols = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
            'LKR' => 'Rs.', 'INR' => '₹', 'AUD' => 'A$', 'CAD' => 'C$',
            'SGD' => 'S$', 'AED' => 'د.إ', 'CNY' => '¥', 'KRW' => '₩',
            'THB' => '฿', 'MYR' => 'RM', 'PHP' => '₱', 'IDR' => 'Rp',
            'BRL' => 'R$', 'ZAR' => 'R', 'TRY' => '₺', 'RUB' => '₽',
            'CHF' => 'CHF', 'SEK' => 'kr', 'NOK' => 'kr', 'DKK' => 'kr',
            'NZD' => 'NZ$', 'HKD' => 'HK$', 'MXN' => 'MX$', 'PKR' => 'Rs.',
            'BDT' => '৳', 'NGN' => '₦', 'EGP' => 'E£', 'SAR' => '﷼',
            'QAR' => 'QR', 'KWD' => 'KD', 'BHD' => 'BD', 'OMR' => 'OMR',
        ];

        return $symbols[$code] ?? $code;
    }
}
