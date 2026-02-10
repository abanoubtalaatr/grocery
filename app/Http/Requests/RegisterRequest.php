<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                'min:3',
                // Only unique among accounts that are not soft-deleted
                'unique:users,username,NULL,id,deleted_at,NULL'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'required_without:phone',
                // Only unique among accounts that are not soft-deleted
                'unique:users,email,NULL,id,deleted_at,NULL'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'min:10',
                'required_without:email',
                'regex:/^\+?[1-9]\d{1,14}$/',
                // Only unique among accounts that are not soft-deleted
                'unique:users,phone,NULL,id,deleted_at,NULL'
            ],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers(),'max:20'],
            'agree_terms' => ['required', 'accepted'],
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
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes and underscores.',
            'email.required_without' => 'Either email or phone number is required.',
            'email.email' => 'Please enter a valid email address (e.g., example@example.com).',
            'email.unique' => 'This email is already registered.',
            'phone.required_without' => 'Either phone number or email is required.',
            'phone.unique' => 'This phone number is already registered.',
            'phone.regex' => 'Please enter a valid phone number with country code (e.g., +1234567890).',
            'password.confirmed' => 'Password confirmation does not match.',
            'agree_terms.required' => 'You must agree to the terms and conditions.',
            'agree_terms.accepted' => 'You must agree to the terms and conditions.',
        ];
    }
}
