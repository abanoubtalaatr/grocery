<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function handle(User $user): Cart
    {
        $cart = $user->getOrCreateCart();

        return DB::transaction(function () use ($cart) {
            $cart->items()->delete();
            $cart->calculateTotals();

            return $cart;
        });
    }
}
