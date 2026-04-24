<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutService
{
    public function createSessionForOrder(Order $order, User $user, float $claimedAmount): Session
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $orderTotal = (float) $order->total;
        // if (abs($orderTotal - $claimedAmount) > 0.02) {
        //     throw new \InvalidArgumentException('Amount does not match order total.');
        // }

        $currency = strtolower((string) config('services.stripe.currency', 'usd'));
        $unitAmount = (int) round($orderTotal * 100);
        // if ($unitAmount < 1) {
        //     throw new \InvalidArgumentException('Order total is too small to charge.');
        // }

        $successUrl = (string) config('services.stripe.checkout_success_url');
        $cancelUrl = (string) config('services.stripe.checkout_cancel_url');

        return Session::create([
            'mode' => 'payment',
            'client_reference_id' => (string) $order->id,
            'customer_email' => $user->email,
            'metadata' => [
                'order_id' => (string) $order->id,
                'user_id' => (string) $order->user_id,
            ],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $unitAmount,
                    'product_data' => [
                        'name' => 'Order '.$order->order_number,
                    ],
                ],
            ]],
            'success_url' => $successUrl,
            'cancel_url' => str_replace('{ORDER_ID}', (string) $order->id, $cancelUrl),
        ]);
    }
}
