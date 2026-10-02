<?php

namespace Doc;

use OpenApi\Attributes as OA;

class DataManagementDoc
{
    #[OA\Get(
        path: '/api/data-management/download',
        summary: "Download an export of the authenticated user's personal data",
        description: 'Streams a JSON file download (Content-Type: application/json) containing the profile, addresses, recent orders and notification preferences. This is a file download, not a {success, message, data} JSON envelope.',
        security: [['bearerAuth' => []]],
        tags: ['DataManagement'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'JSON data export file',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'exported_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'profile', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'username', type: 'string', example: 'ahmed_dev'),
                            new OA\Property(property: 'firstname', type: 'string', nullable: true),
                            new OA\Property(property: 'lastname', type: 'string', nullable: true),
                            new OA\Property(property: 'email', type: 'string', nullable: true),
                            new OA\Property(property: 'phone', type: 'string', nullable: true),
                            new OA\Property(property: 'app_language', type: 'string', nullable: true, example: 'en'),
                            new OA\Property(property: 'app_theme', type: 'string', nullable: true, example: 'light'),
                            new OA\Property(property: 'loyalty_points', type: 'integer', nullable: true, example: 120),
                            new OA\Property(property: 'store_credits', type: 'number', format: 'float', nullable: true, example: 0),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                        ]),
                        new OA\Property(property: 'addresses', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'label', type: 'string', example: 'Home'),
                                new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Ali'),
                                new OA\Property(property: 'phone', type: 'string', example: '+201234567890'),
                                new OA\Property(property: 'street_address', type: 'string', example: '12 Nile St.'),
                                new OA\Property(property: 'city', type: 'string', example: 'Cairo'),
                                new OA\Property(property: 'country', type: 'string', example: 'Egypt'),
                                new OA\Property(property: 'is_default', type: 'boolean', example: true),
                            ],
                            type: 'object'
                        )),
                        new OA\Property(property: 'orders', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'order_number', type: 'string', example: 'ORD-00000101'),
                                new OA\Property(property: 'status', type: 'string', example: 'placed'),
                                new OA\Property(property: 'total', type: 'number', format: 'float', example: 65),
                                new OA\Property(property: 'placed_at', type: 'string', format: 'date-time', nullable: true),
                            ],
                            type: 'object'
                        )),
                        new OA\Property(property: 'notification_preferences', ref: '#/components/schemas/NotificationPreferences'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function download() {}

    #[OA\Delete(
        path: '/api/data-management/delete',
        summary: "Permanently delete the authenticated user's account and personal data",
        security: [['bearerAuth' => []]],
        tags: ['DataManagement'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Account deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Account deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function delete() {}
}
