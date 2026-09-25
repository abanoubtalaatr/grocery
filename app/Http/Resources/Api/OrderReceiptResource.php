<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;
        $address = $this->address;

        return [
            'receipt_number'    => $this->order_number,
            'invoice_number'    => 'INV-' . str_pad($this->id, 8, '0', STR_PAD_LEFT),
            'type'              => 'receipt',
            'date'              => $this->placed_at ?? $this->created_at,
            'payment_date'      => $this->placed_at ?? $this->created_at,
            'status'            => $this->status,
            'status_description' => $this->status_description,

            'customer' => [
                'id'           => $user?->id,
                'name'         => $user?->full_name ?? $user?->username ?? 'Customer',
                'firstname'    => $user?->firstname,
                'lastname'     => $user?->lastname,
                'email'        => $user?->email,
                'phone'        => $user?->phone,
                'country_code' => $user?->country_code,
            ],

            'delivery_address' => $address ? new AddressResource($address) : null,

            'payment' => [
                'method'                   => $this->payment_method,
                'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
                'method_display'           => $this->getPaymentMethodDisplay($this->payment_method),
            ],

            'items' => $this->items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'meal' => [
                        'id'          => $item->meal->id,
                        'title'       => $item->meal->title,
                        'category'    => $item->meal->category ? [
                            'id'   => $item->meal->category->id,
                            'name' => $item->meal->category->name,
                        ] : null,
                        'subcategory' => $item->meal->subcategory ? [
                            'id'   => $item->meal->subcategory->id,
                            'name' => $item->meal->subcategory->name,
                        ] : null,
                    ],
                    'quantity'        => $item->quantity,
                    'unit_price'      => (float) $item->unit_price,
                    'discount_amount' => (float) $item->discount_amount,
                    'subtotal'        => (float) $item->subtotal,
                ];
            }),

            'pricing' => [
                'subtotal' => (float) $this->subtotal,
                'tax'      => (float) $this->tax,
                'tax_rate' => $this->subtotal > 0 ? round(($this->tax / $this->subtotal) * 100, 2) : 0,
                'discount' => (float) $this->discount,
                'total'    => (float) $this->total,
            ],

            'delivery' => [
                'type'                    => $this->delivery_type,
                'estimated_delivery_time' => $this->estimated_delivery_time,
                'placed_at'               => $this->placed_at,
                'processing_at'           => $this->processing_at,
                'shipping_at'             => $this->shipping_at,
                'out_for_delivery_at'     => $this->out_for_delivery_at,
                'delivered_at'            => $this->delivered_at,
            ],

            'notes'      => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getPaymentMethodDisplay(?string $method): string
    {
        if (!$method) return '';

        return match($method) {
            'card'             => 'Credit/Debit Card',
            'stripe_checkout'  => 'Card (Stripe Checkout)',
            'cash_on_delivery' => 'Cash on Delivery',
            'cash'             => 'Cash',
            'stripe'           => 'Stripe',
            default            => ucfirst(str_replace('_', ' ', $method)),
        };
    }
}