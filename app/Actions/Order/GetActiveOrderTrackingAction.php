<?php

namespace App\Actions\Order;
use App\Models\User;
use App\Models\Order;

class GetActiveOrderTrackingAction
{
    public function execute(User $user): ?array
    {
        $order = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->latest()
            ->first();

        if (!$order) {
            return null;
        }

        if ($order->status === 'awaiting_payment') {
            return [
                'order' => $order,
                'awaiting_payment' => true,
                'tracking' => null,
            ];
        }

        return [
            'order' => $order,
            'awaiting_payment' => false,
            'tracking' => [
                'position' => $order->status_position,
                'status' => $order->status,
                'status_description' => $order->status_description,
                'positions' => $this->buildTrackingPositions($order),
            ],
        ];
    }

    private function buildTrackingPositions(Order $order): array
    {
        $statuses = [
            1 => ['placed', 'Order Placed', 'Your order has been placed', $order->placed_at],
            2 => ['processing', 'Processing', 'Your order is being processed', $order->processing_at],
            3 => ['shipping', 'Shipping', 'Your order is being shipped', $order->shipping_at],
            4 => ['out_for_delivery', 'Out for Delivery', 'Your order is on the way', $order->out_for_delivery_at],
            5 => ['delivered', 'Delivered', 'Your order has been delivered', $order->delivered_at],
        ];

        $currentStatus = $order->status;
        $flow = ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered'];

        return array_map(function ($pos, $data) use ($currentStatus, $flow) {
            return [
                'position' => $pos,
                'status' => $data[0],
                'label' => $data[1],
                'description' => $data[2],
                'completed' => in_array($currentStatus, array_slice($flow, array_search($data[0], $flow))),
                'timestamp' => $data[3],
            ];
        }, array_keys($statuses), $statuses);
    }
}