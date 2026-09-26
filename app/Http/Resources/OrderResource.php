<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'payment_method' => $this->payment_method,
            'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
            'delivery_type' => $this->delivery_type,
            'status' => $this->status,
            'status_position' => $this->status_position,
            'status_description' => $this->status_description,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'address' => $this->whenLoaded('address'),
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'shipping_fee' => (float) ($this->shipping_fee ?? 0),
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'placed_at' => $this->placed_at,
            'processing_at' => $this->processing_at,
            'shipping_at' => $this->shipping_at,
            'out_for_delivery_at' => $this->out_for_delivery_at,
            'delivered_at' => $this->delivered_at,
            'estimated_delivery_time' => $this->estimated_delivery_time,
            'special_note' => $this->special_note,
            'schedule_delivery' => $this->schedule_delivery,
            'delivery_speed' => $this->delivery_speed,
        ];
    }
}