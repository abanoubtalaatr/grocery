<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationPreferences',
    type: 'object',
    properties: [
        new OA\Property(property: 'order_updates', type: 'boolean', example: true),
        new OA\Property(property: 'promotion_emails', type: 'boolean', example: false),
        new OA\Property(property: 'nutrition_insights', type: 'boolean', example: true),
        new OA\Property(property: 'price_alerts', type: 'boolean', example: false),
    ]
)]
class NotificationPreferencesSchema {}
