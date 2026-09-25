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

        return response()->json([
            'success'      => true,
            'message'      => 'Payment history retrieved successfully',
            'data'         => PaymentHistoryResource::collection($orders),
            'total_count'  => $orders->count(),
            'total_amount' => (float) $orders->sum('total'),
        ]);
    }

    /**
     * Get receipt/invoice for a specific order.
     */
    public function receipt(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->errorResponse('Order not found', 404);
        }

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