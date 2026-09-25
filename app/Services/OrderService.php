<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class OrderService
{
    /**
     * Validate and process items from the user's cart.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|array  $cartItems
     * @return array{success: bool, items?: array<int, array<string, mixed>>, response?: array<string, mixed>, subtotal?: float}
     */
    public function validateAndProcessCartItems($cartItems): array
    {
        $items = [];
        $subtotal = 0.0;

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (! $meal) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'One or more items in your cart are no longer available.',
                    ],
                ];
            }

            if (! $meal->is_available) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Meal '{$meal->title}' is currently unavailable",
                    ],
                ];
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Only {$meal->stock_quantity} items available for '{$meal->title}'",
                    ],
                ];
            }

            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            if ($cartItem->quantity > $maxPerProduct) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'.",
                    ],
                ];
            }

            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];

            $subtotal += (float) $cartItem->subtotal;
        }

        return [
            'success' => true,
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Calculate order totals.
     */
    public function calculateTotals(float $subtotal, string $deliveryType): array
    {
        $tax = $subtotal * 0.1;
        $discount = 0.0;
        $shippingFee = $this->calculateShippingFee($subtotal, $deliveryType);
        $total = $subtotal + $tax + $shippingFee;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'shipping_fee' => $shippingFee,
            'total' => (float) $total,
        ];
    }

    /**
     * Calculate shipping fee.
     */
    public function calculateShippingFee(float $subtotal, string $deliveryType): float
    {
        if ($deliveryType !== 'delivery') {
            return 0.0;
        }

        $settings = \App\Models\Setting::getSettings();
        $fee = (float) ($settings->shipping_fee ?? 0);
        $minForFree = $settings->free_shipping_min_order !== null
            ? (float) $settings->free_shipping_min_order
            : null;

        if ($minForFree !== null && $subtotal >= $minForFree) {
            return 0.0;
        }

        return $fee;
    }

    /**
     * Process payment for card orders.
     */
    public function processPayment(User $user, array $validated, float $total): array
    {
        if ($validated['payment_method'] !== 'card') {
            return ['success' => true];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Stripe customer not found. Please add a payment method first.',
                ],
            ];
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($total * 100),
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $validated['payment_method_id'],
                'off_session' => true,
                'confirm' => true,
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'Payment failed: ' . $paymentIntent->status,
                    ],
                ];
            }

            return [
                'success' => true,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Payment processing failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Create order record.
     */
    public function createOrder(User $user, array $validated, float $subtotal, array $totals, ?string $stripePaymentIntentId = null): Order
    {
        $isHostedStripe = $validated['payment_method'] === 'stripe_checkout';

        return Order::create([
            'user_id' => $user->id,
            'address_id' => $validated['delivery_type'] === 'delivery' ? $validated['address_id'] : null,
            'payment_method' => $validated['payment_method'],
            'payment_method_id' => null,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'delivery_type' => $validated['delivery_type'],
            'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
            'subtotal' => $subtotal,
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'shipping_fee' => $totals['shipping_fee'],
            'total' => $totals['total'],
            'notes' => $validated['notes'] ?? null,
            'placed_at' => $isHostedStripe ? null : now(),
            'schedule_delivery' => $validated['schedule_delivery'] ?? null,
            'delivery_speed' => $validated['delivery_speed'] ?? null,
            'estimated_delivery_time' => $validated['estimated_delivery_time'] ?? null,
        ]);
    }

    /**
     * Create order items and update stock.
     */
    public function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'meal_id' => $item['meal']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'subtotal' => $item['subtotal'],
            ]);

            $item['meal']->decrement('stock_quantity', $item['quantity']);
        }
    }

    /**
     * Clear the user's active cart.
     */
    public function clearUserCart(User $user): void
    {
        $cart = $user->activeCart()->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->update(['status' => 'completed']);
        }
    }

    /**
     * Get the most recent active order for a user.
     */
    public function trackOrder(User $user): ?Order
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Build tracking payload for the order.
     */
    public function buildTrackingData(Order $order): array
    {
        return [
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
        ];
    }

    /**
     * Format order response payload.
     */
    public function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method,
            'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
            'delivery_type' => $order->delivery_type,
            'status' => $order->status,
            'status_position' => $order->status_position,
            'status_description' => $order->status_description,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meal' => [
                        'id' => $item->meal->id,
                        'title' => $item->meal->title,
                        'slug' => $item->meal->slug,
                        'image_url' => $item->meal->image_url,
                        ...$item->meal->getApiPriceAttributes(),
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
            })->values(),
            'address' => $order->address ? [
                'id' => $order->address->id,
                'label' => $order->address->label,
                'full_name' => $order->address->full_name,
                'phone' => $order->address->phone,
                'country_code' => $order->address->country_code,
                'street_address' => $order->address->street_address,
                'building_number' => $order->address->building_number,
                'floor' => $order->address->floor,
                'apartment' => $order->address->apartment,
                'landmark' => $order->address->landmark,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country,
                'full_address' => $order->address->full_address,
                'latitude' => $order->address->latitude,
                'longitude' => $order->address->longitude,
            ] : null,
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'shipping_fee' => (float) ($order->shipping_fee ?? 0),
            'total' => $order->total,
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'placed_at' => $order->placed_at,
            'processing_at' => $order->processing_at,
            'shipping_at' => $order->shipping_at,
            'out_for_delivery_at' => $order->out_for_delivery_at,
            'delivered_at' => $order->delivered_at,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'special_note' => $order->special_note,
            'schedule_delivery' => $order->schedule_delivery,
            'delivery_speed' => $order->delivery_speed,
        ];
    }
}
