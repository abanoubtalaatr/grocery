<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryMealsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'category' => [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
            ],
            'meals' => MealResource::collection($this->meals_paginator->items()),
            'pagination' => [
                'current_page' => $this->meals_paginator->currentPage(),
                'last_page' => $this->meals_paginator->lastPage(),
                'per_page' => $this->meals_paginator->perPage(),
                'total' => $this->meals_paginator->total(),
                'from' => $this->meals_paginator->firstItem(),
                'to' => $this->meals_paginator->lastItem(),
            ],
        ];
    }
}