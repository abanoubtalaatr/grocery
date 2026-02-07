<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Get all subcategories
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Subcategory::with('category')->active();

            // Filter by category if provided
            if ($request->has('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            $subcategories = $query->inRandomOrder()
                ->get()
                ->map(function ($subcategory) {
                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                        'description' => $subcategory->description,
                        'image_url' => $subcategory->image_url,
                        'order' => $subcategory->order,
                        'category' => [
                            'id' => $subcategory->category->id,
                            'name' => $subcategory->category->name,
                            'slug' => $subcategory->category->slug,
                        ],
                        'meals_count' => $subcategory->meals()->available()->count(),
                        'created_at' => $subcategory->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Subcategories retrieved successfully',
                'data' => $subcategories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subcategories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single subcategory
     */
    public function show(string $id): JsonResponse
    {
        try {
            $subcategory = Subcategory::with(['category', 'meals' => function ($query) {
                $query->available()->limit(10);
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Subcategory retrieved successfully',
                'data' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                    'description' => $subcategory->description,
                    'image_url' => $subcategory->image_url,
                    'order' => $subcategory->order,
                    'is_active' => $subcategory->is_active,
                    'category' => [
                        'id' => $subcategory->category->id,
                        'name' => $subcategory->category->name,
                        'slug' => $subcategory->category->slug,
                    ],
                    'meals' => $subcategory->meals->map(function ($meal) {
                        return [
                            'id' => $meal->id,
                            'title' => $meal->title,
                            'slug' => $meal->slug,
                            'image_url' => $meal->image_url,
                            'price' => $meal->price,
                            'discount_price' => $meal->discount_price,
                            'final_price' => $meal->final_price,
                            'rating' => $meal->rating,
                            'is_featured' => $meal->is_featured,
                        ];
                    }),
                    'meals_count' => $subcategory->meals()->available()->count(),
                    'created_at' => $subcategory->created_at,
                    'updated_at' => $subcategory->updated_at,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subcategory',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get meals by subcategory
     */
    public function meals(string $id, Request $request): JsonResponse
    {
        try {
            $subcategory = Subcategory::findOrFail($id);
            
            $query = $subcategory->meals()->with('category')->available();

            // Filter by featured if provided
            if ($request->has('featured') && $request->boolean('featured')) {
                $query->featured();
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
                        'price' => $meal->price,
                        'discount_price' => $meal->discount_price,
                        'final_price' => $meal->final_price,
                        'rating' => $meal->rating,
                        'rating_count' => $meal->rating_count,
                        'has_offer' => $meal->hasOffer(),
                        'is_featured' => $meal->is_featured,
                        'in_stock' => $meal->isInStock(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Meals retrieved successfully',
                'data' => [
                    'subcategory' => [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                    ],
                    'meals' => $meals,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found',
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
