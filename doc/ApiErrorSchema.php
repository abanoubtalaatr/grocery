<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ApiError',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Request failed'),
        new OA\Property(property: 'error', type: 'string', nullable: true, example: 'Detailed error message'),
    ]
)]
class ApiErrorSchema {}
