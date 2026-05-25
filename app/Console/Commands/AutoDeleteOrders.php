<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SiteSetting;
use App\Models\Order;
use App\Models\Notification;
use App\Models\OrderDelivery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoDeleteOrders extends Command
{
    protected $signature = 'orders:auto-delete {--user= : Only run cleanup for a specific user ID}';
    protected $description = 'Automatically delete orders based on user settings';

    public function handle()
    {
        $this->info('Starting auto-delete process...');

        $userIdOption = $this->option('user');

        // Get all users who have auto-delete settings
        $query = SiteSetting::where('key', 'like', 'auto_delete_%');
        if ($userIdOption) {
            $query->where('user_id', $userIdOption);
        }
        $settings = $query->get()->groupBy('user_id');

        foreach ($settings as $userId => $userSettings) {
            $userSettings = $userSettings->pluck('value', 'key');
            $user = \App\Models\User::find($userId);
            if (!$user)
                continue;

            $isSeller = $user->role === 'seller';

            // 1. Delivered Orders
            $this->cleanupStatus($user, 'delivered', $userSettings, $isSeller);

            // 2. Cancelled Orders
            $this->cleanupStatus($user, 'cancelled', $userSettings, $isSeller);

            // 3. Refunded Orders
            $this->cleanupStatus($user, 'refunded', $userSettings, $isSeller);

            // 4. Notifications (Global for Admin, User-specific for Seller)
            $this->cleanupNotifications($user, $userSettings, $isSeller);

            // 5. Order Returns (Purge completed/rejected return requests)
            $this->cleanupOrderReturns($user, $userSettings);
        }

        // 6. Global Session Cleanup (Older than session lifetime)
        if (!$userIdOption || (\App\Models\User::find($userIdOption)?->role === 'admin')) {
            $sessionLifetime = 0;
            $adminSetting = SiteSetting::where('key', 'like', 'auto_delete_sessions_%')->get()->pluck('value', 'key');
            if ($adminSetting->has('auto_delete_sessions_value') && (int) $adminSetting->get('auto_delete_sessions_value') > 0) {
                $val = (int) $adminSetting->get('auto_delete_sessions_value');
                $unit = $adminSetting->get('auto_delete_sessions_unit', 'hours');
                if ($unit === 'minutes') {
                    $sessionLifetime = $val * 60;
                } elseif ($unit === 'hours') {
                    $sessionLifetime = $val * 3600;
                } elseif ($unit === 'days') {
                    $sessionLifetime = $val * 86400;
                } elseif ($unit === 'months') {
                    $sessionLifetime = $val * 2592000;
                } elseif ($unit === 'years') {
                    $sessionLifetime = $val * 31536000;
                }
            }
            if ($sessionLifetime <= 0) {
                $sessionLifetime = config('session.lifetime', 120) * 60;
            }
            DB::statement("CALL sp_auto_delete_expired_sessions(?)", [$sessionLifetime]);
        }

        $this->info('Auto-delete process finished.');
    }

    private function cleanupStatus($user, $status, $settings, $isSeller)
    {
        $valueKey = "auto_delete_{$status}_value";
        $unitKey = "auto_delete_{$status}_unit";

        $value = $settings->get($valueKey, 0);
        $unit = $settings->get($unitKey, 'months');

        if ($value <= 0)
            return;

        // Call the procedure to get files and delete records
        $results = DB::select("CALL sp_auto_delete_orders(?, ?, ?, ?, ?)", [
            (int) $user->id,
            $status,
            (int) $value,
            $unit,
            $isSeller ? 1 : 0
        ]);

        foreach ($results as $row) {
            $this->deleteFilesFromRow($row);
        }
    }

    private function cleanupNotifications($user, $settings, $isSeller)
    {
        $value = $settings->get('auto_delete_notifications_days', 0);
        if ($value <= 0)
            return;

        DB::statement("CALL sp_auto_delete_notifications(?, ?)", [
            (int) $user->id,
            (int) $value
        ]);
    }

    private function cleanupOrderReturns($user, $settings)
    {
        $value = $settings->get('auto_delete_returns_value', 0);
        $unit = $settings->get('auto_delete_returns_unit', 'months');

        if ($value <= 0)
            return;

        $results = DB::select("CALL sp_auto_delete_order_returns(?, ?, ?, ?)", [
            (int) $user->id,
            (int) $value,
            $unit,
            $user->role === 'seller' ? 1 : 0
        ]);

        foreach ($results as $row) {
            $paths = [
                $row->pickup_image,
                $row->store_image
            ];
            foreach ($paths as $path) {
                if ($path && file_exists(public_path($path))) {
                    @unlink(public_path($path));
                }
            }
        }
    }

    private function getThreshold($value, $unit)
    {
        // No longer needed in PHP, but keeping for compatibility if other methods use it
        $now = Carbon::now();
        switch ($unit) {
            case 'minutes':
                return $now->subMinutes($value);
            case 'hours':
                return $now->subHours($value);
            case 'days':
                return $now->subDays($value);
            case 'months':
                return $now->subMonths($value);
            case 'years':
                return $now->subYears($value);
            default:
                return $now->subMonths($value);
        }
    }

    private function deleteFilesFromRow($row)
    {
        $paths = [
            $row->order_pickup_image ?? null,
            $row->delivery_pickup_image ?? null,
            $row->delivery_delivery_image ?? null
        ];

        foreach ($paths as $path) {
            if ($path && file_exists(public_path($path))) {
                @unlink(public_path($path));
            }
        }
    }

    private function deleteOrderFiles($order)
    {
        // No longer needed as deleteFilesFromRow handles it, but keeping to avoid errors if called elsewhere
    }
}
