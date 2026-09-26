<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Order;

class GetUserOrdersAction
{
    public function execute(User $user): Collection
    {
        return Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->latest()
            ->get();
    }
}