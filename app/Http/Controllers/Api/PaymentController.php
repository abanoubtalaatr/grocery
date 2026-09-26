<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payment\GetUserPaymentHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderReceiptResource;
use App\Http\Resources\Api\PaymentHistoryResource;
use App\Models\Order;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ResponseTrait;

    /**
     * Get payment history for the authenticated user.
     */
    public function paymentHistory(Request $request, GetUserPaymentHistoryAction $action): JsonResponse
    {
        $orders = $action->execute($request->user());

        return $this->successResponse([
            'payments'     => PaymentHistoryResource::collection($orders),
            'total_count'  => $orders->count(),
            'total_amount' => (float) $orders->sum('total'),
        ], 'Payment history retrieved successfully');
    }

    /**
     * Get receipt/invoice for a specific order.
     */
    public function receipt(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);

        return $this->successResponse(
            new OrderReceiptResource($order),
            'Receipt retrieved successfully'
        );
    }

    /**
     * Get invoice for a specific order (alias for receipt).
     */
    public function invoice(Request $request, Order $order): JsonResponse
    {
        return $this->receipt($request, $order);
    }
}