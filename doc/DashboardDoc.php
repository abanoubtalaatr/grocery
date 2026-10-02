<?php

namespace Doc;

use OpenApi\Attributes as OA;

class DashboardDoc
{
    #[OA\Get(
        path: '/api/dashboard',
        summary: 'Get home dashboard statistics and insights for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dashboard data retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Dashboard data retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'overview', type: 'object', properties: [
                                new OA\Property(property: 'tracking_order', type: 'object', nullable: true, properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'order_number', type: 'string', example: 'ORD-00000101'),
                                    new OA\Property(property: 'status', type: 'string', example: 'processing'),
                                    new OA\Property(property: 'status_description', type: 'string'),
                                    new OA\Property(property: 'status_position', type: 'integer', example: 2),
                                ]),
                                new OA\Property(property: 'loyalty_points', type: 'integer', example: 120),
                                new OA\Property(property: 'store_credits', type: 'number', format: 'float', example: 0),
                                new OA\Property(property: 'current_cart', type: 'object', properties: [
                                    new OA\Property(property: 'items_count', type: 'integer', example: 3),
                                    new OA\Property(property: 'total', type: 'number', format: 'float', example: 23.1),
                                    new OA\Property(property: 'last_updated', type: 'string', format: 'date-time', nullable: true),
                                ]),
                                new OA\Property(property: 'upcoming_delivery', type: 'object', nullable: true, properties: [
                                    new OA\Property(property: 'order_id', type: 'integer', example: 101),
                                    new OA\Property(property: 'order_number', type: 'string', example: 'ORD-00000101'),
                                    new OA\Property(property: 'date', type: 'string', example: '2026-09-25'),
                                    new OA\Property(property: 'time', type: 'string', example: '14:30'),
                                    new OA\Property(property: 'estimated_delivery_time', type: 'string', format: 'date-time', nullable: true),
                                ]),
                            ]),
                            new OA\Property(property: 'shopping_insights', type: 'object', properties: [
                                new OA\Property(property: 'monthly_spend', type: 'number', format: 'float', example: 350.5),
                                new OA\Property(property: 'orders_this_month', type: 'object', properties: [
                                    new OA\Property(property: 'count', type: 'integer', example: 5),
                                    new OA\Property(property: 'average_days_between_orders', type: 'number', format: 'float', example: 4.5),
                                ]),
                                new OA\Property(property: 'total_savings', type: 'number', format: 'float', example: 42.75),
                                new OA\Property(property: 'average_order_value', type: 'number', format: 'float', example: 70.1),
                            ]),
                            new OA\Property(property: 'category_distribution', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'category_id', type: 'integer', example: 2),
                                    new OA\Property(property: 'category_name', type: 'string', example: 'Fruits'),
                                    new OA\Property(property: 'total_quantity', type: 'integer', example: 15),
                                    new OA\Property(property: 'percentage', type: 'number', format: 'float', example: 35.5),
                                ],
                                type: 'object'
                            )),
                            new OA\Property(property: 'recent_orders', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 101),
                                    new OA\Property(property: 'order_number', type: 'string', example: 'ORD-00000101'),
                                    new OA\Property(property: 'status', type: 'string', example: 'placed'),
                                    new OA\Property(property: 'status_description', type: 'string'),
                                    new OA\Property(property: 'total', type: 'number', format: 'float', example: 65),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'items_count', type: 'integer', example: 3),
                                ],
                                type: 'object'
                            )),
                            new OA\Property(property: 'top_purchases', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'meal_id', type: 'integer', nullable: true, example: 5),
                                    new OA\Property(property: 'title', type: 'string', nullable: true, example: 'Fresh Bananas'),
                                    new OA\Property(property: 'image_url', type: 'string', nullable: true),
                                    new OA\Property(property: 'category', type: 'object', nullable: true, properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 2),
                                        new OA\Property(property: 'name', type: 'string', example: 'Fruits'),
                                    ]),
                                    new OA\Property(property: 'total_quantity_purchased', type: 'integer', example: 20),
                                    new OA\Property(property: 'total_spent', type: 'number', format: 'float', example: 70),
                                ],
                                type: 'object'
                            )),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve dashboard data', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}
}
