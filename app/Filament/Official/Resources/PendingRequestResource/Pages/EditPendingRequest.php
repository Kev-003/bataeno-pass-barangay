<?php

namespace App\Filament\Official\Resources\PendingRequestResource\Pages;

use App\Filament\Official\Resources\PendingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingRequest extends EditRecord
{
    protected static string $resource = PendingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
