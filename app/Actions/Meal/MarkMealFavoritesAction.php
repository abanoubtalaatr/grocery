<?php
namespace App\Actions\Meal;

use App\Models\User;
use Illuminate\Support\Collection;

class MarkMealFavoritesAction
{
    public function execute(Collection $meals, ?User $user): Collection
    {
        if (! $user) {
            return $meals;
        }

        $favoriteMealIds = $user->favorites()->pluck('meal_id')->toArray();

        return $meals->each(function ($meal) use ($favoriteMealIds) {
            $meal->is_favorited = in_array($meal->id, $favoriteMealIds, true);
        });
    }
}