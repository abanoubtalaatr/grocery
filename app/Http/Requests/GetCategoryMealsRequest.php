<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetCategoryMealsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'featured' => 'nullable|boolean',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'in_stock' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:created_at,price,rating,title,sold_count,newest',
            'sort_order' => 'nullable|string|in:asc,desc,ASC,DESC',
            'per_page' => 'nullable|integer|min:1|max:50',
        ];
    }
}