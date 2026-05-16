<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'payment_intent_id',
        'total_price',
        'total_items',
        'items_json',
        'delivery_boy_id',
        'pickup_image',
        'secret_code',
        'status',
        'assignment_type',
        'delivered_at',
    ];

    protected $casts = [
        'items_json' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class)->latestOfMany();
    }

    public function processDeliveryPayment()
    {
        if ($this->status !== 'delivered' && $this->delivery_boy_id) {
            $app = DeliveryApplication::where('delivery_boy_id', $this->delivery_boy_id)
                ->where('status', 'approved')
                ->first();

            if ($app && $app->delivery_fee > 0) {
                DB::transaction(function () use ($app) {
                    $storeOwner = $app->storeOwner;
                    $deliveryBoy = $this->deliveryBoy;

                    if ($storeOwner && $deliveryBoy) {
                        $storeOwner->decrement('balance', $app->delivery_fee);
                        $deliveryBoy->increment('balance', $app->delivery_fee);
                    }
                });
                return $app->delivery_fee;
            }
        }
        return 0;
    }
}
