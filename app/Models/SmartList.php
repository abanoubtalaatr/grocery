<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmartList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'image',
        'user_id',
        'notify_on_price_drop',
        'notify_on_offers',
    ];

    protected $casts = [
        'notify_on_price_drop' => 'boolean',
        'notify_on_offers' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meals()
    {
        return $this->belongsToMany(Meal::class, 'smart_list_meals');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // If it's already a full URL, return as is
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        // Otherwise, generate URL from storage
        return asset('/images/smart-lists/' . $this->image);
    }
}
