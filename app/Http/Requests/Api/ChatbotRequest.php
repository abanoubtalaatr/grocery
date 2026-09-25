<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (
            $this->filled('message') &&
            ! $this->filled('question')
        ) {
            $this->merge([
                'question' => $this->input('message'),
            ]);
        }

        if (is_string($this->input('question'))) {
            $this->merge([
                'question' => trim($this->input('question')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'question' => [
                'required',
                'string',
                'max:1000',
            ],

            'conversation_id' => [
                'nullable',
                'uuid',
            ],

            'session_id' => [
                'nullable',
                'uuid',
            ],

            'rating' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
            ],

            'locale' => [
                'nullable',
                'string',
                'in:ar,en',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' =>
                'A non-empty question is required.',

            'question.string' =>
                'Send a single question text only.',

            'question.max' =>
                'The question may not be greater than 1000 characters.',
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}