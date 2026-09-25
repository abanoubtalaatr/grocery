<?php

namespace App\Actions\Subcategories;

use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Collection;

class GetFilteredSubcategoriesAction
{
    /**
     * Get active subcategories optionally filtered by category.
     */
    public function execute(array $filters = []): Collection
    {
        $query = Subcategory::with('category')
            ->withCount(['meals' => fn ($q) => $q->available()])
            ->active();

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->inRandomOrder()->get();
    }
}