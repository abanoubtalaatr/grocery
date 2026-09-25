<?php

namespace Doc;

use OpenApi\Attributes as OA;

class UserAppSettingsDoc
{
    #[OA\Get(
        path: '/api/language',
        summary: "Get the authenticated user's app language",
        security: [['bearerAuth' => []]],
        tags: ['AppSettings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Language retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'language', type: 'string', enum: ['en', 'ar'], example: 'en'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function showLanguage() {}

    #[OA\Put(
        path: '/api/language',
        summary: "Update the authenticated user's app language",
        security: [['bearerAuth' => []]],
        tags: ['AppSettings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['language'],
                properties: [
                    new OA\Property(property: 'language', type: 'string', enum: ['en', 'ar'], example: 'ar'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Language updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Language updated successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'language', type: 'string', enum: ['en', 'ar'], example: 'ar'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function updateLanguage() {}

    #[OA\Get(
        path: '/api/appearance',
        summary: "Get the authenticated user's app theme",
        security: [['bearerAuth' => []]],
        tags: ['AppSettings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Appearance retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'theme', type: 'string', enum: ['light', 'dark'], example: 'light'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function showAppearance() {}

    #[OA\Put(
        path: '/api/appearance',
        summary: "Update the authenticated user's app theme",
        security: [['bearerAuth' => []]],
        tags: ['AppSettings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['theme'],
                properties: [
                    new OA\Property(property: 'theme', type: 'string', enum: ['light', 'dark'], example: 'dark'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Appearance updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Appearance updated successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'theme', type: 'string', enum: ['light', 'dark'], example: 'dark'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function updateAppearance() {}

    #[OA\Get(
        path: '/api/notification-preferences',
        summary: "Get the authenticated user's notification preferences",
        security: [['bearerAuth' => []]],
        tags: ['AppSettings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification preferences retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/NotificationPreferences'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function showNotificationPreferences() {}

    #[OA\Put(
        path: '/api/notification-preferences',
        summary: "Update the authenticated user's notification preferences",
        description: 'All fields are optional; only the ones provided are updated.',
        security: [['bearerAuth' => []]],
        tags: ['AppSettings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'order_updates', type: 'boolean', example: true),
                    new OA\Property(property: 'promotion_emails', type: 'boolean', example: false),
                    new OA\Property(property: 'nutrition_insights', type: 'boolean', example: true),
                    new OA\Property(property: 'price_alerts', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification preferences updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notification preferences updated successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/NotificationPreferences'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function updateNotificationPreferences() {}
}
