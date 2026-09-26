<?php

namespace App\Services;

use App\Models\Order;

class InvoiceService
{
    public function formatReceipt(Order $order): array
    {
        $user = $order->user;
        $address = $order->address;

        return [
            'receipt_number' => $order->order_number,
            'invoice_number' => 'INV-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
            'type' => 'invoice', 
            'date' => $order->placed_at ?? $order->created_at,
            'payment_date' => $order->placed_at ?? $order->created_at,
            'status' => $order->status,
            'status_description' => $order->status_description,

            // Customer Information
            'customer' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->username ?? 'Customer',
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
            ],

            // Delivery Address
            'delivery_address' => $address ? [
                'id' => $address->id,
                'label' => $address->label,
                'full_name' => $address->full_name,
                'phone' => $address->phone,
                'country_code' => $address->country_code,
                'street_address' => $address->street_address,
                'building_number' => $address->building_number,
                'floor' => $address->floor,
                'apartment' => $address->apartment,
                'landmark' => $address->landmark,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'full_address' => $address->full_address,
            ] : null,

            // Payment Information
            'payment' => [
                'method' => $order->payment_method,
                'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
                'method_display' => $this->getPaymentMethodDisplay($order->payment_method),
            ],

            // Order Items
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meal' => [
                        'id' => $item->meal->id,
                        'title' => $item->meal->title,
                        'category' => $item->meal->category ? [
                            'id' => $item->meal->category->id,
                            'name' => $item->meal->category->name,
                        ] : null,
                        'subcategory' => $item->meal->subcategory ? [
                            'id' => $item->meal->subcategory->id,
                            'name' => $item->meal->subcategory->name,
                        ] : null,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount_amount' => (float) $item->discount_amount,
                    'subtotal' => (float) $item->subtotal,
                ];
            }),

            // Pricing Summary
            'pricing' => [
                'subtotal' => (float) $order->subtotal,
                'tax' => (float) $order->tax,
                'tax_rate' => $order->subtotal > 0 ? round(($order->tax / $order->subtotal) * 100, 2) : 0,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
            ],

            // Delivery Information
            'delivery' => [
                'type' => $order->delivery_type,
                'estimated_delivery_time' => $order->estimated_delivery_time,
                'placed_at' => $order->placed_at,
                'processing_at' => $order->processing_at,
                'shipping_at' => $order->shipping_at,
                'out_for_delivery_at' => $order->out_for_delivery_at,
                'delivered_at' => $order->delivered_at,
            ],

            // Additional Information
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    /**
     * Get payment method display name.
     */
    private function getPaymentMethodDisplay(string $method): string
    {
        return match ($method) {
            'card' => 'Credit/Debit Card',
            'stripe_checkout' => 'Card (Stripe Checkout)',
            'cash_on_delivery' => 'Cash on Delivery',
            'cash' => 'Cash',
            'stripe' => 'Stripe',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }
}
