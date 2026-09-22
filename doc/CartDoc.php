<?php

namespace Doc;

use OpenApi\Attributes as OA;

class CartDoc
{
    #[OA\Get(
        path: '/api/cart',
        summary: "Get the authenticated user's cart",
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'delivery_type',
                in: 'query',
                required: false,
                description: 'When provided (delivery or pickup), shipping_fee and total_with_shipping are included in the response',
                schema: new OA\Schema(type: 'string', enum: ['delivery', 'pickup'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Cart retrieved successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve cart', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/cart/items',
        summary: 'Add an item to the cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['meal_id', 'quantity'],
                properties: [
                    new OA\Property(property: 'meal_id', type: 'integer', example: 5),
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item added to cart successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Item added to cart successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Meal unavailable, out of stock, or over quantity limit', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to add item to cart', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function addItem() {}

    #[OA\Put(
        path: '/api/cart/items/{itemId}',
        summary: 'Update the quantity of a cart item',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'itemId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 12),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 3),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart item updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Cart item updated successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Insufficient stock', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Cart item not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to update cart item', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function updateItem() {}

    #[OA\Delete(
        path: '/api/cart/items/{itemId}',
        summary: 'Remove an item from the cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'itemId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 12),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item removed from cart successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Item removed from cart successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Cart item not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to remove item from cart', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function removeItem() {}

    #[OA\Delete(
        path: '/api/cart/clear',
        summary: "Clear all items from the user's cart",
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart cleared successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Cart cleared successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Cart'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to clear cart', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function clear() {}
}
