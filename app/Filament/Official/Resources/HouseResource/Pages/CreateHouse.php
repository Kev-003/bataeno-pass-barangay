<?php

namespace App\Filament\Official\Resources\HouseResource\Pages;

use App\Filament\Official\Resources\HouseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHouse extends CreateRecord
{
    protected static string $resource = HouseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = filament()->getTenant();

        $data['barangay_id'] = $tenant->id;
        $data['barangay_code'] = $tenant->barangay_code;
        $data['municity_code'] = $tenant->municity_code;
        $data['barangay'] = $tenant->name;

        return $data;
    }
}
