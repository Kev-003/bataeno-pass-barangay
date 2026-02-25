<?php

namespace App\Filament\Official\Resources\DocumentTransactionResource\Pages;

use App\Filament\Official\Resources\DocumentTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentTransactions extends ListRecords
{
    protected static string $resource = DocumentTransactionResource::class;

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
