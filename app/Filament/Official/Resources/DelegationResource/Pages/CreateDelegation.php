<?php

namespace App\Filament\Official\Resources\DelegationResource\Pages;

use App\Filament\Official\Resources\DelegationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDelegation extends CreateRecord
{
    protected static string $resource = DelegationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['granter_term_id'] = auth()->user()->activeTerm->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
