<?php

namespace App\Filament\Official\Resources\OfficialResource\Pages;

use App\Filament\Official\Resources\OfficialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfficial extends EditRecord
{
    protected static string $resource = OfficialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
