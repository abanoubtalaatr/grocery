<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'social_media' => [
                'facebook' => $this->facebook,
                'linkedin' => $this->linkedin,
                'instagram' => $this->instagram,
                'twitter' => $this->twitter,
            ],
            'contact_info' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
            ],
            'site_info' => [
                'site_name' => $this->site_name,
                'site_description' => $this->site_description,
                'copyright_text' => $this->copyright_text,
                'logo' => $this->logo,
                'favicon' => $this->favicon,
            ],
            'shipping' => [
                'shipping_fee' => (float) ($this->shipping_fee ?? 0),
                'free_shipping_min_order' => $this->free_shipping_min_order !== null ? (float) $this->free_shipping_min_order : null,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}