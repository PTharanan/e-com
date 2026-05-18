<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryApplication;
use Illuminate\Support\Facades\Auth;

class AdminDeliveryController extends Controller
{
    public function index()
    {
        // Get all applications aimed at the currently logged in admin/seller
        $applications = DeliveryApplication::with(['deliveryBoy.info'])
            ->where('store_owner_id', Auth::id())
            ->latest()
            ->get();

        // For each applicant, load their full work history and current employment status
        $applicantHistories = [];
        $isEmployedElsewhere = [];
        foreach ($applications as $app) {
            $applicantHistories[$app->delivery_boy_id] = DeliveryApplication::with('storeOwner')
                ->where('delivery_boy_id', $app->delivery_boy_id)
                ->where('store_owner_id', '!=', Auth::id())
                ->where('status', 'rejected')
                ->whereNotNull('fire_reason')
                ->get();

            // Check if currently approved at another store
            $otherStoreJob = DeliveryApplication::with('storeOwner')
                ->where('delivery_boy_id', $app->delivery_boy_id)
                ->where('store_owner_id', '!=', Auth::id())
                ->where('status', 'approved')
                ->first();
                
            $isEmployedElsewhere[$app->delivery_boy_id] = $otherStoreJob ? ($otherStoreJob->storeOwner->role === 'admin' ? 'E-Shop' : $otherStoreJob->storeOwner->name) : null;
        }

        $viewPrefix = auth()->check() && auth()->user()->role === 'seller' ? 'seller' : 'admin'; 
        return view("$viewPrefix.delivery", compact('applications', 'applicantHistories', 'isEmployedElsewhere'));
    }

    public function assignWork()
    {
        // 1. Get all approved delivery boys for this store
        $deliveryBoys = \App\Models\User::where('role', 'delivery_boy')
            ->whereHas('applications', function($q) {
                $q->where('store_owner_id', Auth::id())
                    ->where('status', 'approved');
            })
            ->withCount(['assignedOrders as active_orders_count' => function($q) {
                $q->whereIn('status', ['processing', 'shipped']);
            }])
            ->get();

        // If a seller has no delivery boys of their own, let them assign their admin's delivery boys
        if (auth()->check() && auth()->user()->role === 'seller' && $deliveryBoys->isEmpty()) {
            $deliveryBoys = \App\Models\User::where('role', 'delivery_boy')
                ->whereHas('applications', function($q) {
                    $q->where('store_owner_id', auth()->user()->admin_id)
                        ->where('status', 'approved');
                })
                ->withCount(['assignedOrders as active_orders_count' => function($q) {
                    $q->whereIn('status', ['processing', 'shipped']);
                }])
                ->get();
        }

        // 2. Get all unassigned orders that are "Payment Complet" (completed)
        $unassignedOrders = \App\Models\Order::with('user')
            ->whereNull('delivery_boy_id')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $viewPrefix = auth()->check() && auth()->user()->role === 'seller' ? 'seller' : 'admin'; return view("$viewPrefix.assign_work", compact('deliveryBoys', 'unassignedOrders'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'fire_reason' => 'nullable|string|max:500',
            'delivery_fee' => 'nullable|numeric|min:0',
        ]);

        $application = DeliveryApplication::where('store_owner_id', Auth::id())->findOrFail($id);

        if ($request->status === 'approved') {
            // Double check they aren't working elsewhere
            $exists = DeliveryApplication::where('delivery_boy_id', $application->delivery_boy_id)
                ->where('store_owner_id', '!=', Auth::id())
                ->where('status', 'approved')
                ->exists();
            
            if ($exists) {
                return back()->with('error', "Cannot hire this partner. They are currently employed at another store.");
            }
        }
        $application->status = $request->status;

        // Save fire/reject reason if provided
        if ($request->status === 'rejected' && $request->fire_reason) {
            $application->fire_reason = $request->fire_reason;
        }

        // Set salary and clear fire_reason if hiring
        if ($request->status === 'approved') {
            $application->fire_reason = null;
            if ($request->delivery_fee) {
                $application->delivery_fee = $request->delivery_fee;
            }
        }

        $application->save();

        // Remove the notification for this specific delivery boy
        Auth::user()->notifications()
            ->where('type', 'like', '%DeliveryApplicationNotification')
            ->get()
            ->filter(function ($notification) use ($application) {
                return ($notification->data['delivery_boy_id'] ?? null) == $application->delivery_boy_id;
            })
            ->each(function ($notification) {
                $notification->delete();
            });

        // Notify the delivery boy when hired
        if ($request->status === 'approved') {
            $deliveryBoy = $application->deliveryBoy;
            $fee = $application->delivery_fee ?? 0;
            $storeOwnerName = Auth::user()->role === 'admin' ? 'E-Shop' : Auth::user()->name;
            $deliveryBoy->notify(new \App\Notifications\DeliveryHiredNotification($storeOwnerName, $fee));
        }

        $action = $request->status === 'approved' ? 'approved' : 'rejected';
        
        return back()->with('success', "Delivery partner successfully {$action}.");
    }

    public function releasePartner($id)
    {
        // Release/Fire a partner so they can join another store
        $application = DeliveryApplication::where('store_owner_id', Auth::id())
            ->where('delivery_boy_id', $id)
            ->firstOrFail();
            
        $application->delete(); // Or change status to 'fired' if you want history

        return back()->with('success', "Delivery partner has been released from your store.");
    }
    public function updateFee(Request $request, $id)
    {
        $request->validate([
            'delivery_fee' => 'required|numeric|min:0'
        ]);

        $app = DeliveryApplication::where('store_owner_id', \Illuminate\Support\Facades\Auth::id())
            ->findOrFail($id);

        $app->update(['delivery_fee' => $request->delivery_fee]);

        return back()->with('success', 'Delivery fee updated for ' . $app->deliveryBoy->name);
    }
}
