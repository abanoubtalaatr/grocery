<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);

        return [
            'quantity' => ['required', 'integer', 'min:1', "max:{$maxPerProduct}"],
        ];
    }
}