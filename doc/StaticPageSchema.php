<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StaticPage',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'slug', type: 'string', example: 'about-us'),
        new OA\Property(property: 'title', type: 'string', example: 'About Us'),
        new OA\Property(property: 'content', type: 'string', example: '<p>We are a grocery delivery app...</p>'),
        new OA\Property(property: 'meta_title', type: 'string', nullable: true),
        new OA\Property(property: 'meta_description', type: 'string', nullable: true),
        new OA\Property(property: 'meta_keywords', type: 'array', nullable: true, items: new OA\Items(type: 'string')),
        new OA\Property(property: 'is_published', type: 'boolean', example: true),
        new OA\Property(property: 'order', type: 'integer', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class StaticPageSchema {}
