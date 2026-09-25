<?php

namespace App\Http\Controllers\Api;

use App\Actions\Subcategories\GetFilteredSubcategoriesAction;
use App\Actions\Subcategories\GetSubcategoryDetailsAction;
use App\Actions\Subcategories\GetSubcategoryMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealResource;
use App\Http\Resources\Api\SubcategoryResource;
use App\Models\Subcategory;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ResponseTrait;

    /**
     * Get all active subcategories.
     */
    public function index(Request $request, GetFilteredSubcategoriesAction $action): JsonResponse
    {
        $subcategories = $action->execute($request->all());

        return $this->successResponse(
            SubcategoryResource::collection($subcategories),
            'Subcategories retrieved successfully'
        );
    }

    /**
     * Get single subcategory details.
     */
    public function show(string $id, GetSubcategoryDetailsAction $action): JsonResponse
    {
        $subcategory = $action->execute($id);

        return $this->successResponse(
            new SubcategoryResource($subcategory),
            'Subcategory retrieved successfully'
        );
    }

    /**
     * Get meals by subcategory (paginated).
     */
    public function meals(string $id, Request $request, GetSubcategoryMealsAction $action): JsonResponse
    {
        $subcategory = Subcategory::findOrFail($id);
        $paginator = $action->execute($subcategory, $request->all());

        $responseData = [
            'subcategory' => [
                'id'   => $subcategory->id,
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
            ],
            'meals'      => MealResource::collection($paginator->getCollection()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ];

        if ($paginator->total() === 0) {
            $responseData['empty_message'] = 'No products match the applied filters. Try adjusting your filters.';
        }

        return $this->successResponse(
            $responseData,
            $paginator->total() === 0 ? 'No products match your filters.' : 'Meals retrieved successfully'
        );
    }
}