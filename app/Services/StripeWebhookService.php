<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Stripe;

class StripeWebhookService
{
    public function handleEvent(Event $event): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        match ($event->type) {
            'checkout.session.completed' => $this->onCheckoutSessionCompleted($event->data->object),
            'checkout.session.async_payment_failed', 'checkout.session.expired' => $this->onCheckoutSessionAbandoned($event->data->object),
            default => null,
        };
    }

    private function onCheckoutSessionAbandoned(Session $session): void
    {
        $order = $this->resolveOrderFromSession($session);
        if (! $order || $order->status !== 'awaiting_payment') {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->refresh();
            if ($order->status !== 'awaiting_payment') {
                return;
            }

            foreach ($order->items as $item) {
                if ($item->meal) {
                    $item->meal->increment('stock_quantity', $item->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'stripe_checkout_session_id' => null,
            ]);
        });
    }

    private function onCheckoutSessionCompleted(Session $session): void
    {
        if ($session->payment_status !== 'paid') {
            return;
        }

        $order = $this->resolveOrderFromSession($session);
        if (! $order || $order->status !== 'awaiting_payment') {
            return;
        }

        $pi = $session->payment_intent;
        $paymentIntentId = null;
        if (is_string($pi)) {
            $paymentIntentId = $pi;
        } elseif (is_object($pi) && isset($pi->id)) {
            $paymentIntentId = $pi->id;
        }

        DB::transaction(function () use ($order, $paymentIntentId, $session) {
            $order->refresh();
            if ($order->status !== 'awaiting_payment') {
                return;
            }

            $order->update([
                'status' => 'placed',
                'placed_at' => now(),
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_checkout_session_id' => $session->id,
            ]);
        });
    }

    private function resolveOrderFromSession(Session $session): ?Order
    {
        $orderId = $session->metadata->order_id ?? null;
        if ($orderId) {
            return Order::query()->whereKey((int) $orderId)->first();
        }

        if ($session->client_reference_id) {
            return Order::query()->whereKey((int) $session->client_reference_id)->first();
        }

        return null;
    }
}
