<?php

namespace Doc;

use OpenApi\Attributes as OA;

class OfferDoc
{
    #[OA\Get(
        path: '/api/offers',
        summary: 'Get all active offers (paginated, with filters and sorting)',
        tags: ['Offers'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', required: false, description: 'Offer type, e.g. percentage, fixed, buy_one_get_one, free_shipping', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'min_purchase', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'featured', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Search in title or code', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', default: 'created_at')),
            new OA\Parameter(name: 'order_direction', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of offers',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Offer')),
                        new OA\Property(property: 'links', type: 'object', properties: [
                            new OA\Property(property: 'first', type: 'string', nullable: true),
                            new OA\Property(property: 'last', type: 'string', nullable: true),
                            new OA\Property(property: 'prev', type: 'string', nullable: true),
                            new OA\Property(property: 'next', type: 'string', nullable: true),
                        ]),
                        new OA\Property(property: 'meta', type: 'object', properties: [
                            new OA\Property(property: 'current_page', type: 'integer', example: 1),
                            new OA\Property(property: 'last_page', type: 'integer', example: 3),
                            new OA\Property(property: 'per_page', type: 'integer', example: 15),
                            new OA\Property(property: 'total', type: 'integer', example: 42),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/api/offers/featured',
        summary: 'Get up to 5 featured offers',
        tags: ['Offers'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Featured offers',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Offer')),
                ])
            ),
        ]
    )]
    public function featured() {}

    #[OA\Get(
        path: '/api/offers/validate',
        summary: 'Validate an offer code, optionally against a purchase amount',
        tags: ['Offers'],
        parameters: [
            new OA\Parameter(name: 'code', in: 'query', required: true, schema: new OA\Schema(type: 'string'), example: 'SUMMER20'),
            new OA\Parameter(name: 'amount', in: 'query', required: false, description: 'Purchase amount to validate the minimum-purchase requirement against', schema: new OA\Schema(type: 'number', format: 'float')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Offer validation result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'valid', type: 'boolean', example: true),
                        new OA\Property(property: 'offer', ref: '#/components/schemas/Offer'),
                        new OA\Property(property: 'discount_amount', type: 'number', format: 'float', example: 10),
                        new OA\Property(property: 'message', type: 'string', example: 'Offer is valid'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid offer code',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'valid', type: 'boolean', example: false),
                    new OA\Property(property: 'message', type: 'string', example: 'Invalid offer code'),
                ])
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function validateOffer() {}

    #[OA\Get(
        path: '/api/offers/{code}',
        summary: 'Get an offer by its code',
        tags: ['Offers'],
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'SUMMER20'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Offer found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/Offer'),
                ])
            ),
            new OA\Response(response: 404, description: 'Offer not found'),
        ]
    )]
    public function showByCode() {}
}
