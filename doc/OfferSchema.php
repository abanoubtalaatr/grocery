<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Offer',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Summer Sale'),
        new OA\Property(property: 'code', type: 'string', example: 'SUMMER20'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', example: 'percentage', description: 'percentage|fixed|buy_one_get_one|free_shipping'),
        new OA\Property(property: 'type_label', type: 'string', example: 'Percentage Discount'),
        new OA\Property(property: 'discount_value', type: 'number', format: 'float', example: 20),
        new OA\Property(property: 'minimum_purchase', type: 'number', format: 'float', nullable: true, example: 100),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-06-01'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2026-08-31'),
        new OA\Property(property: 'usage_limit', type: 'integer', nullable: true, example: 100),
        new OA\Property(property: 'used_count', type: 'integer', example: 12),
        new OA\Property(property: 'remaining_uses', type: 'integer', nullable: true, example: 88),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'is_featured', type: 'boolean', example: false),
        new OA\Property(property: 'is_valid', type: 'boolean', example: true),
        new OA\Property(property: 'days_remaining', type: 'integer', example: 15),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class OfferSchema {}
