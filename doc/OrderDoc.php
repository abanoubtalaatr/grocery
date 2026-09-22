<?php

namespace Doc;

use OpenApi\Attributes as OA;

class OrderDoc
{
    #[OA\Post(
        path: '/api/orders',
        summary: 'Create a new order from the current cart',
        description: 'Uses the items currently in the authenticated user\'s active cart. The cart is cleared on success. For payment_method=stripe_checkout the order is created with status awaiting_payment and should be finalized via POST /api/payments/stripe/checkout-session.',
        security: [['bearerAuth' => []]],
        tags: ['Orders'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['payment_method', 'delivery_type', 'amount'],
                properties: [
                    new OA\Property(property: 'payment_method', type: 'string', enum: ['card', 'cash_on_delivery', 'stripe_checkout'], example: 'stripe_checkout'),
                    new OA\Property(property: 'payment_method_id', type: 'string', nullable: true, description: 'Required when payment_method=card', example: 'pm_1AbCdEfGhIjKlMn'),
                    new OA\Property(property: 'delivery_type', type: 'string', enum: ['delivery', 'pickup'], example: 'delivery'),
                    new OA\Property(property: 'address_id', type: 'integer', nullable: true, description: 'Required when delivery_type=delivery; must belong to the authenticated user', example: 3),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', minimum: 0, example: 65),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, maxLength: 1000),
                    new OA\Property(property: 'special_note_id', type: 'integer', nullable: true, example: 2),
                    new OA\Property(property: 'schedule_delivery', type: 'string', nullable: true, maxLength: 255),
                    new OA\Property(property: 'delivery_speed', type: 'string', nullable: true, maxLength: 255),
                    new OA\Property(property: 'estimated_delivery_time', type: 'integer', nullable: true, minimum: 0),
                    new OA\Property(property: 'contacts_information', type: 'object', nullable: true, properties: [
                        new OA\Property(property: 'first_name', type: 'string', nullable: true, maxLength: 255),
                        new OA\Property(property: 'last_name', type: 'string', nullable: true, maxLength: 255),
                        new OA\Property(property: 'email', type: 'string', nullable: true, maxLength: 255),
                        new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 20),
                    ]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Order created successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Order'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Cart is empty or items are invalid/out of stock', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to create order', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/orders',
        summary: "List the authenticated user's orders",
        security: [['bearerAuth' => []]],
        tags: ['Orders'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Orders retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Orders retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Order')),
                        new OA\Property(property: 'total_count', type: 'integer', example: 12),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve orders', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/api/orders/track',
        summary: "Track the authenticated user's most recent active order",
        description: 'Returns the latest order that is not cancelled or delivered, along with a step-by-step tracking timeline.',
        security: [['bearerAuth' => []]],
        tags: ['Orders'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order tracking retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Order tracking retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'order', ref: '#/components/schemas/Order'),
                            new OA\Property(property: 'awaiting_payment', type: 'boolean', example: false),
                            new OA\Property(property: 'tracking', type: 'object', nullable: true, properties: [
                                new OA\Property(property: 'position', type: 'integer', example: 2),
                                new OA\Property(property: 'status', type: 'string', example: 'processing'),
                                new OA\Property(property: 'status_description', type: 'string', example: 'Your order is being processed'),
                                new OA\Property(property: 'positions', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'position', type: 'integer', example: 1),
                                        new OA\Property(property: 'status', type: 'string', example: 'placed'),
                                        new OA\Property(property: 'label', type: 'string', example: 'Order Placed'),
                                        new OA\Property(property: 'description', type: 'string', example: 'Your order has been placed'),
                                        new OA\Property(property: 'completed', type: 'boolean', example: true),
                                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', nullable: true),
                                    ],
                                    type: 'object'
                                )),
                            ]),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'No active order found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to track order', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function track() {}

    #[OA\Get(
        path: '/api/orders/{id}',
        summary: 'Get a single order by id',
        security: [['bearerAuth' => []]],
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 101),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Order retrieved successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Order'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function show() {}
}
