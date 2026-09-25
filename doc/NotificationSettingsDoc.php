<?php

namespace Doc;

use OpenApi\Attributes as OA;

class NotificationSettingsDoc
{
    #[OA\Get(
        path: '/api/notification-settings',
        summary: 'Get the authenticated user notification settings, grouped by category',
        security: [['bearerAuth' => []]],
        tags: ['NotificationSettings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification settings retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'order_delivery_updates', type: 'object', properties: [
                                new OA\Property(property: 'category', type: 'string', example: 'Order & Delivery Updates'),
                                new OA\Property(property: 'enabled', type: 'boolean', example: true),
                                new OA\Property(property: 'settings', type: 'object', properties: [
                                    new OA\Property(property: 'order_confirmation', type: 'boolean', example: true),
                                    new OA\Property(property: 'order_shipped', type: 'boolean', example: true),
                                    new OA\Property(property: 'delivery_updates', type: 'boolean', example: true),
                                    new OA\Property(property: 'out_of_stock_alerts', type: 'boolean', example: true),
                                ]),
                            ]),
                            new OA\Property(property: 'deals_promotions', type: 'object', properties: [
                                new OA\Property(property: 'category', type: 'string', example: 'Deals & Promotions'),
                                new OA\Property(property: 'enabled', type: 'boolean', example: true),
                                new OA\Property(property: 'settings', type: 'object', properties: [
                                    new OA\Property(property: 'weekly_discounts', type: 'boolean', example: true),
                                    new OA\Property(property: 'exclusive_member_offers', type: 'boolean', example: true),
                                    new OA\Property(property: 'seasonal_campaigns', type: 'boolean', example: true),
                                ]),
                            ]),
                            new OA\Property(property: 'account_reminders', type: 'object', properties: [
                                new OA\Property(property: 'category', type: 'string', example: 'Account & Reminders'),
                                new OA\Property(property: 'enabled', type: 'boolean', example: true),
                                new OA\Property(property: 'settings', type: 'object', properties: [
                                    new OA\Property(property: 'cart_reminders', type: 'boolean', example: true),
                                    new OA\Property(property: 'payment_billing', type: 'boolean', example: true),
                                ]),
                            ]),
                            new OA\Property(property: 'channels', type: 'object', properties: [
                                new OA\Property(property: 'category', type: 'string', example: 'Notification Channels'),
                                new OA\Property(property: 'enabled', type: 'boolean', example: true),
                                new OA\Property(property: 'settings', type: 'object', properties: [
                                    new OA\Property(property: 'email_notifications', type: 'boolean', example: true),
                                    new OA\Property(property: 'push_notifications', type: 'boolean', example: true),
                                    new OA\Property(property: 'sms_notifications', type: 'boolean', example: false),
                                ]),
                            ]),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index() {}

    #[OA\Put(
        path: '/api/notification-settings',
        summary: 'Update notification settings (any subset of boolean fields)',
        description: 'Each field only accepts true, false, 0, or 1; any other value returns a 422 validation error.',
        security: [['bearerAuth' => []]],
        tags: ['NotificationSettings'],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'order_confirmation', type: 'boolean', example: true),
                    new OA\Property(property: 'order_shipped', type: 'boolean', example: true),
                    new OA\Property(property: 'delivery_updates', type: 'boolean', example: true),
                    new OA\Property(property: 'out_of_stock_alerts', type: 'boolean', example: true),
                    new OA\Property(property: 'weekly_discounts', type: 'boolean', example: true),
                    new OA\Property(property: 'exclusive_member_offers', type: 'boolean', example: true),
                    new OA\Property(property: 'seasonal_campaigns', type: 'boolean', example: true),
                    new OA\Property(property: 'cart_reminders', type: 'boolean', example: true),
                    new OA\Property(property: 'payment_billing', type: 'boolean', example: true),
                    new OA\Property(property: 'email_notifications', type: 'boolean', example: true),
                    new OA\Property(property: 'push_notifications', type: 'boolean', example: true),
                    new OA\Property(property: 'sms_notifications', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification settings updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification settings updated successfully'),
                        new OA\Property(property: 'data', type: 'object', description: 'Same shape as GET /api/notification-settings data'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error (value must be true, false, 0, or 1)', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function update() {}

    #[OA\Put(
        path: '/api/notification-settings/category/{category}',
        summary: 'Enable/disable all settings within a category at once',
        description: 'Valid category values: order_delivery, deals_promotions, account_reminders, channels.',
        security: [['bearerAuth' => []]],
        tags: ['NotificationSettings'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['order_delivery', 'deals_promotions', 'account_reminders', 'channels'])),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['enabled'],
                properties: [
                    new OA\Property(property: 'enabled', type: 'boolean', example: true, description: 'Only true, false, 0, or 1 accepted'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification settings updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification settings updated successfully'),
                        new OA\Property(property: 'data', type: 'object', description: 'Same shape as GET /api/notification-settings data'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid category'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function updateCategory() {}
}
