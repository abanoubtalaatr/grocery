<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (count($this->allFiles()) > 1 || is_array($this->file('image'))) {
            abort(response()->json([
                'success' => false,
                'message' => 'Only one profile image is allowed',
                'errors'  => ['image' => ['Only one profile image is allowed']],
            ], 422));
        }
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}