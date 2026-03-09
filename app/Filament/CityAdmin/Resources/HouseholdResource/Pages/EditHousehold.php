<?php

namespace App\Filament\CityAdmin\Resources\HouseholdResource\Pages;

use App\Filament\CityAdmin\Resources\HouseholdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHousehold extends EditRecord
{
    protected static string $resource = HouseholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
