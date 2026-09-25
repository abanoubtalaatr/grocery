<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Optional shipping fee and total with shipping.
     */
    protected ?float $shippingFee = null;
    protected ?float $totalWithShipping = null;

    /**
     * Pass shipping details to the resource.
     */
    public function withShipping(?float $shippingFee, ?float $totalWithShipping): self
    {
        $this->shippingFee = $shippingFee;
        $this->totalWithShipping = $totalWithShipping;
        return $this;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'status' => $this->isEmpty() ? 'empty' : 'not empty',
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'item_count' => $this->item_count,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'is_empty' => $this->isEmpty(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->shippingFee !== null && $this->totalWithShipping !== null) {
            $data['shipping_fee'] = (float) $this->shippingFee;
            $data['total_with_shipping'] = (float) $this->totalWithShipping;
        }

        return $data;
    }
}