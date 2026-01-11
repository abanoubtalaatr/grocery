<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'payment_method' => ['required', 'string', Rule::in(['card', 'cash_on_delivery'])],
            'payment_method_id' => ['nullable', 'string', 'required_if:payment_method,card'],
            'delivery_type' => ['required', 'string', Rule::in(['delivery', 'pickup'])],
            'address_id' => [
                'nullable',
                'exists:addresses,id',
                'required_if:delivery_type,delivery',
                Rule::exists('addresses', 'id')->where('user_id', $userId),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method_id.required_if' => 'Payment method ID is required when using card payment.',
            'address_id.required_if' => 'Address is required for delivery orders.',
        ];
    }
}
