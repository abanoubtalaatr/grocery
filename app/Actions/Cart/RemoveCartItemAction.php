<?php

namespace App\Actions\Cart;

use App\Models\CartItem;

class RemoveCartItemAction
{
    public function handle(CartItem $cartItem): void
    {
        $cartItem->delete();
    }
}