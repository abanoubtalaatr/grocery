<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class RemoveCartItemAction
{
    public function handle(CartItem $cartItem): void
    {
        DB::transaction(fn () => $cartItem->delete());
    }
}