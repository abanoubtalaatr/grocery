<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{

    public function frequency(Request $request)
    {
        $meals = Meal::with('category')
            ->available()
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Featured retrieved successfully',
            'data' => $meals,
        ]);
    }
    
    public function moreToExplore(Request $request)
    {
        $meals = Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'More to explore retrieved successfully',
            'data' => $meals,
        ]);
    }

    public function brands(Request $request)
    {
        $brands = Meal::distinct()->pluck('brand');
        return response()->json([
            'success' => true,
            'message' => 'Brands retrieved successfully',
            'data' => $brands,
        ]);
    }
    public function slider(Request $request)
    {
        $meals = Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                    'category' => [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name,
                    ],
                    'available_date' => $meal->available_date,
                    'created_at' => $meal->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Today\'s meals retrieved successfully',
            'data' => $meals,
        ]);
    }
    public function bestSells(Request $request)
    {
        $meals = Meal::with('category')
            ->available()

            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Best sells retrieved successfully',
            'data' => $meals,
        ]);
    }
    public function newProducts(Request $request)
    {

        try {
            $meals = Meal::with('category')
                ->available()
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'New products retrieved successfully',
                'data' => $meals,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Get today's meals
     */
    public function hot(Request $request): JsonResponse
    {
        try {
            $meals = Meal::with('category')
                ->available()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($meal) {
                    return [
                        'id' => $meal->id,
                        'title' => $meal->title,
                        'slug' => $meal->slug,
                        'description' => $meal->description,
                        'image_url' => $meal->image_url,
                        'offer_title' => $meal->offer_title,
...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                        'category' => [
                            'id' => $meal->category->id,
                            'name' => $meal->category->name,
                        ],
                        'available_date' => $meal->available_date,
                        'created_at' => $meal->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Today\'s meals retrieved successfully',
                'data' => $meals,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Get today's deals (meals with active discounts)
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $meals = Meal::with('category')
                ->available()
                ->withActiveDiscount()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($meal) {
                    return [
                        'id' => $meal->id,
                        'title' => $meal->title,
                        'slug' => $meal->slug,
                        'description' => $meal->description,
                        'image_url' => $meal->image_url,
                        'offer_title' => $meal->offer_title,
...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                        'category' => [
                            'id' => $meal->category->id,
                            'name' => $meal->category->name,
                        ],
                        'available_date' => $meal->available_date,
                        'created_at' => $meal->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Today\'s deals retrieved successfully',
                'data' => $meals,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve today\'s deals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all meals
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Meal::with(['category', 'subcategory'])->available();

            // SEARCH by title or description
            if ($request->has('search') && $request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ;
                });
            }

            // FILTER by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            // FILTER by subcategory
            if ($request->has('subcategory_id')) {
                $query->where('subcategory_id', $request->input('subcategory_id'));
            }

            // FILTER by featured (featured=1/true → featured only, featured=0/false → non-featured only)
            if ($request->has('featured')) {
                $request->boolean('featured') ? $query->featured() : $query->where('is_featured', false);
            }

            // FILTER by in stock (in_stock=1/true → in stock only, in_stock=0/false → out of stock only)
            if ($request->has('in_stock')) {
                $request->boolean('in_stock') ? $query->inStock() : $query->outOfStock();
            }

            // FILTER by price range
            if ($request->has('min_price')) {
                $minPrice = $request->input('min_price');
                $query->whereRaw('COALESCE(discount_price, price) >= ?', [$minPrice]);
            }
            if ($request->has('max_price')) {
                $maxPrice = $request->input('max_price');
                $query->whereRaw('COALESCE(discount_price, price) <= ?', [$maxPrice]);
            }

            // FILTER by rating
            if ($request->has('min_rating')) {
                $minRating = $request->input('min_rating');
                $query->where('rating', '>=', $minRating);
            }

            // FILTER by brand
            if ($request->has('brand')) {
                $query->where('brand', $request->input('brand'));
            }

            // SORTING (sort_by: created_at|price|rating|title|sold_count|newest, sort_order: asc|desc)
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

            // Get favorite meal IDs for the authenticated user
            $favoriteMealIds = [];
            if ($user) {
                $favoriteMealIds = $user->favorites()->pluck('meal_id')->toArray();
            }

            $meals = $query->get()
                ->map(function ($meal) use ($favoriteMealIds) {
                    return [
                        'id' => $meal->id,
                        'title' => $meal->title,
                        'slug' => $meal->slug,
                        'description' => $meal->description,
                        'image_url' => $meal->image_url,
                        'offer_title' => $meal->offer_title,
...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                        'rating' => (float) $meal->rating,
                        'rating_count' => (int) $meal->rating_count,
                        'size' => $meal->size,
                        'brand' => $meal->brand,
                        'stock_quantity' => $meal->stock_quantity,
                        'in_stock' => $meal->isInStock(),
                        'is_featured' => $meal->is_featured,
                        'sold_count' => $meal->sold_count,
                        'category' => [
                            'id' => $meal->category->id,
                            'name' => $meal->category->name,
                        ],
                        'subcategory' => $meal->subcategory ? [
                            'id' => $meal->subcategory->id,
                            'name' => $meal->subcategory->name,
                        ] : null,
                        'is_favorited' => in_array($meal->id, $favoriteMealIds),
                        'created_at' => $meal->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Meals retrieved successfully',
                'data' => $meals,
                'total_count' => $meals->count(),
                'filters_applied' => [
                    'search' => $request->input('search'),
                    'category_id' => $request->input('category_id'),
                    'subcategory_id' => $request->input('subcategory_id'),
                    'min_price' => $request->input('min_price'),
                    'max_price' => $request->input('max_price'),
                    'min_rating' => $request->input('min_rating'),
                    'brand' => $request->input('brand'),
                    'featured' => $request->boolean('featured'),
                    'in_stock' => $request->boolean('in_stock'),
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recommended meals
     */
    public function recommendations(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);

            // Get featured meals with offers
            $featuredMeals = Meal::with('category')
                ->available()
                ->featured()
                ->whereNotNull('discount_price')
                ->inRandomOrder()
                ->limit(ceil($limit / 2))
                ->get();

            // Get random meals from different categories
            $randomMeals = Meal::with('category')
                ->available()
                ->whereNotIn('id', $featuredMeals->pluck('id'))
                ->inRandomOrder()
                ->limit($limit - $featuredMeals->count())
                ->get();

            // Combine and shuffle
            $recommendations = $featuredMeals->merge($randomMeals)->shuffle()->take($limit);

            $meals = $recommendations->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                    'is_featured' => $meal->is_featured,
                    'category' => [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name,
                        'slug' => $meal->category->slug,
                    ],
                    'recommendation_reason' => $this->getRecommendationReason($meal),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Meal recommendations retrieved successfully',
                'data' => $meals->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recommendations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recommendation reason for a meal
     */
    private function getRecommendationReason($meal): string
    {
        if ($meal->is_featured && $meal->discount_price) {
            return 'Featured with special offer';
        }

        if ($meal->is_featured) {
            return 'Featured meal';
        }

        if ($meal->discount_price) {
            return 'Special offer';
        }

        return 'Popular choice';
    }

    /**
     * Get single meal
     */
    public function show(string $id): JsonResponse
    {
        try {
            $meal = Meal::with(['category', 'subcategory','reviews'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Meal retrieved successfully',
                'data' => [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,

                    // Pricing
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),

                    // Rating
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,

                    // Product details
                    'size' => $meal->size,
                    'brand' => $meal->brand,
                    'includes' => $meal->includes,
                    'how_to_use' => $meal->how_to_use,
                    'features' => $meal->features,

                    // Expiry and availability
                    'expiry_date' => $meal->expiry_date,
                    'days_until_expiry' => $meal->daysUntilExpiry(),
                    'is_expired' => $meal->isExpired(),

                    // Stock
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock' => $meal->isInStock(),
                    'sold_count' => $meal->sold_count,

                    // Status
                    'is_featured' => $meal->is_featured,
                    'is_available' => $meal->is_available,
                    'available_date' => $meal->available_date,

                    // Relationships
                    'category' => [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name,
                        'slug' => $meal->category->slug,
                    ],
                    'reviews' => $meal->reviews->map(function ($review) {
                        return [
                            'id' => $review->id,
                            'rating' => $review->rating,
                            'comment' => $review->comment,
                            'images' => $review->images,
                        ];
                    }),
                    'subcategory' => $meal->subcategory ? [
                        'id' => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                        'slug' => $meal->subcategory->slug,
                    ] : null,

                    'created_at' => $meal->created_at,
                    'updated_at' => $meal->updated_at,
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
                'message' => 'Failed to retrieve meal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
