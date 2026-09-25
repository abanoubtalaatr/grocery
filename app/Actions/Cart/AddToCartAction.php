<?php
namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\Meal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddToCartAction
{
    public function handle(Cart $cart, array $data): void
    {
        $meal = Meal::findOrFail($data['meal_id']);
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        if (!$meal->is_available || !$meal->isInStock()) {
            throw ValidationException::withMessages(['meal_id' => 'This meal is currently unavailable or out of stock']);
        }

        if ($meal->stock_quantity < $data['quantity']) {
            throw ValidationException::withMessages(['quantity' => "Only {$meal->stock_quantity} items available in stock"]);
        }

        DB::transaction(function () use ($cart, $meal, $data, $maxPerProduct) {
            $cartItem = $cart->items()->where('meal_id', $meal->id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $data['quantity'];
                $effectiveMax = min($maxPerProduct, $meal->stock_quantity);

                if ($newQuantity > $effectiveMax) {
                    throw ValidationException::withMessages([
                        'quantity' => "Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart."
                    ]);
                }

                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $discountAmount = $meal->resolved_discount_price 
                    ? ($meal->price - $meal->resolved_discount_price) * $data['quantity'] 
                    : 0;

                $cart->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $data['quantity'],
                    'unit_price' => $meal->final_price,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $meal->final_price * $data['quantity'],
                ]);
            }

            $cart->calculateTotals();
        });
    }
}