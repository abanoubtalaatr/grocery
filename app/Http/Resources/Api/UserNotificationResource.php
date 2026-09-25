<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id'         => $this->id,
            'type'       => $data['type'] ?? 'order',
            'title'      => $data['title'] ?? 'Order update',
            'body'       => $data['body'] ?? '',
            'is_read'    => $this->read_at !== null,
            'read_at'    => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'action_url' => $data['action_url'] ?? null,
        ];
    }
}