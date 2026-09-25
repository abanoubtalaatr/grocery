<?php

namespace App\Actions\Offers;

use App\Models\Offer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetFilteredOffersAction
{
    /**
     * Execute the action to filter and paginate active offers.
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Offer::active();

        // Filter by type
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by minimum purchase (Isolated scope)
        if (isset($filters['min_purchase'])) {
            $minPurchase = $filters['min_purchase'];
            $query->where(function ($q) use ($minPurchase) {
                $q->where('minimum_purchase', '<=', $minPurchase)
                  ->orWhereNull('minimum_purchase');
            });
        }

        // Featured offers filter
        if (! empty($filters['featured'])) {
            $query->featured();
        }

        // Search by title or code
        if (! empty($filters['search'])) {
            $search = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Ordering & Pagination
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy($orderBy, $orderDirection)->paginate($perPage);
    }
}