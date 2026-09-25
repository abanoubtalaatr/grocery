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
                            ...$meal->getApiPriceAttributes(),
                            'rating' => (float) $meal->rating,
                            'is_featured' => $meal->is_featured,
                            'features' => $meal->features,
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
     * Get meals by subcategory (paginated)
     */
    public function meals(string $id, Request $request): JsonResponse
    {
        try {
            $subcategory = Subcategory::findOrFail($id);

            $query = $subcategory->meals()->with('category')->available();

            // Filter by featured if provided (featured=1/true → featured only, featured=0/false → non-featured only)
            if ($request->has('featured')) {
                $request->boolean('featured') ? $query->featured() : $query->where('is_featured', false);
            }

            // Filter by in stock (in_stock=1/true → in stock only, in_stock=0/false → out of stock only)
            if ($request->has('in_stock')) {
                $request->boolean('in_stock') ? $query->inStock() : $query->outOfStock();
            }

            // Sorting (sort_by: created_at|price|rating|title|sold_count|newest, sort_order: asc|desc)
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
            if ($sortBy === 'newest') {
                $sortBy = 'created_at';
                $sortOrder = 'desc';
            }
            $allowedSortFields = ['created_at', 'price', 'rating', 'title', 'sold_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                if ($sortBy === 'price') {
                    $query->orderByRaw('COALESCE(discount_price, price) ' . $sortOrder);
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
            $paginator = $query
                ->paginate($perPage)
                ->withQueryString();

            $meals = $paginator->getCollection()->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'has_offer' => $meal->hasOffer(),
                    'is_featured' => $meal->is_featured,
                    'in_stock' => $meal->isInStock(),
                    'features' => $meal->features,
                ];
            });

            $total = $paginator->total();
            return response()->json(array_merge([
                'success' => true,
                'message' => $total === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
                'data' => [
                    'subcategory' => [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                    ],
                    'meals' => $meals->values()->all(),
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $total,
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                    ],
                ],
            ], $total === 0 ? ['empty_message' => 'No products match the applied filters. Try adjusting your filters.'] : []));
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
