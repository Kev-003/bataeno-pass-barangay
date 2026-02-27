<?php

namespace App\Filament\Official\Resources\PendingRequestResource\Pages;

use App\Filament\Official\Resources\PendingRequestResource;
use App\Filament\Official\Resources\DocumentTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingRequests extends ListRecords
{
    protected static string $resource = PendingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('walk_in_request')
                ->label('Walk-In Request')
                ->color('primary')
                ->url(fn() => DocumentTransactionResource::getUrl('walk-in')),
        ];
    }
}
