<?php

namespace App\Filament\CityAdmin\Resources\HouseResource\Pages;

use App\Filament\CityAdmin\Resources\HouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHouses extends ListRecords
{
    protected static string $resource = HouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
