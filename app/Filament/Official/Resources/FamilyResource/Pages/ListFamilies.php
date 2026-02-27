<?php

namespace App\Filament\Official\Resources\FamilyResource\Pages;

use App\Filament\Official\Resources\FamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFamilies extends ListRecords
{
    protected static string $resource = FamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
