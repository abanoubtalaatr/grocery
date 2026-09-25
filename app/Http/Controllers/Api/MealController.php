<?php
namespace App\Http\Controllers\Api;

use App\Actions\Meal\GetFilteredMealsAction;
use App\Actions\Meal\GetFrequencyMealsAction;
use App\Actions\Meal\GetRecommendedMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\MealDetailResource;
use App\Http\Resources\MealResource;
use App\Models\Meal;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MealController extends Controller
{
    use ResponseTrait;

    /**
     * Get all meals with filters and search.
     */
    public function index(Request $request, GetFilteredMealsAction $action): JsonResponse
    {
        $result = $action->execute($request->all(), $request->user());

        return $this->successResponse([
            'data' => MealResource::collection($result['meals']),
            'total_count' => $result['total_count'],
            'filters_applied' => $result['filters_applied'],
            'empty_message' => $result['is_empty'] ? 'No products match the applied filters.' : null,
        ], $result['is_empty'] ? 'No products match your filters.' : 'Meals retrieved successfully');
    }

    /**
     * Get meals ordered most often.
     */
    public function frequency(Request $request, GetFrequencyMealsAction $action): JsonResponse
    {
        if (! $request->user()) {
            return $this->errorResponse('Authentication required to view frequency meals.', 401);
        }

        $subcategoryId = $request->filled('subcategory_id') ? (int) $request->input('subcategory_id') : null;
        $result = $action->execute($request->user(), $request->input('frequency_type'), $subcategoryId);

        return $this->successResponse([
            'frequency_type' => $result['frequency_type'],
            'subcategory_id' => $result['subcategory_id'] ?? null,
            'data' => MealResource::collection($result['meals']),
        ], 'Frequency meals retrieved successfully');
    }

    /**
     * Get single meal details using Route Model Binding.
     */
    public function show(Meal $meal): JsonResponse
    {
        $meal->load([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->latest(),
        ]);

        return $this->successResponse(new MealDetailResource($meal), 'Meal retrieved successfully');
    }

    /**
     * Get recommended meals via Dedicated Action Class.
     */
    public function recommendations(Request $request, GetRecommendedMealsAction $action): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);

        return $this->successResponse(
            MealResource::collection($action->execute($limit)),
            'Meal recommendations retrieved successfully'
        );
    }

    /**
     * Cached static / frequent lookup endpoints
     */
    public function brands(): JsonResponse
    {
        $brands = Cache::remember('meals_brands', now()->addHours(6), fn () => Meal::distinct()->pluck('brand')->filter()->values());

        return $this->successResponse($brands, 'Brands retrieved successfully');
    }

    public function slider(): JsonResponse
    {
        return $this->successResponse(MealResource::collection(Meal::with('category')->available()->latest()->get()), "Today's meals retrieved successfully");
    }

    public function bestSells(): JsonResponse
    {
        return $this->successResponse(MealResource::collection(Meal::with('category')->available()->take(10)->get()), 'Best sells retrieved successfully');
    }

    public function today(): JsonResponse
    {
        return $this->successResponse(MealResource::collection(Meal::with('category')->available()->withActiveDiscount()->latest()->get()), "Today's deals retrieved successfully");
    }

    public function hot(): JsonResponse
    {
        return $this->successResponse(MealResource::collection(Meal::with('category')->available()->hot()->latest()->get()), 'Hot meals retrieved successfully');
    }

    public function newProducts(): JsonResponse
    {
        return $this->successResponse(MealResource::collection(Meal::with('category')->available()->latest()->get()), 'New products retrieved successfully');
    }

    public function moreToExplore(): JsonResponse
    {
        return $this->successResponse(MealResource::collection(Meal::with('category')->available()->latest()->get()), 'More to explore retrieved successfully');
    }
}