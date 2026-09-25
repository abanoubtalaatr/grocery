<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meal' => [
                'id' => $this->meal->id,
                'title' => $this->meal->title,
                'slug' => $this->meal->slug,
                'image_url' => $this->meal->image_url,
                ...$this->meal->getApiPriceAttributes(),
                'rating' => (float) $this->meal->rating,
                'size' => $this->meal->size,
                'brand' => $this->meal->brand,
                'stock_quantity' => $this->meal->stock_quantity,
                'is_available' => $this->meal->is_available,
                'in_stock' => $this->meal->isInStock(),
                'category' => $this->meal->category ? [
                    'id' => $this->meal->category->id,
                    'name' => $this->meal->category->name,
                ] : null,
                'subcategory' => $this->meal->subcategory ? [
                    'id' => $this->meal->subcategory->id,
                    'name' => $this->meal->subcategory->name,
                ] : null,
            ],
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount_amount' => (float) $this->discount_amount,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}