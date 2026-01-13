<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Get all reviews (with filters)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()
            ->with(['user', 'meal'])
            ->latest();
        
        // Apply filters
        if ($request->has('meal_id')) {
            $query->where('meal_id', $request->meal_id);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        if ($request->boolean('approved_only', true)) {
            $query->approved();
        }
        
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $reviews = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Store a new review
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        // Check if user has already reviewed this meal
        if (Review::hasUserReviewed(Auth::id(), $request->meal_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this meal'
            ], 400);
        }
        
        // Create review
        $review = Review::create([
            'user_id' => Auth::id(),
            'meal_id' => $request->meal_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'images' => $request->images,
            'is_approved' => false, // Admin approval required
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. Waiting for admin approval.',
            'data' => new ReviewResource($review->load(['user', 'meal']))
        ], 201);
    }

    /**
     * Get single review
     */
    public function show($id): JsonResponse
    {
        $review = Review::with(['user', 'meal'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review)
        ]);
    }

    /**
     * Update review (only by owner or admin)
     */
    public function update(UpdateReviewRequest $request, $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        
        // Check authorization (owner or admin)
        if (Auth::id() !== $review->user_id && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $review->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => new ReviewResource($review->load(['user', 'meal']))
        ]);
    }

    /**
     * Delete review (only by owner or admin)
     */
    public function destroy($id): JsonResponse
    {
        $review = Review::findOrFail($id);
        
        // Check authorization (owner or admin)
        if (Auth::id() !== $review->user_id && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $review->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Get reviews for a specific meal
     */
    public function getMealReviews($mealId, Request $request): JsonResponse
    {
        $meal = Meal::findOrFail($mealId);
        
        $reviews = Review::with('user')
            ->where('meal_id', $mealId)
            ->approved()
            ->latest()
            ->paginate($request->input('per_page', 10));
        
        $averageRating = Review::getAverageRating($mealId);
        $totalReviews = Review::getTotalReviews($mealId);
        
        return response()->json([
            'success' => true,
            'meal' => [
                'id' => $meal->id,
                'name' => $meal->name,
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews,
            ],
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? Auth::id();
        
        $reviews = Review::with('meal')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($request->input('per_page', 10));
        
        return response()->json([
            'success' => true,
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Get review statistics for a meal
     */
    public function getMealReviewStats($mealId): JsonResponse
    {
        $stats = Review::where('meal_id', $mealId)
            ->approved()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
            ')
            ->first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_reviews' => (int) $stats->total_reviews,
                'average_rating' => round($stats->average_rating ?? 0, 1),
                'rating_distribution' => [
                    'five_star' => (int) $stats->five_star,
                    'four_star' => (int) $stats->four_star,
                    'three_star' => (int) $stats->three_star,
                    'two_star' => (int) $stats->two_star,
                    'one_star' => (int) $stats->one_star,
                ]
            ]
        ]);
    }
}