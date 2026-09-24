<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Order',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 101),
        new OA\Property(property: 'order_number', type: 'string', example: 'ORD-00000101'),
        new OA\Property(property: 'payment_method', type: 'string', enum: ['card', 'cash_on_delivery', 'stripe_checkout'], example: 'stripe_checkout'),
        new OA\Property(property: 'stripe_payment_intent_id', type: 'string', nullable: true, example: 'pi_1AbCdEfGhIjKlMn'),
        new OA\Property(property: 'delivery_type', type: 'string', enum: ['delivery', 'pickup'], example: 'delivery'),
        new OA\Property(property: 'status', type: 'string', example: 'placed', enum: ['awaiting_payment', 'placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled']),
        new OA\Property(property: 'status_position', type: 'integer', example: 1),
        new OA\Property(property: 'status_description', type: 'string', example: 'Your order has been placed'),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'meal', type: 'object', properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 5),
                    new OA\Property(property: 'title', type: 'string', example: 'Fresh Bananas'),
                    new OA\Property(property: 'slug', type: 'string', example: 'fresh-bananas'),
                    new OA\Property(property: 'image_url', type: 'string', nullable: true),
                ]),
                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 3.5),
                new OA\Property(property: 'discount_amount', type: 'number', format: 'float', example: 0),
                new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 7),
            ],
            type: 'object'
        )),
        new OA\Property(property: 'address', type: 'object', nullable: true, properties: [
            new OA\Property(property: 'id', type: 'integer', example: 3),
            new OA\Property(property: 'label', type: 'string', example: 'Home'),
            new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Ali'),
            new OA\Property(property: 'phone', type: 'string', example: '+201234567890'),
            new OA\Property(property: 'street_address', type: 'string', example: '12 Nile St.'),
            new OA\Property(property: 'city', type: 'string', example: 'Cairo'),
            new OA\Property(property: 'country', type: 'string', example: 'Egypt'),
            new OA\Property(property: 'full_address', type: 'string', example: '12 Nile St., Cairo, Egypt'),
        ]),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 50),
        new OA\Property(property: 'tax', type: 'number', format: 'float', example: 5),
        new OA\Property(property: 'discount', type: 'number', format: 'float', example: 0),
        new OA\Property(property: 'shipping_fee', type: 'number', format: 'float', example: 10),
        new OA\Property(property: 'total', type: 'number', format: 'float', example: 65),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'placed_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'estimated_delivery_time', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'schedule_delivery', type: 'string', nullable: true),
        new OA\Property(property: 'delivery_speed', type: 'string', nullable: true),
    ]
)]
class OrderSchema {}
