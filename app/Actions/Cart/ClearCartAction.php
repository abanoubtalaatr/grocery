<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function handle(Cart $cart): void
    {
        DB::transaction(fn () => $cart->items()->delete());
    }
}