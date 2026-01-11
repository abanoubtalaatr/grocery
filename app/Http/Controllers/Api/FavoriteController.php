<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /**
     * Get all user's favorite meals
     */
    public function index(Request $request): JsonResponse
    {
        try {
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
                        'price' => $meal->price,
                        'discount_price' => $meal->discount_price,
                        'final_price' => $meal->final_price,
                        'has_offer' => $meal->hasOffer(),
                        
                        // Rating & Details
                        'rating' => $meal->rating,
                        'rating_count' => $meal->rating_count,
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve favorites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle favorite status for a meal
     */
    public function toggle(Request $request, string $mealId): JsonResponse
    {
        try {
            $user = $request->user();
            $meal = Meal::findOrFail($mealId);

            DB::beginTransaction();

            $favorite = $user->favorites()->where('meal_id', $meal->id)->first();

            if ($favorite) {
                // Remove from favorites
                $favorite->delete();
                $isFavorited = false;
                $message = 'Removed from favorites';
            } else {
                // Add to favorites
                $user->favorites()->create([
                    'meal_id' => $meal->id,
                ]);
                $isFavorited = true;
                $message = 'Added to favorites';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'meal_id' => $meal->id,
                    'is_favorited' => $isFavorited,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle favorite',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a meal is favorited
     */
    public function check(Request $request, string $mealId): JsonResponse
    {
        try {
            $user = $request->user();
            $meal = Meal::findOrFail($mealId);

            $isFavorited = $user->favorites()->where('meal_id', $meal->id)->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'meal_id' => $meal->id,
                    'is_favorited' => $isFavorited,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check favorite status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove meal from favorites
     */
    public function remove(Request $request, string $mealId): JsonResponse
    {
        try {
            $user = $request->user();
            $meal = Meal::findOrFail($mealId);

            $deleted = $user->favorites()->where('meal_id', $meal->id)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Removed from favorites',
                    'data' => [
                        'meal_id' => $meal->id,
                        'is_favorited' => false,
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Meal was not in favorites',
                ], 404);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from favorites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
