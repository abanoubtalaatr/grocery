<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
            'site_name'        => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string'],
            'email'            => ['nullable', 'email', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'address'          => ['nullable', 'string', 'max:500'],
            'facebook'         => ['nullable', 'url', 'max:255'],
            'linkedin'         => ['nullable', 'url', 'max:255'],
            'instagram'        => ['nullable', 'url', 'max:255'],
            'twitter'          => ['nullable', 'url', 'max:255'],
            'copyright_text'   => ['nullable', 'string', 'max:255'],
            'logo'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'favicon'          => ['nullable', 'image', 'mimes:ico,png', 'max:1024'],
        ];
    }
}