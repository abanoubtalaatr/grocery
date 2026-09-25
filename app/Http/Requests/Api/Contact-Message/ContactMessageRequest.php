<?php

namespace App\Http\Requests;

use App\Support\EmailValidation;
use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                ...EmailValidation::formatRules(),
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:250',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.not_regex' =>
                EmailValidation::trailingHyphenDotBeforeAtMessage(),

            'email.regex' =>
                EmailValidation::domainStructureMessage(),

            'email.max' =>
                'The email address may not exceed 255 characters.',
        ];
    }
}