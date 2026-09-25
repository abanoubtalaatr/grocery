<?php

namespace App\Actions\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUserPaymentHistoryAction
{
    public function execute(User $user): Collection
    {
        return Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->with(['items.meal.category', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}