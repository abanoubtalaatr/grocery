<?php

namespace Doc;

use OpenApi\Attributes as OA;

class FaqDoc
{
    #[OA\Get(
        path: '/api/faqs',
        summary: 'Get FAQs (paginated, filterable by category/search, active only by default)',
        tags: ['Faqs'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'active_only', in: 'query', required: false, description: 'Defaults to true', schema: new OA\Schema(type: 'boolean', default: true)),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search in question/answer', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'with_categories', in: 'query', required: false, description: 'When true, includes the distinct list of active FAQ categories', schema: new OA\Schema(type: 'boolean', default: false)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated FAQs',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'question', type: 'string', example: 'How do I track my order?'),
                                    new OA\Property(property: 'answer', type: 'string', example: 'You can track your order from the Orders section.'),
                                    new OA\Property(property: 'category', type: 'string', nullable: true, example: 'orders'),
                                    new OA\Property(property: 'order', type: 'integer', nullable: true),
                                    new OA\Property(property: 'is_active', type: 'boolean'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )),
                            new OA\Property(property: 'meta', type: 'object', properties: [
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'from', type: 'integer', nullable: true),
                                new OA\Property(property: 'to', type: 'integer', nullable: true),
                            ]),
                            new OA\Property(property: 'links', type: 'object', properties: [
                                new OA\Property(property: 'first', type: 'string', nullable: true),
                                new OA\Property(property: 'last', type: 'string', nullable: true),
                                new OA\Property(property: 'prev', type: 'string', nullable: true),
                                new OA\Property(property: 'next', type: 'string', nullable: true),
                            ]),
                        ]),
                        new OA\Property(property: 'categories', type: 'array', nullable: true, items: new OA\Items(type: 'string'), description: 'Present only when with_categories=true'),
                    ]
                )
            ),
        ]
    )]
    public function index() {}
}
