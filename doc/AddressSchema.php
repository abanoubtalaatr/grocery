<?php

namespace Doc;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Address',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'label', type: 'string', nullable: true, example: 'Home'),
        new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Ali'),
        new OA\Property(property: 'phone', type: 'string', example: '01234567890'),
        new OA\Property(property: 'country_code', type: 'string', nullable: true, example: '+20'),
        new OA\Property(property: 'formatted_phone', type: 'string', nullable: true, example: '+201234567890'),
        new OA\Property(property: 'street_address', type: 'string', example: '12 Tahrir St.'),
        new OA\Property(property: 'building_number', type: 'string', nullable: true, example: '12'),
        new OA\Property(property: 'floor', type: 'string', nullable: true, example: '3'),
        new OA\Property(property: 'apartment', type: 'string', nullable: true, example: '5'),
        new OA\Property(property: 'landmark', type: 'string', nullable: true, example: 'Next to the pharmacy'),
        new OA\Property(property: 'city', type: 'string', example: 'Cairo'),
        new OA\Property(property: 'state', type: 'string', nullable: true, example: 'Cairo'),
        new OA\Property(property: 'postal_code', type: 'string', nullable: true, example: '11511'),
        new OA\Property(property: 'country', type: 'string', nullable: true, example: 'Egypt'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Leave with doorman'),
        new OA\Property(property: 'is_default', type: 'boolean', example: true),
        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: 30.0444),
        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: 31.2357),
        new OA\Property(property: 'full_address', type: 'string', nullable: true, example: '12 Tahrir St., Cairo, Egypt'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class AddressSchema {}
