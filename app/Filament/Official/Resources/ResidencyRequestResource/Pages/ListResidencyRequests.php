<?php

namespace App\Filament\Official\Resources\ResidencyRequestResource\Pages;

use App\Filament\Official\Resources\ResidencyRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResidencyRequests extends ListRecords
{
    protected static string $resource = ResidencyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
