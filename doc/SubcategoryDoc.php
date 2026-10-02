<?php

namespace Doc;

use OpenApi\Attributes as OA;

class SubcategoryDoc
{
    #[OA\Get(
        path: '/api/subcategories',
        summary: 'Get all active subcategories (optionally filtered by category), random order',
        tags: ['Subcategories'],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subcategories retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Subcategories retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Juices'),
                                new OA\Property(property: 'slug', type: 'string', example: 'juices'),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'image_url', type: 'string', nullable: true),
                                new OA\Property(property: 'order', type: 'integer'),
                                new OA\Property(property: 'category', type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'slug', type: 'string'),
                                ]),
                                new OA\Property(property: 'meals_count', type: 'integer', example: 8),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ]
                        )),
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Failed to retrieve subcategories', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/api/subcategories/{id}',
        summary: 'Get a single subcategory with up to 10 available meals',
        tags: ['Subcategories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subcategory retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Subcategory retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Juices'),
                            new OA\Property(property: 'slug', type: 'string', example: 'juices'),
                            new OA\Property(property: 'description', type: 'string', nullable: true),
                            new OA\Property(property: 'image_url', type: 'string', nullable: true),
                            new OA\Property(property: 'order', type: 'integer'),
                            new OA\Property(property: 'is_active', type: 'boolean'),
                            new OA\Property(property: 'category', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'slug', type: 'string'),
                            ]),
                            new OA\Property(property: 'meals', type: 'array', items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'title', type: 'string'),
                                    new OA\Property(property: 'slug', type: 'string'),
                                    new OA\Property(property: 'image_url', type: 'string', nullable: true),
                                    new OA\Property(property: 'price', type: 'number', format: 'float'),
                                    new OA\Property(property: 'discount_price', type: 'number', format: 'float', nullable: true),
                                    new OA\Property(property: 'final_price', type: 'number', format: 'float'),
                                    new OA\Property(property: 'rating', type: 'number', format: 'float'),
                                    new OA\Property(property: 'is_featured', type: 'boolean'),
                                    new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string')),
                                ]
                            )),
                            new OA\Property(property: 'meals_count', type: 'integer', example: 8),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Subcategory not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to retrieve subcategory', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function show() {}

    #[OA\Get(
        path: '/api/subcategories/{id}/meals',
        summary: 'Get available meals for a subcategory (paginated, filterable, sortable)',
        tags: ['Subcategories'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'featured', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'in_stock', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'price', 'rating', 'title', 'sold_count', 'newest'], default: 'created_at')),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Clamped between 1 and 50', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Meals retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Meals retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'subcategory', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'slug', type: 'string'),
                            ]),
                            new OA\Property(property: 'meals', type: 'array', items: new OA\Items(ref: '#/components/schemas/Meal')),
                            new OA\Property(property: 'pagination', type: 'object', properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'from', type: 'integer', nullable: true),
                                new OA\Property(property: 'to', type: 'integer', nullable: true),
                            ]),
                        ]),
                        new OA\Property(property: 'empty_message', type: 'string', nullable: true, description: 'Present only when no meals match the applied filters'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Subcategory not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to retrieve meals', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function meals() {}
}
