<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'delivery_boy_id',
        'assignment_type',
        'pickup_image',
        'store_image',
        'reason',
        'rejection_reason',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function getStoreOwnerAttribute()
    {
        return $this->order->admin;
    }
}
