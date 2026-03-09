<?php

namespace App\Filament\CityAdmin\Resources\OfficialsResource\Pages;

use App\Filament\CityAdmin\Resources\OfficialsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficials extends ListRecords
{
    protected static string $resource = OfficialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
