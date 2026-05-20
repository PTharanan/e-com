<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteSetting;
use App\Models\Order;
use App\Models\Notification;
use Carbon\Carbon;

class SiteSettingController extends Controller
{
    public function autoDeleteIndex()
    {
        $userId = auth()->id();
        $isSeller = auth()->user()->role === 'seller';

        $settings = SiteSetting::where('user_id', $userId)
            ->where('key', 'like', 'auto_delete_%')
            ->pluck('value', 'key');

        $viewPrefix = $isSeller ? 'seller' : 'admin';
        return view("$viewPrefix.auto-delete", compact('settings'));
    }

    public function updateAutoDelete(Request $request)
    {
        $userId = auth()->id();
        $data = $request->validate([
            'auto_delete_delivered_value' => 'nullable|integer|min:0',
            'auto_delete_delivered_unit' => 'nullable|string|in:minutes,hours,days,months,years',
            'auto_delete_cancelled_value' => 'nullable|integer|min:0',
            'auto_delete_cancelled_unit' => 'nullable|string|in:minutes,hours,days,months,years',
            'auto_delete_refunded_value' => 'nullable|integer|min:0',
            'auto_delete_refunded_unit' => 'nullable|string|in:minutes,hours,days,months,years',
            'auto_delete_notifications_days' => 'nullable|integer|min:0',
            'auto_delete_returns_value' => 'nullable|integer|min:0',
            'auto_delete_returns_unit' => 'nullable|string|in:minutes,hours,days,months,years',
            'auto_delete_sessions_value' => 'nullable|integer|min:0',
            'auto_delete_sessions_unit' => 'nullable|string|in:minutes,hours,days,months,years',
        ]);

        foreach ($data as $key => $value) {
            $defaultValue = 'months';
            if (str_ends_with($key, '_value') || $key === 'auto_delete_notifications_days') {
                $defaultValue = 0;
            }
            SiteSetting::updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                ['value' => $value ?? $defaultValue]
            );
        }

        // Trigger immediate cleanup after saving
        \Illuminate\Support\Facades\Artisan::call('orders:auto-delete', ['--user' => $userId]);

        return back()->with('success', 'Auto-delete settings updated and cleanup triggered.');
    }

    public function runCleanup()
    {
        \Illuminate\Support\Facades\Artisan::call('orders:auto-delete', ['--user' => auth()->id()]);
        
        return response()->json([
            'success' => true,
            'message' => "Manual cleanup process triggered. Records are being processed."
        ]);
    }
}
