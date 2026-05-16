<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'is_verified',
        'role',
        'balance',
        'phno',
        'address',
        'admin_id',
        'is_blocked',
    ];

    public function info()
    {
        return $this->hasOne(UserInfo::class);
    }

    public function applications()
    {
        return $this->hasMany(DeliveryApplication::class, 'delivery_boy_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function assignedOrders()
    {
        return $this->hasMany(Order::class, 'delivery_boy_id');
    }

    public function subSellers()
    {
        return $this->hasMany(User::class, 'admin_id');
    }

    public function sellerAssignments()
    {
        return $this->hasMany(SellerAssignment::class, 'seller_id');
    }

    public function storeAssignments()
    {
        return $this->hasMany(SellerAssignment::class, 'admin_id');
    }
}
