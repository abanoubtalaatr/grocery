<?php

namespace App\Notifications;

use App\Models\Meal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WishlistPriceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Meal $meal,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Reason: 'price_drop' | 'limited_time_offer'
     */
    public function toArray(object $notifiable): array
    {
        $isOffer = $this->reason === 'limited_time_offer';
        $title = $isOffer
            ? 'Limited-time offer on a wish list item'
            : 'Price drop on a wish list item';
        $body = $isOffer
            ? "{$this->meal->title} has a limited-time offer. Check it out before it ends!"
            : "{$this->meal->title} has dropped in price. Catch it now!";

        $path = "/meals/{$this->meal->id}";
        if ($this->meal->slug) {
            $path = '/meals/' . $this->meal->slug;
        }

        return [
            'type' => 'wishlist_price_alert',
            'title' => $title,
            'body' => $body,
            'action_url' => $path,
            'action_label' => 'View',
            'meal_id' => $this->meal->id,
            'meal_title' => $this->meal->title,
            'reason' => $this->reason,
            'price' => (float) $this->meal->price,
            'discount_price' => $this->meal->resolved_discount_price !== null ? (float) $this->meal->resolved_discount_price : null,
        ];
    }
}
