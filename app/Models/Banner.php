<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'image_url',
        'badge_text',
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'is_active',
        'order',
        'admin_id'
    ];
}
