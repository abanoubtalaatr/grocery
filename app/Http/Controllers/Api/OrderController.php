<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Actions\CreateOrderAction;
use App\Actions\GetUserOrdersAction;
use App\Actions\GetActiveOrderTrackingAction;
use App\Traits\ApiResponseTrait;

class OrderController extends Controller
{
    use ResponseTrait;

    public function index(Request $request, GetUserOrdersAction $action): JsonResponse
    {
        $orders = $action->execute($request->user());

        return $this->successResponse([
            'orders' => OrderResource::collection($orders),
            'total_count' => $orders->count(),
        ], 'Orders retrieved successfully');
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized access.', 403);
        }

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address']);

        return $this->successResponse(
            new OrderResource($order),
            'Order retrieved successfully'
        );
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        $order = $action->execute($request->user(), $request->validated());

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address']);

        return $this->successResponse(
            new OrderResource($order),
            'Order created successfully',
            201
        );
    }

    public function track(Request $request, \App\Actions\Order\GetActiveOrderTrackingAction $action): JsonResponse
    {
        $trackingData = $action->execute($request->user());

        if (!$trackingData) {
            return $this->errorResponse('No active order found', 404);
        }

        if ($trackingData['awaiting_payment']) {
            return $this->successResponse([
                'order' => new OrderResource($trackingData['order']),
                'awaiting_payment' => true,
                'tracking' => null,
            ], 'Order is waiting for payment. Complete checkout to continue.');
        }

        return $this->successResponse([
            'order' => new OrderResource($trackingData['order']),
            'tracking' => $trackingData['tracking'],
        ], 'Order tracking retrieved successfully');
    }
}