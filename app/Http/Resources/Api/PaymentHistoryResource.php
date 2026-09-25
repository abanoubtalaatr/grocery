<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'order_number'             => $this->order_number,
            'payment_method'           => $this->payment_method,
            'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
            'amount'                   => (float) $this->total,
            'subtotal'                 => (float) $this->subtotal,
            'tax'                      => (float) $this->tax,
            'discount'                 => (float) $this->discount,
            'status'                   => $this->status,
            'status_description'       => $this->status_description,
            'payment_date'             => $this->placed_at ?? $this->created_at,
            'created_at'               => $this->created_at,
            'items_count'              => $this->items->sum('quantity'),
        ];
    }
}