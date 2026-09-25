<?php

namespace App\Http\Requests\Api\Contact;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'in:read,replied,spam',
            ],

            'admin_notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}