<?php

namespace App\Observers;

use App\Models\Meal;
use App\Services\WishlistAlertService;

class MealObserver
{
    public function __construct(
        protected WishlistAlertService $wishlistAlerts
    ) {}

    /**
     * After a meal is updated, notify wish list users if price dropped or limited-time offer added.
     */
    public function updated(Meal $meal): void
    {
        $original = $meal->getOriginal();
        $hadDiscount = ! empty($original['discount_price']) || ! empty($original['offer_title']);
        $hasDiscount = $meal->resolved_discount_price !== null || ! empty($meal->offer_title);

        $oldPrice = (float) ($original['price'] ?? 0);
        $oldFinal = $hadDiscount ? (float) ($original['discount_price'] ?? $oldPrice) : $oldPrice;
        $newFinal = $meal->final_price;

        $priceDropped = $newFinal < $oldFinal;
        $offerAddedOrChanged = $hasDiscount && (! $hadDiscount || $original['discount_price'] != $meal->getRawDiscountPrice() || ($original['offer_title'] ?? '') != ($meal->offer_title ?? ''));

        if ($priceDropped) {
            $this->wishlistAlerts->notifyWishlistUsers($meal, 'price_drop');
        }
        if ($offerAddedOrChanged) {
            $this->wishlistAlerts->notifyWishlistUsers($meal, 'limited_time_offer');
        }
    }
}
