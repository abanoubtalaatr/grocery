<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactStatisticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total' => $this['total'],
            'new' => $this['new'],
            'read' => $this['read'],
            'replied' => $this['replied'],
            'spam' => $this['spam'],
            'monthly_stats' => $this['monthly_stats'],
        ];
    }
}