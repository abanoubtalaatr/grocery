<?php

namespace App\Services;

use App\Jobs\SendOrderInvoiceJob;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout session for the order.
     */
    public function createSessionForOrder(Order $order, User $user, float $claimedAmount): Session
    {
        $orderTotal = (float) $order->total;

        if (abs($orderTotal - $claimedAmount) > 0.02) {
            throw new \InvalidArgumentException('Amount does not match order total.');
        }

        $currency = strtolower((string) config('services.stripe.currency', 'usd'));
        $unitAmount = (int) round($orderTotal * 100);

        if ($unitAmount < 1) {
            throw new \InvalidArgumentException('Order total is too small to charge.');
        }

        $successUrl = (string) config('services.stripe.checkout_success_url');
        $cancelUrl = (string) config('services.stripe.checkout_cancel_url');

        $session = Session::create([
            'mode'                => 'payment',
            'client_reference_id' => (string) $order->id,
            'customer_email'      => $user->email,
            'metadata'            => [
                'order_id' => (string) $order->id,
                'user_id'  => (string) $order->user_id,
            ],
            'line_items'          => [[
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => $currency,
                    'unit_amount'  => $unitAmount,
                    'product_data' => [
                        'name' => 'Order ' . $order->order_number,
                    ],
                ],
            ]],
            'success_url'         => $successUrl,
            'cancel_url'          => str_replace('{ORDER_ID}', (string) $order->id, $cancelUrl),
        ]);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $session;
    }

    /**
     * Verify Stripe session and update order status.
     */
    public function verifySession(string $sessionId, User $user): Order
    {
        $session = Session::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            throw new \Exception('Payment has not been completed.', 402);
        }

        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        $order = $orderId
            ? Order::query()->whereKey((int) $orderId)->where('user_id', $user->id)->first()
            : null;

        if (!$order) {
            throw new \Exception('Order not found.', 404);
        }

        if ($order->status === 'awaiting_payment') {
            $pi = $session->payment_intent;
            $paymentIntentId = is_string($pi) ? $pi : ($pi->id ?? null);
$wasUpdated = false;
            DB::transaction(function () use ($order, $paymentIntentId, $session, &$wasUpdated) {
                $order->refresh();
                if ($order->status !== 'awaiting_payment') {
                    return;
                }

                $order->update([
                    'status'                     => 'placed',
                    'placed_at'                  => now(),
                    'stripe_payment_intent_id'   => $paymentIntentId,
                    'stripe_checkout_session_id' => $session->id,
                ]);
                $wasUpdated = true;
            });

            $order->refresh();
            if ($wasUpdated) {
                SendOrderInvoiceJob::dispatch($order);
            }
        }

        return $order;
    }
}