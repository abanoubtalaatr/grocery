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
            'special_note_id' => ['nullable', 'exists:special_notes,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'contacts_information' => ['nullable'],
            'schedule_delivery' => ['nullable', 'string', 'max:255'],
            'delivery_speed' => ['nullable', 'string', 'max:255'],
            'estimated_delivery_time' => ['nullable', 'integer', 'min:0'],
            'contacts_information.first_name' => ['nullable', 'string', 'max:255'],
            'contacts_information.last_name' => ['nullable', 'string', 'max:255'],
            'contacts_information.email' => ['nullable', 'email', 'max:255'],
            'contacts_information.phone' => ['nullable', 'string', 'max:20'],
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
