<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = Category::active()
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

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single category with meals
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = Category::with(['meals' => function ($query) {
                $query->available()->orderBy('created_at', 'desc');
            }])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $category->image_url,
                    'sort_order' => $category->sort_order,
                    'meals' => $category->meals->map(function ($meal) {
                        return [
                            'id' => $meal->id,
                            'title' => $meal->title,
                            'slug' => $meal->slug,
                            'description' => $meal->description,
                            'image_url' => $meal->image_url,
                            'offer_title' => $meal->offer_title,
                            'price' => $meal->price,
                            'discount_price' => $meal->discount_price,
                            'final_price' => $meal->final_price,
                            'rating' => $meal->rating,
                            'rating_count' => $meal->rating_count,
                            'has_offer' => $meal->hasOffer(),
                            'is_featured' => $meal->is_featured,
                        ];
                    }),
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get meals by category
     */
    public function meals(string $id, Request $request): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            
            $query = $category->meals()->with(['subcategory'])->available();

            // Filter by featured if provided
            if ($request->has('featured') && $request->boolean('featured')) {
                $query->featured();
            }

            // Filter by subcategory if provided
            if ($request->has('subcategory_id')) {
                $query->where('subcategory_id', $request->input('subcategory_id'));
            }

            // Filter by in stock only
            if ($request->has('in_stock') && $request->boolean('in_stock')) {
                $query->inStock();
            }

            $meals = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($meal) {
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
                        'is_featured' => $meal->is_featured,
                        
                        // Expiry
                        'expiry_date' => $meal->expiry_date,
                        'days_until_expiry' => $meal->daysUntilExpiry(),
                        'is_expired' => $meal->isExpired(),
                        
                        // Subcategory
                        'subcategory' => $meal->subcategory ? [
                            'id' => $meal->subcategory->id,
                            'name' => $meal->subcategory->name,
                            'slug' => $meal->subcategory->slug,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Meals retrieved successfully',
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'meals' => $meals,
                    'total_count' => $meals->count(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
