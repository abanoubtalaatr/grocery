<?php

namespace Doc;

use OpenApi\Attributes as OA;

class SpecialNoteDoc
{
    #[OA\Get(
        path: '/api/special-notes',
        summary: 'Get all special notes',
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Special notes retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Please note delivery hours are 9 AM - 10 PM'),
                            ]
                        )),
                    ]
                )
            ),
        ]
    )]
    public function index() {}
}
