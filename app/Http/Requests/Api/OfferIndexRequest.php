<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OfferIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'            => ['nullable', 'string'],
            'min_purchase'    => ['nullable', 'numeric', 'min:0'],
            'featured'        => ['nullable', 'boolean'],
            'search'          => ['nullable', 'string', 'max:100'],
            'order_by'        => ['nullable', 'string', 'in:created_at,title,minimum_purchase,discount_amount'],
            'order_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page'        => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}