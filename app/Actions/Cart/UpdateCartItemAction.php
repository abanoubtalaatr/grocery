<?php
namespace App\Actions\Cart;

use App\Models\CartItem;
use Illuminate\Validation\ValidationException;

class UpdateCartItemAction
{
    public function handle(CartItem $cartItem, int $quantity): void
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        if ($quantity > $maxPerProduct) {
            throw ValidationException::withMessages([
                'quantity' => "Maximum {$maxPerProduct} units per product allowed.",
            ]);
        }

        if ($cartItem->meal->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$cartItem->meal->stock_quantity} items available in stock"
            ]);
        }

        $cartItem->update(['quantity' => $quantity]);
        $cartItem->cart->calculateTotals();
    }
}