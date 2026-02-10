<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FrequencyService
{
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';

    public const VALID_TYPES = [
        self::FREQUENCY_DAILY,
        self::FREQUENCY_WEEKLY,
        self::FREQUENCY_MONTHLY,
    ];

    /**
     * Get the start of the time window for a frequency type (relative to now).
     */
    public function getSinceForType(string $frequencyType): Carbon
    {
        return match ($frequencyType) {
            self::FREQUENCY_DAILY => now()->subDay(),
            self::FREQUENCY_WEEKLY => now()->subWeek(),
            self::FREQUENCY_MONTHLY => now()->subMonth(),
            default => now()->subWeek(),
        };
    }

    /**
     * Get meal IDs ordered by this user in the given time window, sorted by total quantity (most frequent first).
     * Returns array of [meal_id => total_quantity].
     *
     * @return array<int, int>
     */
    public function getFrequentlyOrderedMealCounts(User $user, string $frequencyType): array
    {
        $since = $this->getSinceForType($frequencyType);

        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('order_items.meal_id, SUM(order_items.quantity) as total_quantity')
            ->groupBy('order_items.meal_id')
            ->orderByDesc('total_quantity')
            ->pluck('total_quantity', 'meal_id')
            ->all();
    }

    /**
     * Get frequently ordered meals for the user in the given time window.
     * Returns a collection of Meal models in frequency order, with order_count set on each.
     *
     * @return Collection<int, Meal>
     */
    public function getFrequentlyOrderedMeals(User $user, string $frequencyType, int $limit = 50): Collection
    {
        $counts = $this->getFrequentlyOrderedMealCounts($user, $frequencyType);

        if (empty($counts)) {
            return collect();
        }

        $mealIds = array_keys($counts);
        $meals = Meal::with('category')
            ->available()
            ->whereIn('id', $mealIds)
            ->get()
            ->keyBy('id');

        // Preserve frequency order and add order_count
        $result = collect();
        foreach (array_slice($mealIds, 0, $limit) as $mealId) {
            $meal = $meals->get($mealId);
            if ($meal) {
                $meal->setAttribute('order_count', (int) $counts[$mealId]);
                $result->push($meal);
            }
        }

        return $result;
    }
}
