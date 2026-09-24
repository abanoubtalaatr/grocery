<?php

namespace Doc;

use OpenApi\Attributes as OA;

class SupportDoc
{
    #[OA\Post(
        path: '/api/support/report',
        summary: 'Submit a support / problem report',
        description: 'If order_number is provided, it must belong to the authenticated user.',
        security: [['bearerAuth' => []]],
        tags: ['Support'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['issue_type', 'message'],
                properties: [
                    new OA\Property(property: 'issue_type', type: 'string', minLength: 2, maxLength: 255, example: 'Missing item'),
                    new OA\Property(property: 'order_number', type: 'string', nullable: true, maxLength: 255, example: 'ORD-00000101'),
                    new OA\Property(property: 'message', type: 'string', minLength: 10, maxLength: 2000, example: 'One of the items I ordered was missing from the delivery.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Support report submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Support report submitted successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 17),
                            new OA\Property(property: 'issue_type', type: 'string', example: 'Missing item'),
                            new OA\Property(property: 'order_number', type: 'string', nullable: true, example: 'ORD-00000101'),
                            new OA\Property(property: 'message', type: 'string'),
                            new OA\Property(property: 'status', type: 'string', example: 'open'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error (or order_number not found on the account)', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function store() {}
}
