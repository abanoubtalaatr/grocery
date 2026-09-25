<?php
namespace App\Services;

use App\Models\Cart;

class CartService
{
    public function __construct(
        protected ShippingService $shippingService
    ) {}

    public function getCartDetails(Cart $cart, ?string $deliveryType = null): Cart
    {
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingFee = $this->shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $cart->shipping_fee = $shippingFee;
            $cart->total_with_shipping = (float) $cart->total + $shippingFee;
        }

        return $cart;
    }
}