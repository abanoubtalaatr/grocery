<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ClearAllNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'         => 'sometimes|string|in:read,unread,all',
            'confirmation' => 'required|boolean|accepted',
        ];
    }
}