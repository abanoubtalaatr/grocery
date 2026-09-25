<?php
namespace App\Actions\Meal;

use App\Models\Meal;
use App\Models\User;

class GetFilteredMealsAction
{
    public function __construct(protected MarkMealFavoritesAction $markFavorites) {}

    public function execute(array $filters, ?User $user): array
    {
        $meals = Meal::with(['category', 'subcategory'])
            ->available()
            ->filter($filters)
            ->get();

        $meals = $this->markFavorites->execute($meals, $user);
        $isEmpty = $meals->isEmpty();

        return [
            'meals' => $meals,
            'total_count' => $meals->count(),
            'is_empty' => $isEmpty,
            'filters_applied' => array_intersect_key($filters, array_flip([
                'search', 'category_id', 'subcategory_id', 'min_price', 'max_price',
                'min_rating', 'brand', 'featured', 'in_stock', 'sort_by', 'sort_order',
            ])),
        ];
    }
}