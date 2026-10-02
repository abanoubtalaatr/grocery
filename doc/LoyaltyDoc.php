<?php

namespace Doc;

use OpenApi\Attributes as OA;

class LoyaltyDoc
{
    #[OA\Get(
        path: '/api/loyalty',
        summary: 'Get loyalty points, membership tier and active coupons for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Loyalty'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Loyalty data retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Loyalty data retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'point_balance', type: 'integer', example: 120),
                            new OA\Property(property: 'rewards_value', type: 'number', format: 'float', example: 1.2),
                            new OA\Property(property: 'rewards_currency', type: 'string', example: 'GBP'),
                            new OA\Property(property: 'point_value', type: 'number', format: 'float', example: 0.01),
                            new OA\Property(property: 'profile_initial', type: 'string', example: 'A'),
                            new OA\Property(property: 'membership', type: 'object', properties: [
                                new OA\Property(property: 'current_tier', type: 'object', properties: [
                                    new OA\Property(property: 'key', type: 'string', example: 'gold'),
                                    new OA\Property(property: 'name', type: 'string', example: 'Gold'),
                                    new OA\Property(property: 'min_points', type: 'integer', example: 100),
                                ]),
                                new OA\Property(property: 'next_tier', type: 'object', nullable: true, properties: [
                                    new OA\Property(property: 'key', type: 'string', example: 'platinum'),
                                    new OA\Property(property: 'name', type: 'string', example: 'Platinum'),
                                    new OA\Property(property: 'min_points', type: 'integer', example: 500),
                                ]),
                                new OA\Property(property: 'progress_label', type: 'string', example: 'Progress to Platinum'),
                                new OA\Property(property: 'points_current', type: 'integer', example: 120),
                                new OA\Property(property: 'points_max', type: 'integer', example: 500),
                                new OA\Property(property: 'points_to_next', type: 'integer', example: 380),
                                new OA\Property(property: 'tiers', type: 'array', items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'key', type: 'string', example: 'silver'),
                                        new OA\Property(property: 'name', type: 'string', example: 'Silver'),
                                        new OA\Property(property: 'min_points', type: 'integer', example: 0),
                                        new OA\Property(property: 'is_current', type: 'boolean', example: false),
                                        new OA\Property(property: 'is_unlocked', type: 'boolean', example: true),
                                    ],
                                    type: 'object'
                                )),
                            ]),
                            new OA\Property(property: 'benefits', type: 'object', properties: [
                                new OA\Property(property: 'tier_key', type: 'string', example: 'gold'),
                                new OA\Property(property: 'tier_name', type: 'string', example: 'Gold'),
                                new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'string'), example: ['Free delivery on orders over $50', 'Early access to offers']),
                            ]),
                            new OA\Property(property: 'coupons', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 9),
                                    new OA\Property(property: 'title', type: 'string', example: '20% Off Fruits'),
                                    new OA\Property(property: 'code', type: 'string', example: 'FRUIT20'),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'type', type: 'string', example: 'percentage'),
                                    new OA\Property(property: 'discount_label', type: 'string', example: '20% off'),
                                    new OA\Property(property: 'minimum_purchase', type: 'number', format: 'float', nullable: true, example: 20),
                                    new OA\Property(property: 'expires_at', type: 'string', format: 'date', nullable: true, example: '2026-12-31'),
                                    new OA\Property(property: 'expires_label', type: 'string', nullable: true, example: 'Dec 31, 2026'),
                                    new OA\Property(property: 'is_featured', type: 'boolean', example: true),
                                ],
                                type: 'object'
                            )),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve loyalty data', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}
}
