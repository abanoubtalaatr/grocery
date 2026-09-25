<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'label'           => $this->label,
            'full_name'       => $this->full_name,
            'phone'           => $this->phone,
            'country_code'    => $this->country_code,
            'street_address'  => $this->street_address,
            'building_number' => $this->building_number,
            'floor'           => $this->floor,
            'apartment'       => $this->apartment,
            'landmark'        => $this->landmark,
            'city'            => $this->city,
            'state'           => $this->state,
            'postal_code'     => $this->postal_code,
            'country'         => $this->country,
            'full_address'    => $this->full_address ?? null,
            'is_default'      => $this->is_default,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}