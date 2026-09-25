<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeCheckoutSessionRequest;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StripeCheckoutController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private readonly StripeCheckoutService $checkoutService
    ) {}

    /**
     * Verify payment session.
     */
    public function verifySession(Request $request, string $sessionId): JsonResponse
    {
        try {
            $order = $this->checkoutService->verifySession($sessionId, $request->user());

            return $this->successResponse([
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
            ], 'Payment verified. Order is placed.');

        } catch (Throwable $e) {
            if (in_array($e->getCode(), [402, 404])) {
                return $this->errorResponse($e->getMessage(), $e->getCode());
            }

            report($e);
            return $this->errorResponse('Unable to verify payment session.', 502);
        }
    }

    /**
     * Create checkout session.
     */
    public function store(CreateStripeCheckoutSessionRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $order = Order::query()->whereKey($data['order_id'])->where('user_id', $user->id)->first();
        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }

        try {
            $session = $this->checkoutService->createSessionForOrder($order, $user, (float) $data['amount']);

            return $this->successResponse([
                'checkout_url' => $session->url,
                'session_id'   => $session->id,
                'order_id'     => $order->id,
            ], 'Checkout session created. Open checkout_url in your WebView.');

        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            report($e);
            return $this->errorResponse('Unable to start checkout. Please try again.', 502);
        }
    }
}