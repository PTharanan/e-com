<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerAssignment extends Model
{
    protected $fillable = ['seller_id', 'admin_id', 'status'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
