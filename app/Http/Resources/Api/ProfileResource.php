<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'];
        $addresses = $this->resource['addresses'];
        $allOrders = $this->resource['orders'];
        $notifications = $this->resource['notifications'];

        $orderHistory = OrderSummaryResource::collection($allOrders);
        $inProgressOrders = OrderTrackingResource::collection(
            $allOrders->whereNotIn('status', ['cancelled', 'delivered'])->values()
        );

        return [
            'me' => new UserResource($user),
            'addresses' => AddressResource::collection($addresses),
            'order_history' => [
                'orders' => $orderHistory,
                'ordered_at' => $allOrders->map(fn ($o) => $o->placed_at?->toIso8601String() ?? $o->created_at?->toIso8601String())->values(),
            ],
            'in_progress_orders' => $inProgressOrders,
            'order_notifications' => UserNotificationResource::collection($notifications),
            'settings' => [
                'privacy_and_security' => [
                    'active_sessions' => SessionResource::collection($user->tokens),
                    'change_password' => ['available' => true],
                    'change_username' => ['available' => true],
                ],
            ],
            'wishlist' => WishlistItemResource::collection($user->favorites),
        ];
    }
}