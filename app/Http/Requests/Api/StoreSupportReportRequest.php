<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'issue_type'   => ['required', 'string', 'min:2', 'max:255'],
            'order_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('orders', 'order_number')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'message'      => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.exists' => 'Order number not found on your account.',
        ];
    }
}