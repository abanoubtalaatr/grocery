<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart(
            $request->user()
        );

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        $shipping = $this->cartService->calculateShipping(
            $cart,
            $request->query('delivery_type')
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'data' => $this->formatCart(
                $cart,
                $shipping['shipping_fee'],
                $shipping['total_with_shipping']
            ),
        ]);
    }

    public function addItem(
        AddCartItemRequest $request
    ): JsonResponse {
        $cart = $this->cartService->addItem(
            $request->user(),
            (int) $request->validated('meal_id'),
            (int) $request->validated('quantity')
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => $this->formatCart($cart),
        ]);
    }

    public function updateItem(
        UpdateCartItemRequest $request,
        CartItem $item
    ): JsonResponse {
        $cart = $this->cartService->updateItem(
            $request->user(),
            $item,
            (int) $request->validated('quantity')
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => $this->formatCart($cart),
        ]);
    }

    public function removeItem(
        Request $request,
        CartItem $item
    ): JsonResponse {
        $cart = $this->cartService->removeItem(
            $request->user(),
            $item
        );

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => $this->formatCart($cart),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->clear(
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => $this->formatCart($cart),
        ]);
    }

    private function formatCart(
        Cart $cart,
        ?float $shippingFee = null,
        ?float $totalWithShipping = null
    ): array {
        $data = [
            'id' => $cart->id,

            'status' => $cart->isEmpty()
                ? 'empty'
                : 'not empty',

            'items' => $cart->items->map(
                function ($item) {
                    return [
                        'id' => $item->id,

                        'meal' => [
                            'id' => $item->meal->id,
                            'title' => $item->meal->title,
                            'slug' => $item->meal->slug,
                            'image_url' => $item->meal->image_url,

                            ...$item->meal->getApiPriceAttributes(),

                            'rating' => (float) $item->meal->rating,
                            'size' => $item->meal->size,
                            'brand' => $item->meal->brand,
                            'stock_quantity' => $item->meal->stock_quantity,
                            'is_available' => $item->meal->is_available,
                            'in_stock' => $item->meal->isInStock(),

                            'category' => $item->meal->category
                                ? [
                                    'id' => $item->meal->category->id,
                                    'name' => $item->meal->category->name,
                                ]
                                : null,

                            'subcategory' => $item->meal->subcategory
                                ? [
                                    'id' => $item->meal->subcategory->id,
                                    'name' => $item->meal->subcategory->name,
                                ]
                                : null,
                        ],

                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'discount_amount' => (float) $item->discount_amount,
                        'subtotal' => (float) $item->subtotal,
                    ];
                }
            ),

            'item_count' => $cart->item_count,
            'subtotal' => (float) $cart->subtotal,
            'tax' => (float) $cart->tax,
            'discount' => (float) $cart->discount,
            'total' => (float) $cart->total,
            'is_empty' => $cart->isEmpty(),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];

        if (
            $shippingFee !== null &&
            $totalWithShipping !== null
        ) {
            $data['shipping_fee'] = (float) $shippingFee;

            $data['total_with_shipping'] =
                (float) $totalWithShipping;
        }

        return $data;
    }
}