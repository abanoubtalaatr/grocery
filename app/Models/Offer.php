<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'description',
        'type',
        'discount_value',
        'minimum_purchase',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Scope for active offers
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    // Scope for featured offers
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)
                     ->where('is_active', true)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    // Check if offer is valid
    public function isValid(): bool
    {
        return $this->is_active 
            && now()->between($this->start_date, $this->end_date)
            && ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }

    // Check if offer can be applied to amount
    public function canApplyToAmount(float $amount): bool
    {
        if ($this->minimum_purchase === null) {
            return true;
        }
        return $amount >= $this->minimum_purchase;
    }

    // Calculate discount
    public function calculateDiscount(float $amount): float
    {
        if (!$this->isValid() || !$this->canApplyToAmount($amount)) {
            return 0;
        }

        return match($this->type) {
            'percentage' => $amount * ($this->discount_value / 100),
            'fixed' => min($this->discount_value, $amount),
            default => 0,
        };
    }
}