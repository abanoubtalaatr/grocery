<?php
namespace App\Actions\Favorite;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ToggleFavoriteAction
{
    public function execute(User $user, Meal $meal): array
    {
        return DB::transaction(function () use ($user, $meal) {
            $favorite = $user->favorites()->where('meal_id', $meal->id)->first();

            if ($favorite) {
                $favorite->delete();
                return [
                    'is_favorited' => false,
                    'message' => 'Removed from favorites',
                ];
            }

            $user->favorites()->create(['meal_id' => $meal->id]);

            return [
                'is_favorited' => true,
                'message' => 'Added to favorites',
            ];
        });
    }
}