<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryMealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $meal = $this->resource;

        return [
            'id' => $meal->id,
            'title' => $meal->title,
            'slug' => $meal->slug,
            'description' => $meal->description,
            'image_url' => $meal->image_url,
            'offer_title' => $meal->offer_title,
            ...$meal->getApiPriceAttributes(),
            'has_offer' => $meal->hasOffer(),
            'rating' => (float) $meal->rating,
            'rating_count' => (int) $meal->rating_count,
            'size' => $meal->size,
            'brand' => $meal->brand,
            'stock_quantity' => $meal->stock_quantity,
            'in_stock' => $meal->isInStock(),
            'is_featured' => $meal->is_featured,
            'expiry_date' => $meal->expiry_date,
            'days_until_expiry' => $meal->daysUntilExpiry(),
            'is_expired' => $meal->isExpired(),
            'features' => $meal->features,
            'subcategory' => $meal->subcategory ? [
                'id' => $meal->subcategory->id,
                'name' => $meal->subcategory->name,
                'slug' => $meal->subcategory->slug,
            ] : null,
        ];
    }
}
