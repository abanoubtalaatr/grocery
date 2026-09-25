<?php

namespace Doc;

use OpenApi\Attributes as OA;

class HealthDoc
{
    #[OA\Get(
        path: '/api/health',
        summary: 'Health check',
        description: 'Simple endpoint to confirm the API is up and running.',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API is running',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'API is running'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-09-24T10:00:00.000000Z'),
                    ]
                )
            ),
        ]
    )]
    public function health() {}
}
