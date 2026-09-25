<?php

namespace Doc;

use OpenApi\Attributes as OA;

class StaticPageDoc
{
    #[OA\Get(
        path: '/api/pages',
        summary: 'Get static pages (paginated; published only for non-admin callers)',
        tags: ['Pages'],
        parameters: [
            new OA\Parameter(name: 'published', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search in title/content', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated static pages',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/StaticPage')),
                        new OA\Property(property: 'meta', type: 'object', properties: [
                            new OA\Property(property: 'total', type: 'integer'),
                            new OA\Property(property: 'per_page', type: 'integer'),
                            new OA\Property(property: 'current_page', type: 'integer'),
                            new OA\Property(property: 'last_page', type: 'integer'),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/api/pages/slug/{slug}',
        summary: 'Get a static page by slug',
        tags: ['Pages'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'about-us'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Static page found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/StaticPage'),
                ])
            ),
            new OA\Response(
                response: 404,
                description: 'Page not found (or not published)',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Page not found'),
                ])
            ),
        ]
    )]
    public function showBySlug() {}

    #[OA\Get(
        path: '/api/pages/important',
        summary: 'Get important pages (terms-and-conditions, policies, about-us, contact-us) for footer/menu use',
        tags: ['Pages'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Important pages',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'slug', type: 'string', example: 'about-us'),
                            new OA\Property(property: 'title', type: 'string', example: 'About Us'),
                        ]
                    )),
                ])
            ),
        ]
    )]
    public function importantPages() {}
}
