<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('login') || ! is_string($this->input('login'))) {
            return;
        }

        $login = trim($this->input('login'));
        if ($login !== '' && ! filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $login = preg_replace('/\s+/', '', $login);
        }

        $this->merge(['login' => $login]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
                function (string $_attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $login = trim($value);

                    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                        $email = strtolower($login);
                        $user = User::withTrashed()->where('email', $email)->first();
                        if ($user === null) {
                            $fail('Email is not registered.');
                        } elseif ($user->trashed()) {
                            $fail('This account has been deleted.');
                        }

                        return;
                    }

                    $phone = preg_replace('/\s+/', '', $login);
                    $user = User::withTrashed()->where('phone', $phone)->first();
                    if ($user === null) {
                        $fail('Phone number is not registered.');
                    } elseif ($user->trashed()) {
                        $fail('This account has been deleted.');
                    }
                },
            ],
            'password' => ['required', 'string'],
        ];
    }

    /**
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
