<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        return [
            'login' => [
                'required',
                'string',
                // Custom validation: Check active user by email or phone, not soft deleted
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::where('email', $value)
                        ->orWhere('phone', $value)
                        ->first();

                    if (!$user || $user->deleted_at !== null) {
                        $fail(__('auth.failed')); // Returns "These credentials do not match our records."
                    }
                }
            ],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Email or phone number is required.',
            'password.required' => 'Password is required.',
        ];
    }
}
