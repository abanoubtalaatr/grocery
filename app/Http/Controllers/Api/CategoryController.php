<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryMealResource;
use App\Http\Resources\Api\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    /**
     * Get all categories
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => CategoryResource::collection($categories)->resolve(),
        ]);
    }

    /**
     * Get single category with meals
     */
    public function show(string $id): JsonResponse
    {
        $category = $this->categoryService->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => CategoryResource::make($category)->resolve(),
        ]);
    }

    /**
     * Get meals by category (paginated)
     */
    public function meals(string $id, Request $request): JsonResponse
    {
        $filters = [];

        if ($request->has('featured')) {
            $filters['featured'] = $request->boolean('featured');
        }

        if ($request->has('subcategory_id')) {
            $filters['subcategory_id'] = $request->input('subcategory_id');
        }

        if ($request->has('in_stock')) {
            $filters['in_stock'] = $request->boolean('in_stock');
        }

        if ($request->has('sort_by')) {
            $filters['sort_by'] = $request->input('sort_by');
        }

        if ($request->has('sort_order')) {
            $filters['sort_order'] = $request->input('sort_order');
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
        [$category, $paginator] = $this->categoryService->getMeals($id, $filters, $perPage);

        $data = [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'meals' => CategoryMealResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];

        return response()->json(array_merge([
            'success' => true,
            'message' => $paginator->total() === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
            'data' => $data,
        ], $paginator->total() === 0 ? ['empty_message' => 'No products match the applied filters. Try adjusting your filters.'] : []));
    }
}
