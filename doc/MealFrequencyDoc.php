<?php

namespace Doc;

use OpenApi\Attributes as OA;

class MealFrequencyDoc
{
    #[OA\Get(
        path: '/api/frequency',
        summary: "Get the authenticated user's frequently ordered meals",
        description: 'Uses the user\'s order history to surface meals ordered regularly, grouped by a frequency window.',
        security: [['bearerAuth' => []]],
        tags: ['Meals'],
        parameters: [
            new OA\Parameter(
                name: 'frequency_type',
                in: 'query',
                required: false,
                description: 'Defaults to weekly if omitted or invalid',
                schema: new OA\Schema(type: 'string', enum: ['weekly', 'monthly'], default: 'weekly')
            ),
            new OA\Parameter(
                name: 'subcategory_id',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
                example: 4
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Frequency meals retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Frequency meals retrieved successfully'),
                        new OA\Property(property: 'frequency_type', type: 'string', example: 'weekly'),
                        new OA\Property(property: 'subcategory_id', type: 'integer', nullable: true, description: 'Only present when the subcategory_id query param was provided', example: 4),
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
                                new OA\Property(property: 'category', type: 'object', nullable: true, properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 2),
                                    new OA\Property(property: 'name', type: 'string', example: 'Fruits'),
                                ]),
                                new OA\Property(property: 'subcategory', type: 'object', nullable: true, properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 4),
                                    new OA\Property(property: 'name', type: 'string', example: 'Tropical'),
                                ]),
                                new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
                                new OA\Property(property: 'available_date', type: 'string', format: 'date', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'order_count', type: 'integer', example: 6),
                            ],
                            type: 'object'
                        )),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Authentication required to view frequency meals', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to retrieve frequency meals', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function frequency() {}
}
