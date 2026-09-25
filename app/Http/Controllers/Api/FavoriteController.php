<?php
namespace App\Http\Controllers\Api;

use App\Actions\Favorite\ToggleFavoriteAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Meal;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ResponseTrait;

    /**
     * Get all user's favorite meals.
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->get();

        return $this->successResponse([
            'favorites' => FavoriteResource::collection($favorites),
            'total_count' => $favorites->count(),
        ], 'Favorites retrieved successfully');
    }

    /**
     * Toggle favorite status for a meal.
     */
    public function toggle(Request $request, Meal $meal, ToggleFavoriteAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $meal);

        return $this->successResponse([
            'meal_id' => $meal->id,
            'is_favorited' => $result['is_favorited'],
        ], $result['message']);
    }

    /**
     * Check if a meal is favorited.
     */
    public function check(Request $request, Meal $meal): JsonResponse
    {
        $isFavorited = $request->user()
            ->favorites()
            ->where('meal_id', $meal->id)
            ->exists();

        return $this->successResponse([
            'meal_id' => $meal->id,
            'is_favorited' => $isFavorited,
        ], 'Favorite status checked successfully');
    }

    /**
     * Remove meal from favorites.
     */
    public function remove(Request $request, Meal $meal): JsonResponse
    {
        $deleted = $request->user()
            ->favorites()
            ->where('meal_id', $meal->id)
            ->delete();

        if (! $deleted) {
            return $this->errorResponse('Meal was not in favorites', 404);
        }

        return $this->successResponse([
            'meal_id' => $meal->id,
            'is_favorited' => false,
        ], 'Removed from favorites successfully');
    }
}