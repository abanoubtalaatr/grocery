<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cart\AddCartItemRequest;
use App\Http\Requests\Api\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly AddCartItemAction $addCartItemAction,
        private readonly UpdateCartItemAction $updateCartItemAction,
        private readonly RemoveCartItemAction $removeCartItemAction,
        private readonly ClearCartAction $clearCartAction,
    ) {}

    /**
     * Get user's cart
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->index($request->user(), $request->query('delivery_type'));

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'data' => CartResource::make($cart)->resolve(),
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->addCartItemAction->handle(
            $request->user(),
            (int) $request->validated('meal_id'),
            (int) $request->validated('quantity'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => CartResource::make($cart)->resolve(),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(UpdateCartItemRequest $request, string $itemId): JsonResponse
    {
        $cart = $this->updateCartItemAction->handle(
            $request->user(),
            $itemId,
            (int) $request->validated('quantity'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => CartResource::make($cart)->resolve(),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $itemId): JsonResponse
    {
        $cart = $this->removeCartItemAction->handle($request->user(), $itemId);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => CartResource::make($cart)->resolve(),
        ]);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->clearCartAction->handle($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => CartResource::make($cart)->resolve(),
        ]);
    }
}
