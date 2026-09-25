<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $base = (new MealResource($this))->toArray($request);

        return array_merge($base, [
            'includes' => $this->includes,
            'how_to_use' => $this->how_to_use,
            'expiry_date' => $this->expiry_date,
            'days_until_expiry' => $this->daysUntilExpiry(),
            'is_expired' => $this->isExpired(),
            'is_available' => $this->is_available,
            'updated_at' => $this->updated_at,
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'user' => $review->relationLoaded('user') && $review->user ? [
                        'id' => $review->user->id,
                        'name' => $review->user->full_name ?? $review->user->username ?? 'User',
                    ] : null,
                    'rating' => (int) $review->rating,
                    'comment' => $review->comment,
                    'images' => $review->images ?? [],
                    'created_at' => $review->created_at?->toIso8601String(),
                ]);
            }),
        ]);
    }
}