<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use ValidationException;

class CreateOrderAction
{
    public function __construct(private ShippingService $shippingService) {}

    public function execute(User $user, array $data): Order
    {
        $cart = $user->activeCart()->with('items.meal')->first();

        if (!$cart || $cart->isEmpty()) {
            throw new \Exception('Your cart is empty. Please add items to your cart before placing an order.', 400);
        }

        $this->validateCartItems($cart->items);

        return DB::transaction(function () use ($user, $cart, $data) {
            $cart->calculateTotals();
            $shippingFee = $this->shippingService->calculateShippingFee((float) $cart->subtotal, $data['delivery_type']);

            $isHostedStripe = $data['payment_method'] === 'stripe_checkout';

            // 1. Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $data['delivery_type'] === 'delivery' ? ($data['address_id'] ?? null) : null,
                'payment_method' => $data['payment_method'],
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'delivery_type' => $data['delivery_type'],
                'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
                'subtotal' => (float) $cart->subtotal,
                'tax' => (float) $cart->tax,
                'discount' => (float) $cart->discount,
                'shipping_fee' => $shippingFee,
                'total' => (float) $cart->subtotal + (float) $cart->tax + $shippingFee,
                'notes' => $data['notes'] ?? null,
                'placed_at' => $isHostedStripe ? null : now(),
            ]);

            // 2. Attach Items & Decrement Stock
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'meal_id' => $cartItem->meal_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'discount_amount' => $cartItem->discount_amount,
                    'subtotal' => $cartItem->subtotal,
                ]);

                $cartItem->meal->decrement('stock_quantity', $cartItem->quantity);
            }

            // 3. Create Note if present
            if (isset($data['special_note_id']) || isset($data['notes'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => $data['special_note_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            // 4. Clear Cart
            $cart->items()->delete();
            $cart->update(['status' => 'completed']);

            return $order;
        });
    }

    private function validateCartItems($cartItems): void
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (!$meal) {
                throw new \Exception('One or more items in your cart are no longer available.', 400);
            }

            if (!$meal->is_available) {
                throw new \Exception("Meal '{$meal->title}' is currently unavailable", 400);
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                throw new \Exception("Only {$meal->stock_quantity} items available for '{$meal->title}'", 400);
            }

            if ($cartItem->quantity > $maxPerProduct) {
                throw new \Exception("Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'.", 400);
            }
        }
    }
}