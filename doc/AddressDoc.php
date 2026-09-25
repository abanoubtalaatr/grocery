<?php

namespace Doc;

use OpenApi\Attributes as OA;

class AddressDoc
{
    #[OA\Get(
        path: '/api/addresses',
        summary: 'Get all addresses for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Addresses'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Addresses retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Addresses retrieved successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Address')),
                        new OA\Property(property: 'total_count', type: 'integer', example: 2),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'This endpoint does not accept file uploads', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to retrieve addresses', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/addresses',
        summary: 'Create a new address',
        security: [['bearerAuth' => []]],
        tags: ['Addresses'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'phone', 'street_address', 'city'],
                properties: [
                    new OA\Property(property: 'label', type: 'string', nullable: true, example: 'Home'),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Ali'),
                    new OA\Property(property: 'phone', type: 'string', example: '+201234567890'),
                    new OA\Property(property: 'country_code', type: 'string', nullable: true, example: '+20'),
                    new OA\Property(property: 'street_address', type: 'string', example: '12 Tahrir St.'),
                    new OA\Property(property: 'building_number', type: 'string', nullable: true, example: '12'),
                    new OA\Property(property: 'floor', type: 'string', nullable: true, example: '3'),
                    new OA\Property(property: 'apartment', type: 'string', nullable: true, example: '5'),
                    new OA\Property(property: 'landmark', type: 'string', nullable: true, example: 'Next to the pharmacy'),
                    new OA\Property(property: 'city', type: 'string', example: 'Cairo'),
                    new OA\Property(property: 'state', type: 'string', nullable: true, example: 'Cairo'),
                    new OA\Property(property: 'postal_code', type: 'string', nullable: true, example: '11511'),
                    new OA\Property(property: 'country', type: 'string', nullable: true, example: 'Egypt'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Leave with doorman'),
                    new OA\Property(property: 'is_default', type: 'boolean', nullable: true, example: false),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Address created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Address created successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Address'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to create address', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/addresses/{id}',
        summary: 'Get a single address',
        security: [['bearerAuth' => []]],
        tags: ['Addresses'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Address retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Address retrieved successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Address'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Address not found'),
            new OA\Response(response: 500, description: 'Failed to retrieve address', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/addresses/{id}',
        summary: 'Update an address (all fields optional)',
        security: [['bearerAuth' => []]],
        tags: ['Addresses'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'label', type: 'string', example: 'Home'),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Ali'),
                    new OA\Property(property: 'phone', type: 'string', example: '+201234567890'),
                    new OA\Property(property: 'country_code', type: 'string', nullable: true, example: '+20'),
                    new OA\Property(property: 'street_address', type: 'string', example: '12 Tahrir St.'),
                    new OA\Property(property: 'building_number', type: 'string', nullable: true, example: '12'),
                    new OA\Property(property: 'floor', type: 'string', nullable: true, example: '3'),
                    new OA\Property(property: 'apartment', type: 'string', nullable: true, example: '5'),
                    new OA\Property(property: 'landmark', type: 'string', nullable: true, example: 'Next to the pharmacy'),
                    new OA\Property(property: 'city', type: 'string', example: 'Cairo'),
                    new OA\Property(property: 'state', type: 'string', nullable: true, example: 'Cairo'),
                    new OA\Property(property: 'postal_code', type: 'string', nullable: true, example: '11511'),
                    new OA\Property(property: 'country', type: 'string', nullable: true, example: 'Egypt'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Leave with doorman'),
                    new OA\Property(property: 'is_default', type: 'boolean', nullable: true, example: true),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Address updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Address updated successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Address'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Address not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to update address', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/api/addresses/{id}',
        summary: 'Delete an address',
        security: [['bearerAuth' => []]],
        tags: ['Addresses'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Address deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Address not found'),
            new OA\Response(response: 500, description: 'Failed to delete address', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function destroy() {}

    #[OA\Post(
        path: '/api/addresses/{id}/set-default',
        summary: 'Set an address as the default address',
        security: [['bearerAuth' => []]],
        tags: ['Addresses'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Default address updated successfully (or already the default)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Default address updated successfully'),
                        new OA\Property(property: 'already_default', type: 'boolean', nullable: true, example: false),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Address'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Address not found'),
            new OA\Response(response: 500, description: 'Failed to set default address', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function setDefault() {}
}
