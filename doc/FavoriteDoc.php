<?php

namespace Doc;

use OpenApi\Attributes as OA;

class FavoriteDoc
{
    #[OA\Get(
        path: '/api/favorites',
        summary: "Get all of the authenticated user's favorite meals",
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Favorites retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Favorites retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 5),
                                new OA\Property(property: 'title', type: 'string', example: 'Fresh Bananas'),
                                new OA\Property(property: 'slug', type: 'string', example: 'fresh-bananas'),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'image_url', type: 'string', nullable: true),
                                new OA\Property(property: 'offer_title', type: 'string', nullable: true),
                                new OA\Property(property: 'price', type: 'number', format: 'float', example: 3.5),
                                new OA\Property(property: 'has_offer', type: 'boolean', example: false),
                                new OA\Property(property: 'rating', type: 'number', format: 'float', example: 4.5),
                                new OA\Property(property: 'rating_count', type: 'integer', example: 12),
                                new OA\Property(property: 'size', type: 'string', nullable: true),
                                new OA\Property(property: 'brand', type: 'string', nullable: true),
                                new OA\Property(property: 'stock_quantity', type: 'integer', example: 50),
                                new OA\Property(property: 'in_stock', type: 'boolean', example: true),
                                new OA\Property(property: 'is_available', type: 'boolean', example: true),
                                new OA\Property(property: 'is_featured', type: 'boolean', example: false),
                                new OA\Property(property: 'category', type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 2),
                                    new OA\Property(property: 'name', type: 'string', example: 'Fruits'),
                                    new OA\Property(property: 'slug', type: 'string', example: 'fruits'),
                                ]),
                                new OA\Property(property: 'subcategory', type: 'object', nullable: true, properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 4),
                                    new OA\Property(property: 'name', type: 'string', example: 'Tropical'),
                                    new OA\Property(property: 'slug', type: 'string', example: 'tropical'),
                                ]),
                                new OA\Property(property: 'is_favorited', type: 'boolean', example: true),
                                new OA\Property(property: 'favorited_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        )),
                        new OA\Property(property: 'total_count', type: 'integer', example: 3),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve favorites', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/favorites/{mealId}/toggle',
        summary: 'Toggle favorite status for a meal (adds if not favorited, removes if favorited)',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        parameters: [
            new OA\Parameter(name: 'mealId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 5),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Favorite status toggled',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Added to favorites'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'meal_id', type: 'integer', example: 5),
                            new OA\Property(property: 'is_favorited', type: 'boolean', example: true),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Meal not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to toggle favorite', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function toggle() {}

    #[OA\Get(
        path: '/api/favorites/{mealId}/check',
        summary: 'Check whether a meal is in the favorites list',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        parameters: [
            new OA\Parameter(name: 'mealId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 5),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Favorite status',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'meal_id', type: 'integer', example: 5),
                            new OA\Property(property: 'is_favorited', type: 'boolean', example: true),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Meal not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to check favorite status', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function check() {}

    #[OA\Delete(
        path: '/api/favorites/{mealId}',
        summary: 'Remove a meal from favorites',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        parameters: [
            new OA\Parameter(name: 'mealId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 5),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Removed from favorites',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Removed from favorites'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'meal_id', type: 'integer', example: 5),
                            new OA\Property(property: 'is_favorited', type: 'boolean', example: false),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Meal not found, or meal was not in favorites', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to remove from favorites', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function remove() {}
}
