<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $trackingStage = match ($this->status) {
            'shipping'         => 'arriving',
            'out_for_delivery' => 'out_for_delivery',
            'delivered'        => 'delivered',
            default            => 'processing',
        };

        return [
            'id'                 => $this->id,
            'order_number'       => $this->order_number,
            'status'             => $this->status,
            'status_description' => $this->status_description,
            'tracking'           => [
                'stage'       => $trackingStage,
                'stage_label' => match ($trackingStage) {
                    'arriving'         => 'Arriving',
                    'out_for_delivery' => 'Out for delivery',
                    'delivered'        => 'Delivered',
                    default            => 'Processing',
                },
                'positions'   => [
                    ['stage' => 'arriving', 'label' => 'Arriving', 'completed' => in_array($this->status, ['shipping', 'out_for_delivery', 'delivered']), 'timestamp' => $this->shipping_at?->toIso8601String()],
                    ['stage' => 'out_for_delivery', 'label' => 'Out for delivery', 'completed' => in_array($this->status, ['out_for_delivery', 'delivered']), 'timestamp' => $this->out_for_delivery_at?->toIso8601String()],
                    ['stage' => 'delivered', 'label' => 'Delivered', 'completed' => $this->status === 'delivered', 'timestamp' => $this->delivered_at?->toIso8601String()],
                ],
            ],
            'total'                   => (float) $this->total,
            'placed_at'               => $this->placed_at?->toIso8601String(),
            'estimated_delivery_time' => $this->estimated_delivery_time?->toIso8601String(),
            'address'                 => $this->address ? new AddressResource($this->address) : null,
            'items'                   => $this->items->map(fn ($item) => [
                'id'       => $item->id,
                'meal'     => ['id' => $item->meal->id, 'title' => $item->meal->title, 'image_url' => $item->meal->image_url],
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
        ];
    }
}