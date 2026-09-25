<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Meal',
    description: 'Representative shape of a meal/product as returned by the browsing list endpoints (today, hot, etc). Some endpoints include a subset or a few extra fields.',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Fresh Orange Juice 1L'),
        new OA\Property(property: 'slug', type: 'string', example: 'fresh-orange-juice-1l'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: '100% natural fresh orange juice.'),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'https://example.com/storage/meals/orange-juice.jpg'),
        new OA\Property(property: 'offer_title', type: 'string', nullable: true, example: 'Buy 1 Get 1 Free'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 49.99),
        new OA\Property(property: 'discount_price', type: 'number', format: 'float', nullable: true, example: 39.99),
        new OA\Property(property: 'final_price', type: 'number', format: 'float', example: 39.99),
        new OA\Property(property: 'has_offer', type: 'boolean', example: true),
        new OA\Property(property: 'rating', type: 'number', format: 'float', example: 4.5),
        new OA\Property(property: 'rating_count', type: 'integer', example: 120),
        new OA\Property(property: 'brand', type: 'string', nullable: true, example: 'Juhayna'),
        new OA\Property(property: 'stock_quantity', type: 'integer', example: 50),
        new OA\Property(property: 'in_stock', type: 'boolean', example: true),
        new OA\Property(property: 'category', type: 'object', properties: [
            new OA\Property(property: 'id', type: 'integer', example: 3),
            new OA\Property(property: 'name', type: 'string', example: 'Beverages'),
        ]),
        new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string'), example: ['No added sugar', 'Rich in vitamin C']),
        new OA\Property(property: 'available_date', type: 'string', format: 'date', nullable: true, example: '2026-09-24'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-09-24T10:00:00.000000Z'),
    ]
)]
class MealSchema {}
