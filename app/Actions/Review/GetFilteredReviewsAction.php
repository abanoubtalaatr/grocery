<?php

namespace App\Actions\Review;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetFilteredReviewsAction
{
    public function execute(Request $request): LengthAwarePaginator
    {
        return Review::query()
            ->with(['user', 'meal'])
            ->when($request->meal_id, fn($q, $id) => $q->where('meal_id', $id))
            ->when($request->user_id, fn($q, $id) => $q->where('user_id', $id))
            ->when($request->rating, fn($q, $r) => $q->where('rating', $r))
            ->when($request->has('min_rating'), fn($q) => $q->where('rating', '>=', $request->min_rating))
            ->when($request->boolean('approved_only', true), fn($q) => $q->approved())
            ->latest()
            ->paginate($request->input('per_page', 15));
    }
}