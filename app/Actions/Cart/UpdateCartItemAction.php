<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCartItemAction
{
    public function handle(User $user, string $itemId, int $quantity): Cart
    {
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);
        $meal = $cartItem->meal;

        if ($meal->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$meal->stock_quantity} items available in stock."],
            ]);
        }

        return DB::transaction(function () use ($cart, $cartItem, $quantity) {
            $cartItem->update(['quantity' => $quantity]);
            $cart->calculateTotals();
            $cart->load(['items.meal.category', 'items.meal.subcategory']);

            return $cart;
        });
    }
}
