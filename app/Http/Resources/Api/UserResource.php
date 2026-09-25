<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'profile_picture'     => $this->profile_image_url,
            'name'                => $this->full_name,
            'username'            => $this->username,
            'firstname'           => $this->firstname,
            'lastname'            => $this->lastname,
            'gender'              => $this->gender,
            'birthday'            => $this->birthday?->format('Y-m-d'),
            'email'               => $this->email,
            'phone'               => $this->phone,
            'country_code'        => $this->country_code,
            'email_verified'      => $this->email_verified,
            'phone_verified'      => $this->phone_verified,
            'preferred_languages' => $this->preferred_languages ?? [],
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}