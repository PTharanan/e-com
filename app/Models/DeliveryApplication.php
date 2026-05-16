<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_boy_id',
        'store_owner_id',
        'status',
        'delivery_fee',
        'fire_reason',
    ];

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function storeOwner()
    {
        return $this->belongsTo(User::class, 'store_owner_id');
    }
}
