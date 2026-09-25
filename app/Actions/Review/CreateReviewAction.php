<?php

namespace App\Actions\Review;

use App\Models\Review;

class CreateReviewAction
{
    public function execute(int $userId, array $data): Review
    {
        return Review::create([
            'user_id'     => $userId,
            'meal_id'     => $data['meal_id'],
            'rating'      => $data['rating'],
            'comment'     => $data['comment'] ?? null,
            'images'      => $data['images'] ?? null,
            'is_approved' => false,
        ]);
    }
}