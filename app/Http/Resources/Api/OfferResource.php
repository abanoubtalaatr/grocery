<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'code' => $this->code,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'discount_value' => $this->discount_value,
            'minimum_purchase' => $this->minimum_purchase,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'usage_limit' => $this->usage_limit,
            'used_count' => $this->used_count,
            'remaining_uses' => $this->usage_limit ? $this->usage_limit - $this->used_count : null,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'is_valid' => $this->isValid(),
            'days_remaining' => max(0, now()->diffInDays($this->end_date, false)),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    protected function getTypeLabel(): string
    {
        return match($this->type) {
            'percentage' => 'Percentage Discount',
            'fixed' => 'Fixed Amount',
            'buy_one_get_one' => 'Buy One Get One',
            'free_shipping' => 'Free Shipping',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}