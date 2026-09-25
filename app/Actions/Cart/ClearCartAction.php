<?php

namespace App\Actions\Cart;

use App\Models\Cart;

class ClearCartAction
{
    public function handle(Cart $cart): void
    {
        $cart->items()->delete();
    }
}