<?php

namespace Doc;

use OpenApi\Attributes as OA;

class MealDoc
{
    #[OA\Get(
        path: '/api/meals/today',
        summary: "Get today's deals (meals with an active discount)",
        tags: ['Meals'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Today's deals retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: "Today's deals retrieved successfully"),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Meal')),
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Failed to retrieve deals', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function today() {}

    #[OA\Get(
        path: '/api/meals/hot',
        summary: 'Get hot / ready-to-eat meals',
        tags: ['Meals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hot meals retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Hot meals retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Meal')),
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Failed to retrieve hot meals', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function hot() {}

    #[OA\Get(
        path: '/api/meals/recommendations',
        summary: 'Get recommended meals (featured with offers mixed with random picks)',
        tags: ['Meals'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Number of recommendations to return', schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Meal recommendations retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Meal recommendations retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Fresh Orange Juice 1L'),
                                new OA\Property(property: 'slug', type: 'string', example: 'fresh-orange-juice-1l'),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'image_url', type: 'string', nullable: true),
                                new OA\Property(property: 'offer_title', type: 'string', nullable: true),
                                new OA\Property(property: 'price', type: 'number', format: 'float', example: 49.99),
                                new OA\Property(property: 'discount_price', type: 'number', format: 'float', nullable: true, example: 39.99),
                                new OA\Property(property: 'final_price', type: 'number', format: 'float', example: 39.99),
                                new OA\Property(property: 'has_offer', type: 'boolean', example: true),
                                new OA\Property(property: 'is_featured', type: 'boolean', example: true),
                                new OA\Property(property: 'category', type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 3),
                                    new OA\Property(property: 'name', type: 'string', example: 'Beverages'),
                                    new OA\Property(property: 'slug', type: 'string', example: 'beverages'),
                                ]),
                                new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'recommendation_reason', type: 'string', example: 'Featured with special offer'),
                            ]
                        )),
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Failed to retrieve recommendations', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function recommendations() {}

    #[OA\Get(
        path: '/api/meals',
        summary: 'Get all meals (with search, filters and sorting)',
        tags: ['Meals'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search in title/description', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'subcategory_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'featured', in: 'query', required: false, description: 'true = featured only, false = non-featured only', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'in_stock', in: 'query', required: false, description: 'true = in stock only, false = out of stock only', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'min_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'max_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'min_rating', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'brand', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'price', 'rating', 'title', 'sold_count', 'newest'], default: 'created_at')),
            new OA\Parameter(name: 'sort_order', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Meals retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Meals retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string'),
                                new OA\Property(property: 'slug', type: 'string'),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'image_url', type: 'string', nullable: true),
                                new OA\Property(property: 'offer_title', type: 'string', nullable: true),
                                new OA\Property(property: 'price', type: 'number', format: 'float'),
                                new OA\Property(property: 'discount_price', type: 'number', format: 'float', nullable: true),
                                new OA\Property(property: 'final_price', type: 'number', format: 'float'),
                                new OA\Property(property: 'has_offer', type: 'boolean'),
                                new OA\Property(property: 'rating', type: 'number', format: 'float'),
                                new OA\Property(property: 'rating_count', type: 'integer'),
                                new OA\Property(property: 'size', type: 'string', nullable: true),
                                new OA\Property(property: 'brand', type: 'string', nullable: true),
                                new OA\Property(property: 'stock_quantity', type: 'integer'),
                                new OA\Property(property: 'in_stock', type: 'boolean'),
                                new OA\Property(property: 'is_featured', type: 'boolean'),
                                new OA\Property(property: 'sold_count', type: 'integer'),
                                new OA\Property(property: 'category', type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'name', type: 'string'),
                                ]),
                                new OA\Property(property: 'subcategory', type: 'object', nullable: true, properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'name', type: 'string'),
                                ]),
                                new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'is_favorited', type: 'boolean', description: 'true if favorited by the authenticated user, false otherwise'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ]
                        )),
                        new OA\Property(property: 'total_count', type: 'integer', example: 24),
                        new OA\Property(property: 'filters_applied', type: 'object'),
                        new OA\Property(property: 'empty_message', type: 'string', nullable: true, description: 'Present only when no meals match the applied filters'),
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Failed to retrieve meals', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/api/meals/{id}',
        summary: 'Get a single meal by id (with category, subcategory and approved reviews)',
        tags: ['Meals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Meal retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Meal retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'title', type: 'string'),
                            new OA\Property(property: 'slug', type: 'string'),
                            new OA\Property(property: 'description', type: 'string', nullable: true),
                            new OA\Property(property: 'image_url', type: 'string', nullable: true),
                            new OA\Property(property: 'offer_title', type: 'string', nullable: true),
                            new OA\Property(property: 'price', type: 'number', format: 'float'),
                            new OA\Property(property: 'discount_price', type: 'number', format: 'float', nullable: true),
                            new OA\Property(property: 'final_price', type: 'number', format: 'float'),
                            new OA\Property(property: 'has_offer', type: 'boolean'),
                            new OA\Property(property: 'rating', type: 'number', format: 'float'),
                            new OA\Property(property: 'rating_count', type: 'integer'),
                            new OA\Property(property: 'size', type: 'string', nullable: true),
                            new OA\Property(property: 'brand', type: 'string', nullable: true),
                            new OA\Property(property: 'includes', type: 'string', nullable: true),
                            new OA\Property(property: 'how_to_use', type: 'string', nullable: true),
                            new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true),
                            new OA\Property(property: 'days_until_expiry', type: 'integer', nullable: true),
                            new OA\Property(property: 'is_expired', type: 'boolean'),
                            new OA\Property(property: 'stock_quantity', type: 'integer'),
                            new OA\Property(property: 'in_stock', type: 'boolean'),
                            new OA\Property(property: 'sold_count', type: 'integer'),
                            new OA\Property(property: 'is_featured', type: 'boolean'),
                            new OA\Property(property: 'is_available', type: 'boolean'),
                            new OA\Property(property: 'available_date', type: 'string', format: 'date', nullable: true),
                            new OA\Property(property: 'category', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'slug', type: 'string'),
                            ]),
                            new OA\Property(property: 'reviews', type: 'array', items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'user', type: 'object', nullable: true, properties: [
                                        new OA\Property(property: 'id', type: 'integer'),
                                        new OA\Property(property: 'name', type: 'string'),
                                    ]),
                                    new OA\Property(property: 'rating', type: 'integer', example: 5),
                                    new OA\Property(property: 'comment', type: 'string', nullable: true),
                                    new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string')),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                                ]
                            )),
                            new OA\Property(property: 'subcategory', type: 'object', nullable: true, properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'slug', type: 'string'),
                            ]),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Meal not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to retrieve meal', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function show() {}

    #[OA\Get(
        path: '/api/new-products',
        summary: 'Get newly added available meals, newest first',
        tags: ['Meals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'New products retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'New products retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            description: 'Raw meal model records (with category loaded); see the Meal schema for the typical field set.',
                            items: new OA\Items(ref: '#/components/schemas/Meal')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Failed to retrieve meals', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function newProducts() {}

    #[OA\Get(
        path: '/api/best-sells',
        summary: 'Get up to 10 best-selling available meals',
        tags: ['Meals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Best sells retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Best sells retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            description: 'Raw meal model records (with category loaded); see the Meal schema for the typical field set.',
                            items: new OA\Items(ref: '#/components/schemas/Meal')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function bestSells() {}

    #[OA\Get(
        path: '/api/sliders',
        summary: 'Get meals for the home page slider',
        tags: ['Meals'],
        responses: [
            new OA\Response(
                response: 200,
                description: "Today's meals retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: "Today's meals retrieved successfully"),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'title', type: 'string'),
                                new OA\Property(property: 'slug', type: 'string'),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'image_url', type: 'string', nullable: true),
                                new OA\Property(property: 'offer_title', type: 'string', nullable: true),
                                new OA\Property(property: 'price', type: 'number', format: 'float'),
                                new OA\Property(property: 'discount_price', type: 'number', format: 'float', nullable: true),
                                new OA\Property(property: 'final_price', type: 'number', format: 'float'),
                                new OA\Property(property: 'has_offer', type: 'boolean'),
                                new OA\Property(property: 'category', type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'name', type: 'string'),
                                ]),
                                new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string')),
                                new OA\Property(property: 'available_date', type: 'string', format: 'date', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ]
                        )),
                    ]
                )
            ),
        ]
    )]
    public function slider() {}

    #[OA\Get(
        path: '/api/brands',
        summary: 'Get the distinct list of meal brands',
        tags: ['Meals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Brands retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Brands retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string'), example: ['Juhayna', 'Domty', 'Vitrac']),
                    ]
                )
            ),
        ]
    )]
    public function brands() {}

    #[OA\Get(
        path: '/api/more-to-explore',
        summary: 'Get available meals to explore, newest first',
        tags: ['Meals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'More to explore retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'More to explore retrieved successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            description: 'Raw meal model records (with category loaded); see the Meal schema for the typical field set.',
                            items: new OA\Items(ref: '#/components/schemas/Meal')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function moreToExplore() {}
}
