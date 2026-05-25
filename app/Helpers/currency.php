<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('currency_symbol')) {
    function currency_symbol() {
        return Cache::remember('site_currency_symbol', 3600, function () {
            return SiteSetting::where('key', 'site_currency_symbol')->first()->value ?? '$';
        });
    }
}

if (!function_exists('format_price')) {
    function format_price($price) {
        $symbol = currency_symbol();
        return $symbol . number_format($price, 2);
    }
}
