<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetContactMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|string|in:created_at,name,email,subject',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}