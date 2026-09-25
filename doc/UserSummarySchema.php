<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserSummary',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'username', type: 'string', example: 'ahmed_dev'),
        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'user@example.com'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+201234567890'),
    ]
)]
class UserSummarySchema {}
