<?php

namespace Doc;

use OpenApi\Attributes as OA;

class SmartListDoc
{
    #[OA\Get(
        path: '/api/smart-lists',
        summary: 'Get all smart lists for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['SmartLists'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Smart lists retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Smart lists retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SmartList')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/smart-lists',
        summary: 'Create a new smart list',
        security: [['bearerAuth' => []]],
        tags: ['SmartLists'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Weekly Groceries'),
                        new OA\Property(property: 'category', type: 'string', nullable: true, example: 'Household'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Recurring weekly essentials'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true, description: 'Image file, max 2MB'),
                        new OA\Property(property: 'notify_on_price_drop', type: 'boolean', nullable: true, example: true),
                        new OA\Property(property: 'notify_on_offers', type: 'boolean', nullable: true, example: true),
                        new OA\Property(property: 'meal_ids', type: 'array', items: new OA\Items(type: 'integer'), nullable: true, example: [1, 2, 3]),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wish list created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Wish list created successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SmartList'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/smart-lists/{smart_list}',
        summary: 'Get a single smart list',
        security: [['bearerAuth' => []]],
        tags: ['SmartLists'],
        parameters: [
            new OA\Parameter(name: 'smart_list', in: 'path', required: true, description: 'Smart list id', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Smart list retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Smart list retrieved successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SmartList'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Smart list not found'),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/smart-lists/{smart_list}',
        summary: 'Update a smart list',
        security: [['bearerAuth' => []]],
        tags: ['SmartLists'],
        parameters: [
            new OA\Parameter(name: 'smart_list', in: 'path', required: true, description: 'Smart list id', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Weekly Groceries'),
                        new OA\Property(property: 'category', type: 'string', nullable: true, example: 'Household'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Recurring weekly essentials'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true, description: 'Image file, max 2MB'),
                        new OA\Property(property: 'notify_on_price_drop', type: 'boolean', nullable: true, example: true),
                        new OA\Property(property: 'notify_on_offers', type: 'boolean', nullable: true, example: true),
                        new OA\Property(property: 'meal_ids', type: 'array', items: new OA\Items(type: 'integer'), nullable: true, example: [1, 2, 3]),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wish list updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Wish list updated successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SmartList'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Smart list not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/api/smart-lists/{smart_list}',
        summary: 'Delete a smart list',
        security: [['bearerAuth' => []]],
        tags: ['SmartLists'],
        parameters: [
            new OA\Parameter(name: 'smart_list', in: 'path', required: true, description: 'Smart list id', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Wish list deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Smart list not found'),
        ]
    )]
    public function destroy() {}

    #[OA\Post(
        path: '/api/smart-lists/{id}/meals',
        summary: 'Add a meal to a smart list',
        security: [['bearerAuth' => []]],
        tags: ['SmartLists'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Smart list id', schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['meal_id'],
                properties: [
                    new OA\Property(property: 'meal_id', type: 'integer', example: 42, description: 'Must exist in meals table'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item added to wish list successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Item added to wish list successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SmartList'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Smart list not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function addMeal() {}

    #[OA\Delete(
        path: '/api/smart-lists/{id}/meals/{mealId}',
        summary: 'Remove a meal from a smart list',
        security: [['bearerAuth' => []]],
        tags: ['SmartLists'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Smart list id', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'mealId', in: 'path', required: true, description: 'Meal id', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Item removed from wish list successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Item removed from wish list successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/SmartList'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Smart list not found'),
        ]
    )]
    public function removeMeal() {}
}
