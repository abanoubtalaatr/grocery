<?php

namespace Doc;

use OpenApi\Attributes as OA;

class NotificationDoc
{
    #[OA\Get(
        path: '/api/notifications',
        summary: 'Get paginated notifications for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100)),
            new OA\Parameter(name: 'read', in: 'query', required: false, description: 'Filter by read/unread', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search title/body', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'read_at'], default: 'created_at')),
            new OA\Parameter(name: 'order_dir', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'notifications', type: 'array', items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: '9c858901-8a57-4791-81fe-4c455b099bc9'),
                                    new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                                    new OA\Property(property: 'title', type: 'string', example: 'Your order is on its way'),
                                    new OA\Property(property: 'body', type: 'string', example: 'Order #1024 has shipped and is on its way.'),
                                    new OA\Property(property: 'action_url', type: 'string', nullable: true),
                                    new OA\Property(property: 'action_label', type: 'string', example: 'View'),
                                    new OA\Property(property: 'is_read', type: 'boolean', example: false),
                                    new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'created_at_human', type: 'string', example: '2 hours ago'),
                                    new OA\Property(property: 'icon', type: 'string', example: 'truck'),
                                    new OA\Property(property: 'priority', type: 'string', example: 'normal'),
                                ]
                            )),
                            new OA\Property(property: 'unread_count', type: 'integer', example: 3),
                            new OA\Property(property: 'total_count', type: 'integer', example: 42),
                            new OA\Property(property: 'pagination', type: 'object', properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                            ]),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/api/notifications/with-resources',
        summary: 'Get paginated notifications, with related meal/order resources attached when referenced',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15, minimum: 1, maximum: 100)),
            new OA\Parameter(name: 'read', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['created_at', 'read_at'], default: 'created_at')),
            new OA\Parameter(name: 'order_dir', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications retrieved successfully, each item may include a "resources" object with "meal" and/or "order"',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'notifications', type: 'array', items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: '9c858901-8a57-4791-81fe-4c455b099bc9'),
                                    new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                                    new OA\Property(property: 'title', type: 'string', example: 'Your order is on its way'),
                                    new OA\Property(property: 'body', type: 'string', example: 'Order #1024 has shipped and is on its way.'),
                                    new OA\Property(property: 'action_url', type: 'string', nullable: true),
                                    new OA\Property(property: 'action_label', type: 'string', example: 'View'),
                                    new OA\Property(property: 'is_read', type: 'boolean', example: false),
                                    new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'created_at_human', type: 'string', example: '2 hours ago'),
                                    new OA\Property(property: 'icon', type: 'string', example: 'truck'),
                                    new OA\Property(property: 'priority', type: 'string', example: 'normal'),
                                    new OA\Property(property: 'resources', type: 'object', properties: [
                                        new OA\Property(property: 'meal', type: 'object', nullable: true),
                                        new OA\Property(property: 'order', type: 'object', nullable: true),
                                    ]),
                                ]
                            )),
                            new OA\Property(property: 'unread_count', type: 'integer', example: 3),
                            new OA\Property(property: 'total_count', type: 'integer', example: 42),
                            new OA\Property(property: 'pagination', type: 'object', properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                            ]),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to load notifications', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function indexWithResources() {}

    #[OA\Get(
        path: '/api/notifications/stats',
        summary: 'Get notification statistics for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification statistics',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'total', type: 'integer', example: 42),
                            new OA\Property(property: 'unread', type: 'integer', example: 3),
                            new OA\Property(property: 'read', type: 'integer', example: 39),
                            new OA\Property(property: 'by_type', type: 'object', additionalProperties: new OA\AdditionalProperties(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'total', type: 'integer', example: 5),
                                    new OA\Property(property: 'unread', type: 'integer', example: 1),
                                ]
                            )),
                            new OA\Property(property: 'recent_types', type: 'array', items: new OA\Items(type: 'string'), example: ['order_shipped', 'weekly_discounts']),
                            new OA\Property(property: 'last_notification_at', type: 'string', format: 'date-time', nullable: true),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function stats() {}

    #[OA\Get(
        path: '/api/notifications/unread-count',
        summary: 'Get the unread notifications count for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread count retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'count', type: 'integer', example: 3),
                            new OA\Property(property: 'has_unread', type: 'boolean', example: true),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function unreadCount() {}

    #[OA\Get(
        path: '/api/notifications/recent',
        summary: 'Get notifications created in the last 24 hours (max 10)',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recent notifications retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'notifications', type: 'array', items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: '9c858901-8a57-4791-81fe-4c455b099bc9'),
                                    new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                                    new OA\Property(property: 'title', type: 'string', example: 'Your order is on its way'),
                                    new OA\Property(property: 'body', type: 'string', example: 'Order #1024 has shipped and is on its way.'),
                                    new OA\Property(property: 'action_url', type: 'string', nullable: true),
                                    new OA\Property(property: 'action_label', type: 'string', example: 'View'),
                                    new OA\Property(property: 'is_read', type: 'boolean', example: false),
                                    new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'created_at_human', type: 'string', example: '2 hours ago'),
                                    new OA\Property(property: 'icon', type: 'string', example: 'truck'),
                                    new OA\Property(property: 'priority', type: 'string', example: 'normal'),
                                ]
                            )),
                            new OA\Property(property: 'total_recent', type: 'integer', example: 4),
                            new OA\Property(property: 'unread_recent', type: 'integer', example: 2),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function recent() {}

    #[OA\Get(
        path: '/api/notifications/{id}',
        summary: 'Get a single notification (marks it as read as a side effect if unread)',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Notification UUID', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification retrieved (detailed)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'string', example: '9c858901-8a57-4791-81fe-4c455b099bc9'),
                            new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                            new OA\Property(property: 'title', type: 'string', example: 'Your order is on its way'),
                            new OA\Property(property: 'body', type: 'string', example: 'Order #1024 has shipped and is on its way.'),
                            new OA\Property(property: 'action_url', type: 'string', nullable: true),
                            new OA\Property(property: 'action_label', type: 'string', example: 'View'),
                            new OA\Property(property: 'is_read', type: 'boolean', example: false),
                            new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'created_at_human', type: 'string', example: '2 hours ago'),
                            new OA\Property(property: 'icon', type: 'string', example: 'truck'),
                            new OA\Property(property: 'priority', type: 'string', example: 'normal'),
                            new OA\Property(property: 'data', type: 'object', description: 'Raw notification payload'),
                            new OA\Property(property: 'channels', type: 'array', items: new OA\Items(type: 'string'), example: ['database']),
                            new OA\Property(property: 'metadata', type: 'object'),
                            new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/notifications/{id}/read',
        summary: 'Mark a notification as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Notification UUID', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification marked as read',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification marked as read'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'string', example: '9c858901-8a57-4791-81fe-4c455b099bc9'),
                            new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                            new OA\Property(property: 'title', type: 'string', example: 'Your order is on its way'),
                            new OA\Property(property: 'body', type: 'string', example: 'Order #1024 has shipped and is on its way.'),
                            new OA\Property(property: 'is_read', type: 'boolean', example: true),
                            new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Notification is already read'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function markAsRead() {}

    #[OA\Put(
        path: '/api/notifications/{id}/unread',
        summary: 'Mark a notification as unread',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Notification UUID', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification marked as unread',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification marked as unread'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'string', example: '9c858901-8a57-4791-81fe-4c455b099bc9'),
                            new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                            new OA\Property(property: 'title', type: 'string', example: 'Your order is on its way'),
                            new OA\Property(property: 'body', type: 'string', example: 'Order #1024 has shipped and is on its way.'),
                            new OA\Property(property: 'is_read', type: 'boolean', example: false),
                            new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Notification is already unread'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function markAsUnread() {}

    #[OA\Delete(
        path: '/api/notifications/{id}',
        summary: 'Delete a notification',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Notification UUID', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ]
    )]
    public function destroy() {}

    #[OA\Put(
        path: '/api/notifications/mark-all-read',
        summary: 'Mark all unread notifications as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: '{n} notifications marked as read'),
            new OA\Response(response: 400, description: 'No unread notifications'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function markAllAsRead() {}

    #[OA\Delete(
        path: '/api/notifications/delete-multiple',
        summary: 'Delete multiple notifications by id',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ids'],
                properties: [
                    new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'string'), example: ['9c858901-8a57-4791-81fe-4c455b099bc9']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: '{n} notifications deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function destroyMultiple() {}

    #[OA\Delete(
        path: '/api/notifications/clear-all',
        summary: 'Clear all (or read/unread) notifications, requires explicit confirmation',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['confirmation'],
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['read', 'unread', 'all'], example: 'all'),
                    new OA\Property(property: 'confirmation', type: 'boolean', example: true, description: 'Must be true/accepted'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Notifications cleared successfully'),
            new OA\Response(response: 400, description: 'Please confirm you want to clear all notifications'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function clearAll() {}

    #[OA\Get(
        path: '/api/notifications/type/{type}',
        summary: 'Get notifications filtered by type',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'path', required: true, description: 'Notification type, e.g. order_shipped', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                            new OA\Property(property: 'notifications', type: 'array', items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: '9c858901-8a57-4791-81fe-4c455b099bc9'),
                                    new OA\Property(property: 'type', type: 'string', example: 'order_shipped'),
                                    new OA\Property(property: 'title', type: 'string', example: 'Your order is on its way'),
                                    new OA\Property(property: 'body', type: 'string', example: 'Order #1024 has shipped and is on its way.'),
                                    new OA\Property(property: 'action_url', type: 'string', nullable: true),
                                    new OA\Property(property: 'action_label', type: 'string', example: 'View'),
                                    new OA\Property(property: 'is_read', type: 'boolean', example: false),
                                    new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'created_at_human', type: 'string', example: '2 hours ago'),
                                    new OA\Property(property: 'icon', type: 'string', example: 'truck'),
                                    new OA\Property(property: 'priority', type: 'string', example: 'normal'),
                                ]
                            )),
                            new OA\Property(property: 'total', type: 'integer', example: 5),
                            new OA\Property(property: 'unread', type: 'integer', example: 2),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function byType() {}
}
