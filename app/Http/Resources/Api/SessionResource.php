<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentTokenId = $request->user()?->currentAccessToken()?->id;

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'is_current'   => (string) $this->id === (string) $currentTokenId,
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}