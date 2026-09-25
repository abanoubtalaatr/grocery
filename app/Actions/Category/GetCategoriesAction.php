<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Support\Collection;

class GetCategoriesAction
{
    public function handle(): Collection
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $category->image_url,
                    'meals_count' => $category->meals_count,
                    'sort_order' => $category->sort_order,
                    'created_at' => $category->created_at,
                ];
            });
    }
}