<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeCheckoutSessionRequest;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class StripeCheckoutController extends Controller
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService
    ) {}

    public function verifySession(Request $request, string $sessionId): JsonResponse
    {
        $user = $request->user();

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify payment session.',
            ], 502);
        }

        if ($session->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment has not been completed.',
                'data' => ['payment_status' => $session->payment_status],
            ], 402);
        }

        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        $order = $orderId
            ? Order::query()->whereKey((int) $orderId)->where('user_id', $user->id)->first()
            : null;

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
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

        return response()->json([
            'success' => true,
            'message' => 'Payment verified. Order is placed.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
            ],
        ]);
    }

    public function store(CreateStripeCheckoutSessionRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $order = Order::query()->whereKey($data['order_id'])->where('user_id', $user->id)->first();
        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        try {
            $session = $this->checkoutService->createSessionForOrder($order, $user, (float) $data['amount']);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to start checkout. Please try again.',
            ], 502);
        }

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return response()->json([
            'success' => true,
            'message' => 'Checkout session created. Open checkout_url in your WebView.',
            'data' => [
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'order_id' => $order->id,
            ],
        ]);
    }
}
