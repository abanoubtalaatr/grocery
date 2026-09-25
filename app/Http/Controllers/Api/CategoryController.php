<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Actions\Category\GetCategoriesAction;
use App\Actions\Category\GetCategoryWithMealsAction;
use App\Actions\Category\GetCategoryPaginatedMealsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index(GetCategoriesAction $action): JsonResponse
    {
        $categories = $action->handle();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    /**
     * Get single category with meals
     */
    public function show(Category $category, GetCategoryWithMealsAction $action): JsonResponse
    {
        $categoryData = $action->handle($category);

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => $categoryData,
        ]);
    }

    /**
     * Get meals by category (paginated)
     */
    public function meals(Request $request, Category $category, GetCategoryPaginatedMealsAction $action): JsonResponse
    {
        $result = $action->handle($category, $request);

        return response()->json(array_merge([
            'success' => true,
            'message' => $result['total'] === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'meals' => $result['paginator']->items(),
                'pagination' => [
                    'current_page' => $result['paginator']->currentPage(),
                    'last_page' => $result['paginator']->lastPage(),
                    'per_page' => $result['paginator']->perPage(),
                    'total' => $result['total'],
                    'from' => $result['paginator']->firstItem(),
                    'to' => $result['paginator']->lastItem(),
                ],
            ],
        ], $result['total'] === 0 ? ['empty_message' => 'No products match the applied filters. Try adjusting your filters.'] : []));
    }
}