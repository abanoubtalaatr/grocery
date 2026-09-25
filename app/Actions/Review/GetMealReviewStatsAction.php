<?php

namespace App\Actions\Review;

use App\Models\Review;

class GetMealReviewStatsAction
{
    public function execute(int $mealId): array
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

        return [
            'total_reviews'       => (int) $stats->total_reviews,
            'average_rating'      => round($stats->average_rating ?? 0, 1),
            'rating_distribution' => [
                'five_star'  => (int) $stats->five_star,
                'four_star'  => (int) $stats->four_star,
                'three_star' => (int) $stats->three_star,
                'two_star'   => (int) $stats->two_star,
                'one_star'   => (int) $stats->one_star,
            ]
        ];
    }
}