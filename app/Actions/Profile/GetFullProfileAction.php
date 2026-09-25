<?php

namespace App\Actions\Profile;

use App\Models\Order;
use App\Models\User;

class GetFullProfileAction
{
    public function execute(User $user): array
    {
        $user->load(['addresses', 'favorites.meal.category', 'favorites.meal.subcategory']);

        $addresses = $user->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $orders = Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        $notifications = $user->notifications()
            ->where(function ($q) {
                $q->where('data->type', 'order_confirmation')
                    ->orWhere('data->type', 'order_shipped')
                    ->orWhere('data->type', 'delivery_updates');
            })
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return [
            'user'          => $user,
            'addresses'     => $addresses,
            'orders'        => $orders,
            'notifications' => $notifications,
        ];
    }
}