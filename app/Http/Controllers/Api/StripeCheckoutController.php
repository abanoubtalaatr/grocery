<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeCheckoutSessionRequest;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;

class StripeCheckoutController extends Controller
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService
    ) {}

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

        // if ($order->payment_method !== 'stripe_checkout') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'This order is not payable with hosted Stripe checkout.',
        //     ], 422);
        // }

        // if ($order->status !== 'awaiting_payment') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'This order is not awaiting payment.',
        //     ], 422);
        // }

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
