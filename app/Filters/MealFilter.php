<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class MealFilter
{
    public static function apply(Builder $builder, array $filters): Builder
    {
        return $builder
            ->when($filters['search'] ?? null, fn ($q, $search) => 
                $q->where(fn ($sub) => 
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                )
            )
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['subcategory_id'] ?? null, fn ($q, $id) => $q->where('subcategory_id', $id))
            ->when($filters['min_price'] ?? null, fn ($q, $min) => $q->whereRaw('COALESCE(discount_price, price) >= ?', [$min]))
            ->when($filters['max_price'] ?? null, fn ($q, $max) => $q->whereRaw('COALESCE(discount_price, price) <= ?', [$max]))
            ->when($filters['brand'] ?? null, fn ($q, $brand) => $q->where('brand', $brand));
    }
}