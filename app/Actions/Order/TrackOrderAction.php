<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;

class TrackOrderAction
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function handle(User $user): ?Order
    {
        return $this->orderService->trackOrder($user);
    }
}
