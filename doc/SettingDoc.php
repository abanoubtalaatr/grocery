<?php

namespace Doc;

use OpenApi\Attributes as OA;

class SettingDoc
{
    #[OA\Get(
        path: '/api/settings',
        summary: 'Get app-wide settings (social media, contact info, site info, shipping)',
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Settings retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'social_media', type: 'object', properties: [
                                new OA\Property(property: 'facebook', type: 'string', nullable: true),
                                new OA\Property(property: 'linkedin', type: 'string', nullable: true),
                                new OA\Property(property: 'instagram', type: 'string', nullable: true),
                                new OA\Property(property: 'twitter', type: 'string', nullable: true),
                            ]),
                            new OA\Property(property: 'contact_info', type: 'object', properties: [
                                new OA\Property(property: 'email', type: 'string', nullable: true),
                                new OA\Property(property: 'phone', type: 'string', nullable: true),
                                new OA\Property(property: 'address', type: 'string', nullable: true),
                            ]),
                            new OA\Property(property: 'site_info', type: 'object', properties: [
                                new OA\Property(property: 'site_name', type: 'string', nullable: true, example: 'Grocery'),
                                new OA\Property(property: 'site_description', type: 'string', nullable: true),
                                new OA\Property(property: 'copyright_text', type: 'string', nullable: true),
                                new OA\Property(property: 'logo', type: 'string', nullable: true),
                                new OA\Property(property: 'favicon', type: 'string', nullable: true),
                            ]),
                            new OA\Property(property: 'shipping', type: 'object', properties: [
                                new OA\Property(property: 'shipping_fee', type: 'number', format: 'float', example: 20),
                                new OA\Property(property: 'free_shipping_min_order', type: 'number', format: 'float', nullable: true, example: 300),
                            ]),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]),
                    ]
                )
            ),
        ]
    )]
    public function index() {}
}
