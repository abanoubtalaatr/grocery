<?php

namespace App\Filament\Resources\SmartListResource\Pages;

use App\Filament\Resources\SmartListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmartLists extends ListRecords
{
    protected static string $resource = SmartListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
