<?php

namespace App\Filament\Official\Resources\DelegationResource\Pages;

use App\Filament\Official\Resources\DelegationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelegation extends EditRecord
{
    protected static string $resource = DelegationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
