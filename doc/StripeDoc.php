<?php

namespace Doc;

use OpenApi\Attributes as OA;

class StripeDoc
{
    #[OA\Get(
        path: '/api/cards',
        summary: "List the authenticated user's saved Stripe cards",
        description: 'Returns an empty array if the user has no Stripe customer yet. Note: this endpoint returns the raw Stripe PaymentMethod list, not the standard {success, message, data} envelope.',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Array of Stripe PaymentMethod objects (card type)',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string', example: 'pm_1AbCdEfGhIjKlMn'),
                            new OA\Property(property: 'type', type: 'string', example: 'card'),
                            new OA\Property(property: 'card', type: 'object', properties: [
                                new OA\Property(property: 'brand', type: 'string', example: 'visa'),
                                new OA\Property(property: 'last4', type: 'string', example: '4242'),
                                new OA\Property(property: 'exp_month', type: 'integer', example: 12),
                                new OA\Property(property: 'exp_year', type: 'integer', example: 2030),
                            ]),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function listCards() {}

    #[OA\Post(
        path: '/api/setup-intent',
        summary: 'Create a Stripe SetupIntent to save a new card',
        description: 'Creates a Stripe customer for the user if one does not exist yet, then creates a SetupIntent for adding a card.',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'SetupIntent created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'clientSecret', type: 'string', example: 'seti_1AbCdEfGhIjKlMn_secret_xyz'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function createSetupIntent() {}

    #[OA\Post(
        path: '/api/charge-card',
        summary: 'Charge a previously saved card (off-session)',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['payment_method_id', 'amount'],
                properties: [
                    new OA\Property(property: 'payment_method_id', type: 'string', example: 'pm_1AbCdEfGhIjKlMn'),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', description: 'Amount in the major currency unit (USD); converted to cents internally', example: 25.5),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Charge successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'payment_intent', type: 'object', description: 'The raw Stripe PaymentIntent object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationError')),
        ]
    )]
    public function chargeSavedCard() {}

    #[OA\Delete(
        path: '/api/cards/{id}',
        summary: 'Delete (detach) a saved card',
        security: [['bearerAuth' => []]],
        tags: ['Payments'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Stripe PaymentMethod id', schema: new OA\Schema(type: 'string'), example: 'pm_1AbCdEfGhIjKlMn'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Card deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'deleted'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function deleteCard() {}
}
