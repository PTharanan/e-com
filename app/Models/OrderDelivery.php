<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    protected $fillable = [
        'order_id',
        'delivery_boy_id',
        'pickup_image',
        'delivery_image',
        'secret_code',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }
}
