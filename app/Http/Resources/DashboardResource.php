<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'overview'              => $this->resource['overview'],
            'shopping_insights'     => $this->resource['shopping_insights'],
            'category_distribution' => $this->resource['category_distribution'],
            'recent_orders'         => $this->resource['recent_orders'],
            'top_purchases'         => $this->resource['top_purchases'],
        ];
    }
}