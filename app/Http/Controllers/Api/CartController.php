<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\GetCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Actions\Cart\AddToCartAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\ClearCartAction;
use App\Services\CartService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Get user's cart
     */
    public function index(GetCartRequest $request): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        $deliveryType = $request->query('delivery_type');

        $cartData = $this->cartService->getCartDetails($cart, $deliveryType);

        return $this->success(new CartResource($cartData), 'Cart retrieved successfully');
    }

    /**
     * Add item to cart
     */
    public function addItem(AddToCartRequest $request, AddToCartAction $action): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();

        $action->handle($cart, $request->validated());

        return $this->success(
            new CartResource($this->cartService->getCartDetails($cart)),
            'Item added to cart successfully'
        );
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId, UpdateCartItemAction $action): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);

        $action->handle($cartItem, $request->validated('quantity'));

        return $this->success(
            new CartResource($this->cartService->getCartDetails($cartItem->cart)),
            'Cart item updated successfully'
        );
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId, RemoveCartItemAction $action): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);

        $action->handle($cartItem);

        return $this->success(
            new CartResource($this->cartService->getCartDetails($cart)),
            'Item removed from cart successfully'
        );
    }

    /**
     * Clear cart
     */
    public function clear(ClearCartAction $action): JsonResponse
    {
        $cart = auth()->user()->getOrCreateCart();

        $action->handle($cart);

        return $this->success(
            new CartResource($this->cartService->getCartDetails($cart)),
            'Cart cleared successfully'
        );
    }
}