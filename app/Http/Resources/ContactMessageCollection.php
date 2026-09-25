<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactMessageCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'stats' => [
                    'new' => $this->collection->where('status', 'new')->count(),
                    'read' => $this->collection->where('status', 'read')->count(),
                    'replied' => $this->collection->where('status', 'replied')->count(),
                    'spam' => $this->collection->where('status', 'spam')->count(),
                ]
            ],
        ];
    }
}