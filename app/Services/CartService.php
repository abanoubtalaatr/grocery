<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCartForUser(User $user): Cart
    {
        $cart = $user->getOrCreateCart();
        return $cart->load(['items.meal.category', 'items.meal.subcategory']);
    }

    public function addItem(User $user, int $mealId, int $quantity): array
    {
        $meal = Meal::findOrFail($mealId);

        if (! $meal->is_available) {
            return ['error' => 'This meal is currently unavailable', 'code' => 400];
        }

        if (! $meal->isInStock()) {
            return ['error' => 'This meal is out of stock', 'code' => 400];
        }

        if ($meal->stock_quantity < $quantity) {
            return ['error' => "Only {$meal->stock_quantity} items available in stock", 'code' => 400];
        }

        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $cart = $user->getOrCreateCart();

        return DB::transaction(function () use ($cart, $meal, $quantity, $maxPerProduct, $user) {
            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);

                if ($newQuantity > $effectiveMax) {
                    return [
                        'error' => "Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.",
                        'code' => 400,
                    ];
                }

                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $discountAmount = $meal->resolved_discount_price 
                    ? ($meal->price - $meal->resolved_discount_price) * $quantity 
                    : 0;

                $cart->items()->create([
                    'meal_id'         => $meal->id,
                    'quantity'        => $quantity,
                    'unit_price'      => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal'        => $meal->final_price * $quantity,
                ]);
            }

            $cart->calculateTotals();

            return ['cart' => $this->getCartForUser($user)];
        });
    }

    public function updateItem(User $user, string $itemId, int $quantity): array
    {
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);

        if ($cartItem->meal->stock_quantity < $quantity) {
            return ['error' => "Only {$cartItem->meal->stock_quantity} items available in stock", 'code' => 400];
        }

        DB::transaction(function () use ($cart, $cartItem, $quantity) {
            $cartItem->update(['quantity' => $quantity]);
            $cart->calculateTotals();
        });

        return ['cart' => $this->getCartForUser($user)];
    }

    public function removeItem(User $user, string $itemId): Cart
    {
        $cart = $user->getOrCreateCart();

        DB::transaction(function () use ($cart, $itemId) {
            $cart->items()->where('id', $itemId)->delete();
            $cart->calculateTotals();
        });

        return $this->getCartForUser($user);
    }

    public function clearCart(User $user): Cart
    {
        $cart = $user->getOrCreateCart();

        DB::transaction(function () use ($cart) {
            $cart->items()->delete();
            $cart->calculateTotals();
        });

        return $this->getCartForUser($user);
    }
}