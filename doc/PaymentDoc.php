<?php

namespace Doc;

use OpenApi\Attributes as OA;

class PaymentDoc
{
    #[OA\Post(
        path: '/api/payments/stripe/checkout-session',
        summary: 'Create a Stripe Checkout session for an existing order',
        description: 'Creates a Stripe-hosted checkout session for an order previously created with payment_method=stripe_checkout. The client should open checkout_url in a WebView/browser.',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['order_id', 'amount'],
                properties: [
                    new OA\Property(property: 'order_id', type: 'integer', example: 101),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', minimum: 0.01, example: 65),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Checkout session created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Checkout session created. Open checkout_url in your WebView.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'checkout_url', type: 'string', example: 'https://checkout.stripe.com/c/pay/cs_test_...'),
                            new OA\Property(property: 'session_id', type: 'string', example: 'cs_test_1AbCdEfGhIjKlMn'),
                            new OA\Property(property: 'order_id', type: 'integer', example: 101),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 422, description: 'Validation error or invalid order state', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 502, description: 'Unable to start checkout with Stripe', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function checkoutSession() {}

    #[OA\Get(
        path: '/api/payments/stripe/verify-session/{session_id}',
        summary: 'Verify a completed Stripe Checkout session and finalize the order',
        description: 'Retrieves the Stripe Checkout session; if payment_status is paid, marks the matching order (awaiting_payment) as placed.',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        parameters: [
            new OA\Parameter(name: 'session_id', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cs_test_1AbCdEfGhIjKlMn'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payment verified, order is placed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payment verified. Order is placed.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'order_id', type: 'integer', example: 101),
                            new OA\Property(property: 'order_number', type: 'string', example: 'ORD-00000101'),
                            new OA\Property(property: 'status', type: 'string', example: 'placed'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(
                response: 402,
                description: 'Payment has not been completed yet',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Payment has not been completed.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'payment_status', type: 'string', example: 'unpaid'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Order not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 502, description: 'Unable to verify payment session with Stripe', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function verifySession() {}

    #[OA\Get(
        path: '/api/payments/history',
        summary: "Get the authenticated user's payment history (non-cancelled orders)",
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payment history retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Payment history retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 101),
                                new OA\Property(property: 'order_number', type: 'string', example: 'ORD-00000101'),
                                new OA\Property(property: 'payment_method', type: 'string', example: 'stripe_checkout'),
                                new OA\Property(property: 'stripe_payment_intent_id', type: 'string', nullable: true),
                                new OA\Property(property: 'amount', type: 'number', format: 'float', example: 65),
                                new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 50),
                                new OA\Property(property: 'tax', type: 'number', format: 'float', example: 5),
                                new OA\Property(property: 'discount', type: 'number', format: 'float', example: 0),
                                new OA\Property(property: 'status', type: 'string', example: 'placed'),
                                new OA\Property(property: 'status_description', type: 'string', example: 'Your order has been placed'),
                                new OA\Property(property: 'payment_date', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'items_count', type: 'integer', example: 3),
                            ],
                            type: 'object'
                        )),
                        new OA\Property(property: 'total_count', type: 'integer', example: 12),
                        new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 780),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve payment history', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function paymentHistory() {}

    #[OA\Get(
        path: '/api/payments/receipt/{order}',
        summary: 'Get the receipt for a specific order',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        parameters: [
            new OA\Parameter(name: 'order', in: 'path', required: true, description: 'Order id', schema: new OA\Schema(type: 'integer'), example: 101),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Receipt retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Receipt retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'receipt_number', type: 'string', example: 'ORD-00000101'),
                            new OA\Property(property: 'invoice_number', type: 'string', example: 'INV-00000101'),
                            new OA\Property(property: 'type', type: 'string', example: 'receipt'),
                            new OA\Property(property: 'date', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'status', type: 'string', example: 'placed'),
                            new OA\Property(property: 'status_description', type: 'string'),
                            new OA\Property(property: 'customer', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                new OA\Property(property: 'email', type: 'string', nullable: true),
                                new OA\Property(property: 'phone', type: 'string', nullable: true),
                            ]),
                            new OA\Property(property: 'delivery_address', type: 'object', nullable: true, properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 3),
                                new OA\Property(property: 'full_address', type: 'string', example: '12 Nile St., Cairo, Egypt'),
                            ]),
                            new OA\Property(property: 'payment', type: 'object', properties: [
                                new OA\Property(property: 'method', type: 'string', example: 'stripe_checkout'),
                                new OA\Property(property: 'stripe_payment_intent_id', type: 'string', nullable: true),
                                new OA\Property(property: 'method_display', type: 'string', example: 'Card (Stripe Checkout)'),
                            ]),
                            new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                    new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 3.5),
                                    new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 7),
                                ],
                                type: 'object'
                            )),
                            new OA\Property(property: 'pricing', type: 'object', properties: [
                                new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 50),
                                new OA\Property(property: 'tax', type: 'number', format: 'float', example: 5),
                                new OA\Property(property: 'tax_rate', type: 'number', format: 'float', example: 10),
                                new OA\Property(property: 'discount', type: 'number', format: 'float', example: 0),
                                new OA\Property(property: 'total', type: 'number', format: 'float', example: 65),
                            ]),
                            new OA\Property(property: 'delivery', type: 'object', properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'delivery'),
                                new OA\Property(property: 'estimated_delivery_time', type: 'string', format: 'date-time', nullable: true),
                            ]),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found (or belongs to another user)', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to retrieve receipt', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function receipt() {}

    #[OA\Get(
        path: '/api/payments/invoice/{order}',
        summary: 'Get the invoice for a specific order (alias for the receipt endpoint)',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        parameters: [
            new OA\Parameter(name: 'order', in: 'path', required: true, description: 'Order id', schema: new OA\Schema(type: 'integer'), example: 101),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Invoice retrieved successfully (same shape as /api/payments/receipt/{order})'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Order not found (or belongs to another user)', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 500, description: 'Failed to retrieve receipt', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function invoice() {}
}
