<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaticPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug'             => ['required', 'string', 'max:100', 'unique:static_pages,slug'],
            'title'            => ['required', 'string', 'max:255'],
            'content'          => ['required', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'array'],
            'is_published'     => ['boolean'],
            'order'            => ['nullable', 'integer'],
        ];
    }
}