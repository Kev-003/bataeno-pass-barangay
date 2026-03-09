<?php

namespace App\Filament\CityAdmin\Resources\FamilyResource\Pages;

use App\Filament\CityAdmin\Resources\FamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFamily extends EditRecord
{
    protected static string $resource = FamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
