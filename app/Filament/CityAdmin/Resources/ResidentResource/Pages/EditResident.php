<?php

namespace App\Filament\CityAdmin\Resources\ResidentResource\Pages;

use App\Filament\CityAdmin\Resources\ResidentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResident extends EditRecord
{
    protected static string $resource = ResidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
