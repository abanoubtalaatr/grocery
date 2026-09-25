<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Order\TrackOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function __invoke(Request $request, TrackOrderAction $action): JsonResponse
    {
        $order = $action->handle($request->user());

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'No active order found',
            ], 404);
        }

        $this->authorize('view', $order);

        if ($order->status === 'awaiting_payment') {
            return response()->json([
                'success' => true,
                'message' => 'Order is waiting for payment. Complete checkout to continue.',
                'data' => [
                    'order' => OrderResource::make($order),
                    'awaiting_payment' => true,
                    'tracking' => null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order tracking retrieved successfully',
            'data' => [
                'order' => OrderResource::make($order),
                'tracking' => $order->status_position !== null ? [
                    'position' => $order->status_position,
                    'status' => $order->status,
                    'status_description' => $order->status_description,
                    'positions' => [
                        [
                            'position' => 1,
                            'status' => 'placed',
                            'label' => 'Order Placed',
                            'description' => 'Your order has been placed',
                            'completed' => in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered']),
                            'timestamp' => $order->placed_at,
                        ],
                        [
                            'position' => 2,
                            'status' => 'processing',
                            'label' => 'Processing',
                            'description' => 'Your order is being processed',
                            'completed' => in_array($order->status, ['processing', 'shipping', 'out_for_delivery', 'delivered']),
                            'timestamp' => $order->processing_at,
                        ],
                        [
                            'position' => 3,
                            'status' => 'shipping',
                            'label' => 'Shipping',
                            'description' => 'Your order is being shipped',
                            'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']),
                            'timestamp' => $order->shipping_at,
                        ],
                        [
                            'position' => 4,
                            'status' => 'out_for_delivery',
                            'label' => 'Out for Delivery',
                            'description' => 'Your order is on the way',
                            'completed' => in_array($order->status, ['out_for_delivery', 'delivered']),
                            'timestamp' => $order->out_for_delivery_at,
                        ],
                        [
                            'position' => 5,
                            'status' => 'delivered',
                            'label' => 'Delivered',
                            'description' => 'Your order has been delivered',
                            'completed' => $order->status === 'delivered',
                            'timestamp' => $order->delivered_at,
                        ],
                    ],
                ] : null,
            ],
        ]);
    }
}
