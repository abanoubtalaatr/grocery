<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'image_url'   => $this->image_url,
            'order'       => $this->order,
            'is_active'   => $this->whenHas('is_active'),
            'category'    => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'meals'       => MealResource::collection($this->whenLoaded('meals')),
            'meals_count' => $this->when(
                isset($this->meals_count),
                fn () => (int) $this->meals_count,
                fn () => $this->meals()->available()->count()
            ),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->whenHas('updated_at'),
        ];
    }
}