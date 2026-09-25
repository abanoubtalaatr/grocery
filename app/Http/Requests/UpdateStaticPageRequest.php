<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaticPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staticPage = $this->route('staticPage') ?? $this->route('static_page');
        $pageId = is_object($staticPage) ? $staticPage->id : $staticPage;

        return [
            'slug'             => ['sometimes', 'required', 'string', 'max:100', Rule::unique('static_pages', 'slug')->ignore($pageId)],
            'title'            => ['sometimes', 'required', 'string', 'max:255'],
            'content'          => ['sometimes', 'required', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'array'],
            'is_published'     => ['sometimes', 'boolean'],
            'order'            => ['nullable', 'integer'],
        ];
    }
}