<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SmartList',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Weekly Groceries'),
        new OA\Property(property: 'category', type: 'string', nullable: true, example: 'Household'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Recurring weekly essentials'),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'https://example.com/storage/images/smart-lists/1700000000.jpg'),
        new OA\Property(property: 'notify_on_price_drop', type: 'boolean', example: true),
        new OA\Property(property: 'notify_on_offers', type: 'boolean', example: true),
        new OA\Property(property: 'meals', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'meals_count', type: 'integer', nullable: true, example: 4),
    ]
)]
class SmartListSchema {}
