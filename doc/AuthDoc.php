<?php

namespace Doc;

use OpenApi\Attributes as OA;

class AuthDoc
{
    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Register a new user',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password', 'password_confirmation', 'agree_terms'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'ahmed_dev'),
                    new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+201234567890'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Passw0rd'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Passw0rd'),
                    new OA\Property(property: 'agree_terms', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Registration successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Registration successful'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'user', ref: '#/components/schemas/UserSummary'),
                            new OA\Property(property: 'token', type: 'string', example: '1|abcdef123456'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Registration failed', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Login with username/email/phone and password',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login', type: 'string', example: 'ahmed_dev', description: 'Username, email or phone'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Passw0rd'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'user', ref: '#/components/schemas/UserSummary'),
                            new OA\Property(property: 'token', type: 'string', example: '1|abcdef123456'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Login failed', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Server error', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: '/api/auth/forgot-password',
        summary: 'Send OTP to reset password',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['identifier'],
                properties: [
                    new OA\Property(property: 'identifier', type: 'string', description: 'Email or phone', example: 'user@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OTP sent successfully'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to send OTP', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function forgotPassword() {}

    #[OA\Post(
        path: '/api/auth/verify-otp',
        summary: 'Verify OTP code',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['identifier', 'otp'],
                properties: [
                    new OA\Property(property: 'identifier', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OTP verified successfully'),
            new OA\Response(response: 400, description: 'Invalid or expired OTP', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function verifyOtp() {}

    #[OA\Post(
        path: '/api/auth/reset-password',
        summary: 'Reset password using a verified OTP',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['identifier', 'otp', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'identifier', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NewPassw0rd'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'NewPassw0rd'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password reset successfully'),
            new OA\Response(response: 400, description: 'Password reset failed', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function resetPassword() {}

    #[OA\Post(
        path: '/api/auth/google',
        summary: 'Login or register via Google',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_token'],
                properties: [
                    new OA\Property(property: 'id_token', type: 'string', description: 'Google OAuth ID token'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'user', ref: '#/components/schemas/UserSummary'),
                            new OA\Property(property: 'token', type: 'string'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid Google token', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function google() {}

    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Logout the authenticated user (revokes current token)',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logout successful'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Logout failed', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function logout() {}

    #[OA\Post(
        path: '/api/auth/change-password',
        summary: 'Change password for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password changed successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function changePassword() {}

    #[OA\Delete(
        path: '/api/auth/delete-account',
        summary: 'Delete the authenticated user account',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Account deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to delete account', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function deleteAccount() {}

    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Get the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated user',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'user', ref: '#/components/schemas/UserSummary'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me() {}
}
