<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\UsernameMustContainLetter;
use App\Support\EgyptianPhoneRules;
use App\Support\EmailValidation;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
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
     * Normalize common client key casing so validation runs on the intended fields.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        $aliases = [
            'email' => ['Email'],
            'phone' => ['Phone', 'phone_number', 'phoneNumber'],
            'username' => ['Username', 'user_name', 'userName'],
        ];

        foreach ($aliases as $canonical => $keys) {
            if ($this->filled($canonical)) {
                continue;
            }
            foreach ($keys as $key) {
                if ($this->filled($key)) {
                    $merge[$canonical] = $this->input($key);
                    break;
                }
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }

        if ($this->has('phone') && is_string($this->input('phone'))) {
            $this->merge(['phone' => preg_replace('/\s+/', '', $this->input('phone'))]);
        }
    }

    /**
     * Return 400 with validation errors (product requirement for Register API).
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400));
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
                'max:'.User::USERNAME_MAX_LENGTH,
                'not_regex:/\s/u',
                'alpha_dash',
                new UsernameMustContainLetter,
                'min:3',
                'unique:users,username,NULL,id,deleted_at,NULL',
                function (string $_attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }
                    if (User::onlyTrashed()->where('username', $value)->exists()) {
                        $fail('This username was previously associated with a deleted account.');
                    }
                },
            ],
            'email' => [
                'nullable',
                'required_without:phone',
                ...EmailValidation::formatRules(),
                'max:255',
                'unique:users,email,NULL,id,deleted_at,NULL',
                function (string $_attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }
                    if (User::onlyTrashed()->whereRaw('LOWER(email) = ?', [strtolower(trim($value))])->exists()) {
                        $fail('This email was previously associated with a deleted account.');
                    }
                },
            ],
            'phone' => [
                'nullable',
                'string',
                'required_without:email',
                EgyptianPhoneRules::internationalPrefixRule(),
                'min:11',
                'max:13',
                EgyptianPhoneRules::mobileRule(),
                'unique:users,phone,NULL,id,deleted_at,NULL',
                function (string $_attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }
                    $normalized = preg_replace('/\s+/', '', $value);
                    if (User::onlyTrashed()->where('phone', $normalized)->exists()) {
                        $fail('This phone number was previously associated with a deleted account.');
                    }
                },
            ],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers(), 'max:20'],
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
        $usernameMax = User::USERNAME_MAX_LENGTH;

        return [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.max' => "Maximum {$usernameMax} characters allowed.",
            'username.not_regex' => 'Username must not contain spaces.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes and underscores.',
            'email.required_without' => 'Either email or phone number is required.',
            'email.email' => EmailValidation::invalidFormatMessage(),
            'email.regex' => EmailValidation::invalidFormatMessage(),
            'email.max' => 'The email address may not exceed 255 characters.',
            'email.not_regex' => EmailValidation::invalidFormatMessage(),
            'email.unique' => 'This email is already registered.',
            'phone.required_without' => 'Either phone number or email is required.',
            'phone.unique' => 'This phone number is already registered.',
            'phone.not_regex' => EgyptianPhoneRules::foreignPrefixMessage(),
            'phone.regex' => EgyptianPhoneRules::invalidMessage(),
            'phone.min' => EgyptianPhoneRules::lengthMessage(),
            'phone.max' => EgyptianPhoneRules::lengthMessage(),
            'password.confirmed' => 'Password confirmation does not match.',
            'agree_terms.required' => 'You must agree to the terms and conditions.',
            'agree_terms.accepted' => 'You must agree to the terms and conditions.',
        ];
    }
}
