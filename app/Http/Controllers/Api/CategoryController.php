<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetCategoryMealsRequest;
use App\Http\Resources\CategoryMealsResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MealResource;
use App\Actions\Category\GetCategoryMealsAction;
use App\Models\Category;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ResponseTrait;

    /**
     * Get all categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();

        return $this->successResponse(
            \App\Http\Resources\Api\CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Get single category with meals
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['meals' => function ($query) {
            $query->available()->orderBy('created_at', 'desc');
        }]);

        return $this->successResponse(
            new \App\Http\Resources\Api\CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * Get meals by category (paginated)
     */
 /**
     * Get meals by category (paginated)
     */
    public function meals(Category $category, GetCategoryMealsRequest $request, GetCategoryMealsAction $action): JsonResponse
    {
        $paginator = $action->execute($category, $request->validated());

        $category->meals_paginator = $paginator;

        $message = $paginator->total() === 0 
            ? 'No products match your filters.' 
            : 'Meals retrieved successfully';

        return $this->successResponse(
            new CategoryMealsResource($category),
            $message
        );
    }
}