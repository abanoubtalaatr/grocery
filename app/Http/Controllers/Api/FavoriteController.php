<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get all user's favorite meals.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->get()
            ->map(function ($favorite) {
                $meal = $favorite->meal;

                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,

                    // Pricing
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),

                    // Rating & Details
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'size' => $meal->size,
                    'brand' => $meal->brand,

                    // Stock & Availability
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock' => $meal->isInStock(),
                    'is_available' => $meal->is_available,
                    'is_featured' => $meal->is_featured,

                    // Category & Subcategory
                    'category' => [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name,
                        'slug' => $meal->category->slug,
                    ],
                    'subcategory' => $meal->subcategory ? [
                        'id' => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                        'slug' => $meal->subcategory->slug,
                    ] : null,

                    'is_favorited' => true,
                    'favorited_at' => $favorite->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Favorites retrieved successfully',
            'data' => $favorites,
            'total_count' => $favorites->count(),
        ]);
    }

    /**
     * Toggle favorite status for a meal.
     */
    public function toggle(Request $request, Meal $meal): JsonResponse
    {
        $user = $request->user();

        $favorite = $user->favorites()
            ->where('meal_id', $meal->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            $isFavorited = false;
            $message = 'Removed from favorites';
        } else {
            $user->favorites()->create([
                'meal_id' => $meal->id,
            ]);

            $isFavorited = true;
            $message = 'Added to favorites';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'meal_id' => $meal->id,
                'is_favorited' => $isFavorited,
            ],
        ]);
    }

    /**
     * Check if a meal is favorited.
     */
    public function check(Request $request, Meal $meal): JsonResponse
    {
        $user = $request->user();

        $isFavorited = $user->favorites()
            ->where('meal_id', $meal->id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'meal_id' => $meal->id,
                'is_favorited' => $isFavorited,
            ],
        ]);
    }

    /**
     * Remove meal from favorites.
     */
    public function remove(Request $request, Meal $meal): JsonResponse
    {
        $user = $request->user();

        $deleted = $user->favorites()
            ->where('meal_id', $meal->id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Removed from favorites',
                'data' => [
                    'meal_id' => $meal->id,
                    'is_favorited' => false,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Meal was not in favorites',
        ], 404);
    }
}