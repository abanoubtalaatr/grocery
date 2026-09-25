<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get the user's cart payload, optionally including shipping data.
     */
    public function index(User $user, ?string $deliveryType = null): Cart
    {
        $cart = $user->getOrCreateCart();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        if ($deliveryType !== null && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingService = app(ShippingService::class);
            $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $cart->shipping_fee = (float) $shippingFee;
            $cart->total_with_shipping = (float) $cart->total + $shippingFee;
        }

        return $cart;
    }
}
