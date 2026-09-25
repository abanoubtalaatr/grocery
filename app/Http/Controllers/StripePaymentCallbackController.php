<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class StripePaymentCallbackController extends Controller
{
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return $this->jsonOrHtml(false, 'Missing session_id parameter.', null, 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            report($e);

            return $this->jsonOrHtml(false, 'Unable to verify payment session.', null, 502);
        }

        if ($session->payment_status !== 'paid') {
            return $this->jsonOrHtml(false, 'Payment has not been completed.', null, 402);
        }

        $order = $this->resolveOrder($session);

        if (! $order) {
            return $this->jsonOrHtml(false, 'Order not found.', null, 404);
        }

        if ($order->status === 'awaiting_payment') {
            $pi = $session->payment_intent;
            $paymentIntentId = is_string($pi) ? $pi : ($pi->id ?? null);

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

            $order->refresh();
        }

        return $this->jsonOrHtml(true, 'Payment successful. Your order has been placed.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
        ], 200);
    }

    public function cancel(Request $request)
    {
        $orderId = $request->query('order_id');

        return $this->jsonOrHtml(false, 'Payment was cancelled.', [
            'order_id' => $orderId,
        ], 200);
    }

    private function resolveOrder(Session $session): ?Order
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

    private function jsonOrHtml(bool $success, string $message, ?array $data, int $status)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
