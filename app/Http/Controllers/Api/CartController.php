<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use App\Services\ShippingService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected CartService $cartService,
        protected ShippingService $shippingService
    ) {}

    /**
     * Get user's cart
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCartForUser($request->user());
        $deliveryType = $request->query('delivery_type');

        $shippingFee = null;
        $totalWithShipping = null;

        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingFee = $this->shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $totalWithShipping = (float) $cart->total + $shippingFee;
        }

        $resource = (new CartResource($cart))->withShipping($shippingFee, $totalWithShipping);

        return $this->successResponse($resource, 'Cart retrieved successfully');
    }

    /**
     * Add item to cart
     */
    public function addItem(AddToCartRequest $request): JsonResponse
    {
        $result = $this->cartService->addItem(
            $request->user(),
            (int) $request->validated('meal_id'),
            (int) $request->validated('quantity')
        );

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['code']);
        }

        return $this->successResponse(
            new CartResource($result['cart']),
            'Item added to cart successfully'
        );
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {
        $result = $this->cartService->updateItem(
            $request->user(),
            $itemId,
            (int) $request->validated('quantity')
        );

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['code']);
        }

        return $this->successResponse(
            new CartResource($result['cart']),
            'Cart item updated successfully'
        );
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        $cart = $this->cartService->removeItem($request->user(), $itemId);

        return $this->successResponse(
            new CartResource($cart),
            'Item removed from cart successfully'
        );
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->clearCart($request->user());

        return $this->successResponse(
            new CartResource($cart),
            'Cart cleared successfully'
        );
    }
}