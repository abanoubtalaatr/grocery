<?php

namespace App\Http\Controllers\Api;

use App\Actions\SmartList\CreateSmartListAction;
use App\Actions\SmartList\UpdateSmartListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    use ResponseTrait;

    /**
     * Get user's smart lists
     */
    public function index(Request $request): JsonResponse
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)
            ->with('meals')
            ->latest()
            ->get();

        return $this->successResponse(
            SmartListResource::collection($smartLists),
            'Smart lists retrieved successfully'
        );
    }

    /**
     * Store a new smart list
     */
    public function store(SmartListRequest $request, CreateSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute($request->user()->id, $request->validated());

        return $this->successResponse(
            new SmartListResource($smartList),
            'Wish list created successfully',
            201
        );
    }

    /**
     * Show single smart list
     */
    public function show(SmartList $smartList): JsonResponse
    {
        $this->authorize('view', $smartList);

        return $this->successResponse(
            new SmartListResource($smartList->load('meals')),
            'Smart list retrieved successfully'
        );
    }

    /**
     * Update smart list
     */
    public function update(SmartListRequest $request, SmartList $smartList, UpdateSmartListAction $action): JsonResponse
    {
        $this->authorize('update', $smartList);

        $updatedList = $action->execute($smartList, $request->validated());

        return $this->successResponse(
            new SmartListResource($updatedList),
            'Wish list updated successfully'
        );
    }

    /**
     * Delete smart list
     */
    public function destroy(SmartList $smartList): JsonResponse
    {
        $this->authorize('delete', $smartList);

        $smartList->meals()->detach();
        $smartList->delete();

        return $this->successResponse(null, 'Wish list deleted successfully');
    }

    /**
     * Add a meal to a wish list.
     */
    public function addMeal(Request $request, SmartList $smartList): JsonResponse
    {
        $this->authorize('update', $smartList);

        $request->validate([
            'meal_id' => ['required', 'exists:meals,id'],
        ]);

        $smartList->meals()->syncWithoutDetaching([$request->meal_id]);

        return $this->successResponse(
            new SmartListResource($smartList->load('meals')),
            'Item added to wish list successfully'
        );
    }

    /**
     * Remove a meal from a wish list.
     */
    public function removeMeal(SmartList $smartList, int $mealId): JsonResponse
    {
        $this->authorize('update', $smartList);

        $smartList->meals()->detach($mealId);

        return $this->successResponse(
            new SmartListResource($smartList->load('meals')),
            'Item removed from wish list successfully'
        );
    }
}