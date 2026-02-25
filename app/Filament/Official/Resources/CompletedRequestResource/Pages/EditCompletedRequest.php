<?php

namespace App\Filament\Official\Resources\CompletedRequestResource\Pages;

use App\Filament\Official\Resources\CompletedRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompletedRequest extends EditRecord
{
    protected static string $resource = CompletedRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
