<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\User;

class LoyaltyService
{
    /**
     * @return array<string, mixed>
     */
    public function buildSummary(User $user): array
    {
        $points = (int) ($user->loyalty_points ?? 0);
        $pointValue = (float) config('loyalty.point_value', 0.01);
        $tiers = $this->tierDefinitions();
        $currentTier = $this->resolveCurrentTier($points, $tiers);
        $nextTier = $this->resolveNextTier($currentTier, $tiers);

        return [
            'point_balance' => $points,
            'rewards_value' => round($points * $pointValue, 2),
            'rewards_currency' => config('loyalty.currency', 'GBP'),
            'point_value' => $pointValue,
            'profile_initial' => $this->profileInitial($user),
            'membership' => $this->formatMembership($points, $currentTier, $nextTier, $tiers),
            'benefits' => [
                'tier_key' => $currentTier['key'],
                'tier_name' => $currentTier['name'],
                'items' => config('loyalty.benefits_by_tier.'.$currentTier['key'], []),
            ],
            'coupons' => $this->formatCoupons(),
        ];
    }

    /**
     * @return list<array{key: string, name: string, min_points: int}>
     */
    private function tierDefinitions(): array
    {
        $tiers = config('loyalty.tiers', []);

        return collect($tiers)
            ->map(fn (array $tier) => [
                'key' => (string) $tier['key'],
                'name' => (string) $tier['name'],
                'min_points' => (int) $tier['min_points'],
            ])
            ->sortBy('min_points')
            ->values()
            ->all();
    }

    /**
     * @param  list<array{key: string, name: string, min_points: int}>  $tiers
     * @return array{key: string, name: string, min_points: int}
     */
    private function resolveCurrentTier(int $points, array $tiers): array
    {
        $current = $tiers[0] ?? ['key' => 'silver', 'name' => 'Silver', 'min_points' => 0];

        foreach ($tiers as $tier) {
            if ($points >= $tier['min_points']) {
                $current = $tier;
            }
        }

        return $current;
    }

    /**
     * @param  array{key: string, name: string, min_points: int}  $currentTier
     * @param  list<array{key: string, name: string, min_points: int}>  $tiers
     * @return array{key: string, name: string, min_points: int}|null
     */
    private function resolveNextTier(array $currentTier, array $tiers): ?array
    {
        foreach ($tiers as $tier) {
            if ($tier['min_points'] > $currentTier['min_points']) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * @param  array{key: string, name: string, min_points: int}  $currentTier
     * @param  array{key: string, name: string, min_points: int}|null  $nextTier
     * @param  list<array{key: string, name: string, min_points: int}>  $tiers
     * @return array<string, mixed>
     */
    private function formatMembership(int $points, array $currentTier, ?array $nextTier, array $tiers): array
    {
        $progressMax = $nextTier['min_points'] ?? $points;
        $progressLabel = $nextTier
            ? 'Progress to '.$nextTier['name']
            : 'Highest tier reached';

        return [
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress_label' => $progressLabel,
            'points_current' => $points,
            'points_max' => $progressMax,
            'points_to_next' => $nextTier ? max(0, $nextTier['min_points'] - $points) : 0,
            'tiers' => collect($tiers)->map(function (array $tier) use ($points, $currentTier) {
                return [
                    'key' => $tier['key'],
                    'name' => $tier['name'],
                    'min_points' => $tier['min_points'],
                    'is_current' => $tier['key'] === $currentTier['key'],
                    'is_unlocked' => $points >= $tier['min_points'],
                ];
            })->values()->all(),
        ];
    }

    private function profileInitial(User $user): string
    {
        $source = $user->full_name
            ?? $user->username
            ?? $user->email
            ?? 'U';

        $letter = mb_strtoupper(mb_substr(trim($source), 0, 1));

        return $letter !== '' ? $letter : 'U';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function formatCoupons(): array
    {
        return Offer::active()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (Offer $offer) => [
                'id' => $offer->id,
                'title' => $offer->title,
                'code' => $offer->code,
                'description' => $offer->description,
                'type' => $offer->type,
                'discount_label' => $this->discountLabel($offer),
                'minimum_purchase' => $offer->minimum_purchase !== null ? (float) $offer->minimum_purchase : null,
                'expires_at' => $offer->end_date?->format('Y-m-d'),
                'expires_label' => $offer->end_date?->format('M j, Y'),
                'is_featured' => (bool) $offer->is_featured,
            ])
            ->values()
            ->all();
    }

    private function discountLabel(Offer $offer): string
    {
        return match ($offer->type) {
            'percentage' => rtrim(rtrim(number_format((float) $offer->discount_value, 2), '0'), '.').'% off',
            'fixed' => '£'.rtrim(rtrim(number_format((float) $offer->discount_value, 2), '0'), '.').' off',
            'free_shipping' => 'Free shipping',
            'buy_one_get_one' => 'Buy one get one',
            default => $offer->title,
        };
    }
}
