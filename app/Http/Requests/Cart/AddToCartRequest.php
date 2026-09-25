<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $max = config('cart.max_quantity_per_product', 10);

        return [
            'meal_id' => ['required', 'integer', 'exists:meals,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$max],
        ];
    }
}