<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = $this->resource;

        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image_url' => $category->image_url,
            'meals_count' => method_exists($category, 'meals_count') ? $category->meals_count : $category->meals()->count(),
            'sort_order' => $category->sort_order,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];

        if ($category->relationLoaded('meals') || isset($category->meals)) {
            $data['meals'] = CategoryMealResource::collection($category->meals)->resolve();
        }

        return $data;
    }
}
