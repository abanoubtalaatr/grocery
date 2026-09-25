<?php

namespace App\Filament\Resources\MealResource\Pages;

use App\Filament\Resources\MealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeal extends CreateRecord
{
    protected static string $resource = MealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty(trim((string) ($data['brand_new'] ?? '')))) {
            $data['brand'] = trim((string) $data['brand_new']);
        }
        unset($data['brand_new']);

        return $data;
    }
}
