<?php

namespace App\Filament\CityAdmin\Resources\BarangaysResource\Pages;

use App\Filament\CityAdmin\Resources\BarangaysResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarangays extends EditRecord
{
    protected static string $resource = BarangaysResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
