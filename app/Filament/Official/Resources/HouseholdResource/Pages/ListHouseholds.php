<?php

namespace App\Filament\Official\Resources\HouseholdResource\Pages;

use App\Filament\Official\Resources\HouseholdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHouseholds extends ListRecords
{
    protected static string $resource = HouseholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
