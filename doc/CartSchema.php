<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Cart',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 7),
        new OA\Property(property: 'status', type: 'string', enum: ['empty', 'not empty'], example: 'not empty'),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'meal', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 5),
                    new OA\Property(property: 'title', type: 'string', example: 'Fresh Bananas'),
                    new OA\Property(property: 'slug', type: 'string', example: 'fresh-bananas'),
                    new OA\Property(property: 'image_url', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 3.5),
                    new OA\Property(property: 'rating', type: 'number', format: 'float', example: 4.5),
                    new OA\Property(property: 'size', type: 'string', nullable: true, example: '1kg'),
                    new OA\Property(property: 'brand', type: 'string', nullable: true, example: 'Fresh Farms'),
                    new OA\Property(property: 'stock_quantity', type: 'integer', example: 50),
                    new OA\Property(property: 'is_available', type: 'boolean', example: true),
                    new OA\Property(property: 'in_stock', type: 'boolean', example: true),
                    new OA\Property(property: 'category', type: 'object', nullable: true, properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 2),
                        new OA\Property(property: 'name', type: 'string', example: 'Fruits'),
                    ]),
                    new OA\Property(property: 'subcategory', type: 'object', nullable: true, properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 4),
                        new OA\Property(property: 'name', type: 'string', example: 'Tropical'),
                    ]),
                ]),
                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 3.5),
                new OA\Property(property: 'discount_amount', type: 'number', format: 'float', example: 0),
                new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 7),
            ],
            type: 'object'
        )),
        new OA\Property(property: 'item_count', type: 'integer', example: 3),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 21),
        new OA\Property(property: 'tax', type: 'number', format: 'float', example: 2.1),
        new OA\Property(property: 'discount', type: 'number', format: 'float', example: 0),
        new OA\Property(property: 'total', type: 'number', format: 'float', example: 23.1),
        new OA\Property(property: 'is_empty', type: 'boolean', example: false),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'shipping_fee', type: 'number', format: 'float', nullable: true, description: 'Only present when a valid delivery_type query param is provided', example: 10),
        new OA\Property(property: 'total_with_shipping', type: 'number', format: 'float', nullable: true, description: 'Only present when a valid delivery_type query param is provided', example: 33.1),
    ]
)]
class CartSchema {}
