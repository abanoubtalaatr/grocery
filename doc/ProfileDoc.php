<?php

namespace Doc;

use OpenApi\Attributes as OA;

class ProfileDoc
{
    #[OA\Get(
        path: '/api/profile',
        summary: 'Get full authenticated user profile (info, addresses, order history, in-progress orders, notifications, sessions, wishlist)',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'me', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'profile_picture', type: 'string', nullable: true),
                                new OA\Property(property: 'name', type: 'string', example: 'Ahmed Ali'),
                                new OA\Property(property: 'username', type: 'string', example: 'ahmed_dev'),
                                new OA\Property(property: 'firstname', type: 'string', nullable: true, example: 'Ahmed'),
                                new OA\Property(property: 'lastname', type: 'string', nullable: true, example: 'Ali'),
                                new OA\Property(property: 'gender', type: 'string', nullable: true, example: 'male'),
                                new OA\Property(property: 'birthday', type: 'string', format: 'date', nullable: true, example: '1995-05-20'),
                                new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                                new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+201234567890'),
                                new OA\Property(property: 'country_code', type: 'string', nullable: true, example: '+20'),
                                new OA\Property(property: 'email_verified', type: 'boolean', example: true),
                                new OA\Property(property: 'phone_verified', type: 'boolean', example: false),
                                new OA\Property(property: 'preferred_languages', type: 'array', items: new OA\Items(type: 'string'), example: ['en', 'ar']),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ]),
                            new OA\Property(property: 'addresses', type: 'array', items: new OA\Items(ref: '#/components/schemas/Address')),
                            new OA\Property(property: 'order_history', type: 'object', properties: [
                                new OA\Property(property: 'orders', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'ordered_at', type: 'array', items: new OA\Items(type: 'string', format: 'date-time')),
                            ]),
                            new OA\Property(property: 'in_progress_orders', type: 'array', items: new OA\Items(type: 'object')),
                            new OA\Property(property: 'order_notifications', type: 'array', items: new OA\Items(type: 'object')),
                            new OA\Property(property: 'settings', type: 'object', properties: [
                                new OA\Property(property: 'privacy_and_security', type: 'object', properties: [
                                    new OA\Property(property: 'active_sessions', type: 'array', items: new OA\Items(type: 'object')),
                                    new OA\Property(property: 'change_password', type: 'object', properties: [new OA\Property(property: 'available', type: 'boolean', example: true)]),
                                    new OA\Property(property: 'change_username', type: 'object', properties: [new OA\Property(property: 'available', type: 'boolean', example: true)]),
                                ]),
                            ]),
                            new OA\Property(property: 'wishlist', type: 'array', items: new OA\Items(type: 'object')),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve profile', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function show() {}

    #[OA\Post(
        path: '/api/profile/image',
        summary: 'Upload/update the profile image',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['image'],
                    properties: [
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Image file (jpeg, png, jpg, gif), max 2MB'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile image updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile image updated successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'profile_image', type: 'string', example: 'profile-images/abc123.jpg'),
                            new OA\Property(property: 'profile_image_url', type: 'string', example: 'https://example.com/storage/profile-images/abc123.jpg'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error (only one image, must be jpeg/png/jpg/gif, max 2MB)', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to update profile image', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function updateImage() {}

    #[OA\Put(
        path: '/api/profile/info',
        summary: 'Update profile information (all fields optional)',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'ahmed_dev'),
                    new OA\Property(property: 'firstname', type: 'string', example: 'Ahmed'),
                    new OA\Property(property: 'lastname', type: 'string', example: 'Ali'),
                    new OA\Property(property: 'gender', type: 'string', nullable: true, enum: ['male', 'female', 'other', 'prefer_not_to_say']),
                    new OA\Property(property: 'birthday', type: 'string', format: 'date', nullable: true, example: '1995-05-20', description: 'Must be a date before today'),
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '01234567890', description: 'Egyptian mobile number, 11-13 chars'),
                    new OA\Property(property: 'country_code', type: 'string', example: '+20'),
                    new OA\Property(property: 'preferred_languages', type: 'array', items: new OA\Items(type: 'string'), example: ['en', 'ar']),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile updated successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'username', type: 'string', example: 'ahmed_dev'),
                            new OA\Property(property: 'firstname', type: 'string', nullable: true),
                            new OA\Property(property: 'lastname', type: 'string', nullable: true),
                            new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Ali'),
                            new OA\Property(property: 'gender', type: 'string', nullable: true),
                            new OA\Property(property: 'birthday', type: 'string', format: 'date', nullable: true),
                            new OA\Property(property: 'email', type: 'string', nullable: true),
                            new OA\Property(property: 'phone', type: 'string', nullable: true),
                            new OA\Property(property: 'country_code', type: 'string', nullable: true),
                            new OA\Property(property: 'preferred_languages', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'profile_image_url', type: 'string', nullable: true),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'No data provided to update'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to update profile', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function updateInfo() {}

    #[OA\Delete(
        path: '/api/profile/image',
        summary: 'Delete the profile image',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile image deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'No profile image to delete'),
            new OA\Response(response: 500, description: 'Failed to delete profile image', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function deleteImage() {}

    #[OA\Get(
        path: '/api/profile/sessions',
        summary: 'List active sessions/devices (Sanctum tokens) for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sessions retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Sessions retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 3),
                                new OA\Property(property: 'name', type: 'string', nullable: true, example: 'iPhone 15'),
                                new OA\Property(property: 'last_used_at', type: 'string', format: 'date-time', nullable: true),
                                new OA\Property(property: 'is_current', type: 'boolean', example: true),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                            ]
                        )),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function sessions() {}

    #[OA\Delete(
        path: '/api/profile/sessions/{tokenId}',
        summary: 'Revoke a session/device (logout from that Sanctum token)',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(name: 'tokenId', in: 'path', required: true, description: 'Sanctum personal access token id', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Session revoked successfully'),
            new OA\Response(response: 400, description: 'Cannot revoke your current session from this request. Use logout instead.'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Session not found'),
        ]
    )]
    public function destroySession() {}
}
