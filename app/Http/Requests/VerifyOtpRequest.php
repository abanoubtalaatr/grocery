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
