<?php

namespace App\Services;

use App\Exceptions\CartOperationException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private readonly ShippingService $shippingService
    ) {
    }

    public function getCart(User $user): Cart
    {
        return $user->getOrCreateCart();
    }

    public function addItem(
        User $user,
        int $mealId,
        int $quantity
    ): Cart {
        $cart = $this->getCart($user);

        $meal = Meal::findOrFail($mealId);

        $this->ensureMealCanBeAdded(
            $meal,
            $quantity
        );

        DB::transaction(function () use (
            $cart,
            $meal,
            $quantity
        ): void {
            $cartItem = $cart->items()
                ->where('meal_id', $meal->id)
                ->first();

            if ($cartItem) {
                $this->increaseExistingItem(
                    $cartItem,
                    $meal,
                    $quantity
                );

                return;
            }

            $cart->items()->create([
                'meal_id' => $meal->id,
                'quantity' => $quantity,
                'unit_price' => $meal->final_price,
                'discount_amount' => $this->calculateDiscountAmount(
                    $meal,
                    $quantity
                ),
                'subtotal' => $meal->final_price * $quantity,
            ]);
        });

        $this->loadCartRelations($cart);

        return $cart;
    }

    public function updateItem(
        User $user,
        CartItem $item,
        int $quantity
    ): Cart {
        $cart = $this->getCart($user);

        $this->ensureItemBelongsToCart(
            $item,
            $cart
        );

        $meal = $item->meal;

        $this->ensureStockAvailable(
            $meal,
            $quantity
        );

        DB::transaction(function () use (
            $item,
            $quantity
        ): void {
            $item->update([
                'quantity' => $quantity,
            ]);
        });

        $this->loadCartRelations($cart);

        return $cart;
    }

    public function removeItem(
        User $user,
        CartItem $item
    ): Cart {
        $cart = $this->getCart($user);

        $this->ensureItemBelongsToCart(
            $item,
            $cart
        );

        DB::transaction(function () use ($item): void {
            $item->delete();
        });

        $this->loadCartRelations($cart);

        return $cart;
    }

    public function clear(User $user): Cart
    {
        $cart = $this->getCart($user);

        DB::transaction(function () use ($cart): void {
            $cart->items()->delete();
        });

        $this->loadCartRelations($cart);

        return $cart;
    }

    public function calculateShipping(
        Cart $cart,
        ?string $deliveryType
    ): array {
        if (
            ! $deliveryType ||
            ! in_array(
                $deliveryType,
                ['delivery', 'pickup'],
                true
            )
        ) {
            return [
                'shipping_fee' => null,
                'total_with_shipping' => null,
            ];
        }

        $shippingFee = $this->shippingService->calculateShippingFee(
            (float) $cart->subtotal,
            $deliveryType
        );

        return [
            'shipping_fee' => $shippingFee,
            'total_with_shipping' => (float) $cart->total + $shippingFee,
        ];
    }

    private function ensureMealCanBeAdded(
        Meal $meal,
        int $quantity
    ): void {
        if (! $meal->is_available) {
            throw new CartOperationException(
                'This meal is currently unavailable'
            );
        }

        if (! $meal->isInStock()) {
            throw new CartOperationException(
                'This meal is out of stock'
            );
        }

        $this->ensureStockAvailable(
            $meal,
            $quantity
        );
    }

    private function ensureStockAvailable(
        Meal $meal,
        int $quantity
    ): void {
        if ($meal->stock_quantity < $quantity) {
            throw new CartOperationException(
                "Only {$meal->stock_quantity} items available in stock"
            );
        }
    }

    private function increaseExistingItem(
        CartItem $cartItem,
        Meal $meal,
        int $quantity
    ): void {
        $newQuantity = $cartItem->quantity + $quantity;

        $maxPerProduct = config(
            'cart.max_quantity_per_product',
            10
        );

        $effectiveMax = min(
            $maxPerProduct,
            $meal->stock_quantity
        );

        if ($newQuantity > $effectiveMax) {
            throw new CartOperationException(
                "Maximum {$maxPerProduct} units per product. " .
                "You already have {$cartItem->quantity} in cart; " .
                "maximum total is {$effectiveMax}."
            );
        }

        $this->ensureStockAvailable(
            $meal,
            $newQuantity
        );

        $cartItem->update([
            'quantity' => $newQuantity,
        ]);
    }

    private function calculateDiscountAmount(
        Meal $meal,
        int $quantity
    ): float {
        if (! $meal->resolved_discount_price) {
            return 0.0;
        }

        return (
            $meal->price -
            $meal->resolved_discount_price
        ) * $quantity;
    }

    private function ensureItemBelongsToCart(
        CartItem $item,
        Cart $cart
    ): void {
        if ($item->cart_id !== $cart->id) {
            throw new CartOperationException(
                'Cart item not found',
                404
            );
        }
    }

    private function loadCartRelations(Cart $cart): void
    {
        $this->forgetLoadedItems($cart);

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);
    }

    private function forgetLoadedItems(Cart $cart): void
    {
        $cart->unsetRelation('items');
    }
}