<?php

namespace App\Actions\Subcategories;

use App\Models\Subcategory;

class GetSubcategoryDetailsAction
{
    /**
     * Find a subcategory by ID with eager loaded relations.
     */
    public function execute(string $id): Subcategory
    {
        return Subcategory::with(['category', 'meals' => fn ($q) => $q->available()->limit(10)])
            ->withCount(['meals' => fn ($q) => $q->available()])
            ->findOrFail($id);
    }
}