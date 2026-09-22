<?php

namespace Doc;

use OpenApi\Attributes as OA;

class ChatbotDoc
{
    #[OA\Post(
        path: '/api/chatbot',
        summary: 'Send a message to the AI assistant',
        description: 'Accepts an optional conversation_id (UUID) to keep conversation history across requests. `message` is also accepted as an alias for `question`.',
        security: [['bearerAuth' => []]],
        tags: ['Chatbot'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['question'],
                properties: [
                    new OA\Property(property: 'question', type: 'string', maxLength: 1000, example: 'What products and offers do you have?'),
                    new OA\Property(property: 'conversation_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'session_id', type: 'string', format: 'uuid', nullable: true, description: 'Deprecated alias for conversation_id, kept for backwards compatibility'),
                    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, nullable: true),
                    new OA\Property(property: 'locale', type: 'string', enum: ['ar', 'en'], nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chat response generated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Chat response generated successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 42),
                            new OA\Property(property: 'conversation_id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'session_id', type: 'string', format: 'uuid', description: 'Same value as conversation_id, for backwards compatibility'),
                            new OA\Property(property: 'question', type: 'string', example: 'What products and offers do you have?'),
                            new OA\Property(property: 'answer', type: 'string', example: 'We currently have fresh produce, dairy and a 20% off offer on fruits...'),
                            new OA\Property(property: 'rating', type: 'integer', nullable: true, example: null),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
            new OA\Response(response: 500, description: 'Failed to process chat request', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function chat() {}

    #[OA\Get(
        path: '/api/chatbot/history',
        summary: "Get the authenticated user's chatbot conversation history (paginated)",
        security: [['bearerAuth' => []]],
        tags: ['Chatbot'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chat history retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Chat history retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 42),
                                    new OA\Property(property: 'session_id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'question', type: 'string'),
                                    new OA\Property(property: 'answer', type: 'string'),
                                    new OA\Property(property: 'rating', type: 'integer', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            )),
                            new OA\Property(property: 'pagination', type: 'object', properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                                new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
                                new OA\Property(property: 'to', type: 'integer', nullable: true, example: 15),
                            ]),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Failed to retrieve chat history', content: new OA\JsonContent(ref: '#/components/schemas/ApiError')),
        ]
    )]
    public function history() {}

    #[OA\Get(
        path: '/api/chatbot/suggestions',
        summary: 'Get suggested quick-reply questions (localised)',
        security: [['bearerAuth' => []]],
        tags: ['Chatbot'],
        parameters: [
            new OA\Parameter(name: 'locale', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['en', 'ar'], default: 'en')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Suggestions retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Suggestions retrieved successfully'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'suggestions', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: 'orders'),
                                    new OA\Property(property: 'label', type: 'string', example: 'Track order'),
                                    new OA\Property(property: 'question', type: 'string', example: 'How do I track my order?'),
                                ],
                                type: 'object'
                            )),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function suggestions() {}
}
