<?php

namespace App\Filament\Resources\OtpResource\Pages;

use App\Filament\Resources\OtpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtps extends ListRecords
{
    protected static string $resource = OtpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
