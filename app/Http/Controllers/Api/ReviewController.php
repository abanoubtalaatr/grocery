<?php

namespace App\Http\Controllers\Api;

use App\Actions\Review\CreateReviewAction;
use App\Actions\Review\GetFilteredReviewsAction;
use App\Actions\Review\GetMealReviewsAction;
use App\Actions\Review\GetMealReviewStatsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\Api\ReviewResource;
use App\Models\Meal;
use App\Models\Review;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ResponseTrait;

    /**
     * Get all reviews (with filters and pagination)
     */
    public function index(Request $request, GetFilteredReviewsAction $action): JsonResponse
    {
        $reviews = $action->execute($request);

        return $this->successResponse(
            ReviewResource::collection($reviews)->response()->getData(true),
            'Reviews retrieved successfully'
        );
    }

    /**
     * Store a new review
     */
    public function store(StoreReviewRequest $request, CreateReviewAction $action): JsonResponse
    {
        if (Review::hasUserReviewed($request->user()->id, $request->meal_id)) {
            return $this->errorResponse('You have already reviewed this meal', 400);
        }

        $review = $action->execute($request->user()->id, $request->validated());

        return $this->successResponse(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review submitted successfully. Waiting for admin approval.',
            201
        );
    }

    /**
     * Get single review
     */
    public function show(Review $review): JsonResponse
    {
        return $this->successResponse(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review retrieved successfully'
        );
    }

    /**
     * Update review
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return $this->successResponse(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review updated successfully'
        );
    }

    /**
     * Delete review
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return $this->successResponse(null, 'Review deleted successfully');
    }

    /**
     * Get reviews for a specific meal
     */
    public function getMealReviews(Meal $meal, Request $request, GetMealReviewsAction $action): JsonResponse
    {
        $result = $action->execute($meal, (int) $request->input('per_page', 10));

        $data = ReviewResource::collection($result['reviews'])->response()->getData(true);
        $data['meal'] = [
            'id'             => $meal->id,
            'name'           => $meal->name,
            'average_rating' => $result['average_rating'],
            'total_reviews'  => $result['total_reviews'],
        ];

        return $this->successResponse($data, 'Meal reviews retrieved successfully');
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? $request->user()->id;

        $reviews = Review::with('meal')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($request->input('per_page', 10));

        return $this->successResponse(
            ReviewResource::collection($reviews)->response()->getData(true),
            'User reviews retrieved successfully'
        );
    }

    /**
     * Get review statistics for a meal
     */
    public function getMealReviewStats(Meal $meal, GetMealReviewStatsAction $action): JsonResponse
    {
        $stats = $action->execute($meal->id);

        return $this->successResponse($stats, 'Meal review stats retrieved successfully');
    }
}