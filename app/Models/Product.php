<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'seller_id',
        'category_id',
        'name',
        'description',
        'price',
        'discount_percentage',
        'is_new',
        'stock_status',
        'stock_quantity',
        'image_urls',
        'main_image_url',
    ];

    protected $appends = ['final_price'];

    protected $casts = [
        'image_urls' => 'array',
        'discount_percentage' => 'integer',
        'is_new' => 'boolean',
    ];

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getStockStatusAttribute($value)
    {
        return $this->stock_quantity > 0 ? 'available' : 'not';
    }

    public function getFinalPriceAttribute()
    {
        if ($this->discount_percentage && $this->discount_percentage > 0) {
            return $this->price - ($this->price * ($this->discount_percentage / 100));
        }
        return $this->price;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    protected static function booted()
    {
        static::saving(function ($product) {
            if ($product->stock_quantity > 0) {
                $product->stock_status = 'available';
            } else {
                $product->stock_status = 'not';
            }
        });

        static::updated(function ($product) {
            // Trigger notification only if stock changed from > 0 to 0
            if ($product->stock_quantity == 0 && $product->getOriginal('stock_quantity') > 0) {
                // Determine the recipient (Seller if it exists, otherwise Admin)
                $recipient = $product->seller ?? $product->admin;
                
                if ($recipient) {
                    $recipient->notify(new \App\Notifications\ProductOutOfStockNotification($product));
                }
            }
        });
    }
}
