<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_number'      => $this->order_number,
            'status'            => $this->status,
            'status_description' => $this->status_description,
            'total'             => (float) $this->total,
            'placed_at'         => $this->placed_at?->toIso8601String(),
            'created_at'        => $this->created_at?->toIso8601String(),
            'item_count'        => $this->items->count(),
        ];
    }
}