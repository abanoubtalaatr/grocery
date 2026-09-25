<?php

namespace App\Actions\Subcategories;

use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetSubcategoryMealsAction
{
    /**
     * Get paginated meals for a given subcategory.
     */
    public function execute(Subcategory $subcategory, array $filters): LengthAwarePaginator
    {
        $query = $subcategory->meals()->with('category')->available();

        if (isset($filters['featured'])) {
            filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN)
                ? $query->featured()
                : $query->where('is_featured', false);
        }

        if (isset($filters['in_stock'])) {
            filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN)
                ? $query->inStock()
                : $query->outOfStock();
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        $allowedSortFields = ['created_at', 'price', 'rating', 'title', 'sold_count'];
        if (in_array($sortBy, $allowedSortFields, true)) {
            if ($sortBy === 'price') {
                $query->orderByRaw("COALESCE(discount_price, price) {$sortOrder}");
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 50);

        return $query->paginate($perPage)->withQueryString();
    }
}