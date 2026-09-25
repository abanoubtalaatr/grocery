<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\SmartList;
use App\Models\User;
use App\Notifications\WishlistPriceAlertNotification;

class WishlistAlertService
{
    /**
     * Notify users who have this meal in a wish list with price-drop or offer alerts enabled.
     *
     * @param  Meal  $meal
     * @param  'price_drop'|'limited_time_offer'  $reason
     */
    public function notifyWishlistUsers(Meal $meal, string $reason): void
    {
        $query = SmartList::query()
            ->whereHas('meals', fn ($q) => $q->where('meals.id', $meal->id))
            ->where(function ($q) use ($reason) {
                if ($reason === 'price_drop') {
                    $q->where('notify_on_price_drop', true);
                } else {
                    $q->where('notify_on_offers', true);
                }
            });

        $userIds = $query->pluck('user_id')->unique()->filter();

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->notify(new WishlistPriceAlertNotification($meal, $reason));
            }
        }
    }
}
