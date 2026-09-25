<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmartListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'notify_on_price_drop' => (bool) ($this->notify_on_price_drop ?? true),
            'notify_on_offers' => (bool) ($this->notify_on_offers ?? true),
            'meals' => $this->whenLoaded('meals', fn () => $this->meals->map(fn ($meal) => new MealResource($meal))),
            'meals_count' => $this->when(isset($this->meals_count), fn () => $this->meals_count),
        ];
    }
}
