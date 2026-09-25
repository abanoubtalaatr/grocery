<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Accept common multipart / mobile field names and trim whitespace from OTP input.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeFirstFilledAlias('identifier', ['Identifier', 'email', 'Email', 'phone', 'Phone']);
        $this->mergeFirstFilledAlias('otp', ['OTP', 'otp_code', 'Otp', 'code', 'Code']);

        $merge = [];
        if ($this->has('identifier') && is_string($this->input('identifier'))) {
            $merge['identifier'] = trim($this->input('identifier'));
        }
        if ($this->has('otp')) {
            $merge['otp'] = preg_replace('/\D/', '', (string) $this->input('otp'));
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @param  array<int, string>  $aliases
     */
    private function mergeFirstFilledAlias(string $canonical, array $aliases): void
    {
        if ($this->filled($canonical)) {
            return;
        }
        foreach ($aliases as $key) {
            if ($this->filled($key)) {
                $this->merge([$canonical => $this->input($key)]);

                return;
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $otpLength = max(4, min(8, (int) config('otp.length', 6)));

        return [
            'identifier' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:'.$otpLength],
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
            'identifier.required' => 'Email or phone number is required.',
            'otp.required' => 'OTP code is required.',
            'otp.size' => 'OTP must be '.max(4, min(8, (int) config('otp.length', 6))).' digits.',
        ];
    }
}
