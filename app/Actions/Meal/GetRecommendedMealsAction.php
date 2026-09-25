<?php
namespace App\Actions\Meal;

use App\Models\Meal;
use Illuminate\Support\Collection;

class GetRecommendedMealsAction
{
    public function execute(int $limit = 10): Collection
    {
        $featuredMeals = Meal::with('category')
            ->available()
            ->featured()
            ->whereNotNull('discount_price')
            ->inRandomOrder()
            ->limit((int) ceil($limit / 2))
            ->get();

        $randomMeals = Meal::with('category')
            ->available()
            ->whereNotIn('id', $featuredMeals->pluck('id'))
            ->inRandomOrder()
            ->limit($limit - $featuredMeals->count())
            ->get();

        return $featuredMeals->merge($randomMeals)->shuffle()->take($limit)->map(function ($meal) {
            $meal->recommendation_reason = match (true) {
                $meal->is_featured && ! empty($meal->discount_price) => 'Featured with special offer',
                $meal->is_featured => 'Featured meal',
                ! empty($meal->discount_price) => 'Special offer',
                default => 'Popular choice',
            };
            return $meal;
        });
    }
}