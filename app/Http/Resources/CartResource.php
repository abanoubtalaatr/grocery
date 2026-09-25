<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->isEmpty() ? 'empty' : 'not empty',
            'items' => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'meal' => [
                    'id' => $item->meal->id,
                    'title' => $item->meal->title,
                    'slug' => $item->meal->slug,
                    'image_url' => $item->meal->image_url,
                    ...$item->meal->getApiPriceAttributes(),
                    'rating' => (float) $item->meal->rating,
                    'size' => $item->meal->size,
                    'brand' => $item->meal->brand,
                    'stock_quantity' => $item->meal->stock_quantity,
                    'is_available' => $item->meal->is_available,
                    'in_stock' => $item->meal->isInStock(),
                    'category' => $item->meal->category ? [
                        'id' => $item->meal->category->id,
                        'name' => $item->meal->category->name,
                    ] : null,
                    'subcategory' => $item->meal->subcategory ? [
                        'id' => $item->meal->subcategory->id,
                        'name' => $item->meal->subcategory->name,
                    ] : null,
                ],
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_amount' => (float) $item->discount_amount,
                'subtotal' => (float) $item->subtotal,
            ]),
            'item_count' => $this->item_count,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'shipping_fee' => isset($this->shipping_fee) ? (float) $this->shipping_fee : null,
            'total_with_shipping' => isset($this->total_with_shipping) ? (float) $this->total_with_shipping : null,
            'is_empty' => $this->isEmpty(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}