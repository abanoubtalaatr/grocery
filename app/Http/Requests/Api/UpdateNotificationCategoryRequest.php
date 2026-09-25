<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationCategoryRequest extends FormRequest
{
    private const BOOLEAN_VALUES = [true, false, 0, 1];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', Rule::in(self::BOOLEAN_VALUES)],
        ];
    }

    public function messages(): array
    {
        return [
            'enabled.required' => 'The enabled field is required.',
            'enabled.in' => 'The enabled field must be true, false, 0, or 1.',
        ];
    }
}
