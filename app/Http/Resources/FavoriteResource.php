<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $meal = $this->meal;

        return [
            'id' => $meal->id,
            'title' => $meal->title,
            'slug' => $meal->slug,
            'description' => $meal->description,
            'image_url' => $meal->image_url,
            'offer_title' => $meal->offer_title,

            // Pricing
            ...$meal->getApiPriceAttributes(),
            'has_offer' => $meal->hasOffer(),

            // Rating & Details
            'rating' => (float) $meal->rating,
            'rating_count' => (int) $meal->rating_count,
            'size' => $meal->size,
            'brand' => $meal->brand,

            // Stock & Availability
            'stock_quantity' => $meal->stock_quantity,
            'in_stock' => $meal->isInStock(),
            'is_available' => $meal->is_available,
            'is_featured' => $meal->is_featured,

            // Category & Subcategory
            'category' => $meal->category ? [
                'id' => $meal->category->id,
                'name' => $meal->category->name,
                'slug' => $meal->category->slug,
            ] : null,
            'subcategory' => $meal->subcategory ? [
                'id' => $meal->subcategory->id,
                'name' => $meal->subcategory->name,
                'slug' => $meal->subcategory->slug,
            ] : null,

            'is_favorited' => true,
            'favorited_at' => $this->created_at,
        ];
    }
}