<?php

namespace Doc;

use OpenApi\Attributes as OA;

class ContactDoc
{
    #[OA\Post(
        path: '/api/contact',
        summary: 'Submit a contact message',
        tags: ['Contact'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'subject', 'message'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ahmed Mostafa'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+201234567890'),
                    new OA\Property(property: 'subject', type: 'string', example: 'Question about my order'),
                    new OA\Property(property: 'message', type: 'string', minLength: 10, maxLength: 250, example: 'Hello, I would like to ask about my recent order status.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Message submitted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Thank you for your message. We will get back to you soon.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Ahmed Mostafa'),
                            new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                            new OA\Property(property: 'phone', type: 'string', nullable: true),
                            new OA\Property(property: 'subject', type: 'string'),
                            new OA\Property(property: 'message', type: 'string'),
                            new OA\Property(property: 'status', type: 'string', example: 'new'),
                            new OA\Property(property: 'admin_notes', type: 'string', nullable: true),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'human_date', type: 'string', example: '2 minutes ago'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Message detected as spam', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Your message appears to be spam'),
            ])),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function submit() {}
}
