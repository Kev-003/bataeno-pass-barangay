<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\PendingRequestResource\Pages;
use App\Models\DocumentTransaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PendingRequestResource extends Resource
{
    protected static ?string $model = DocumentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Document Requests';

    protected static ?string $label = 'Pending';

    protected static ?string $tenantRelationshipName = 'barangay';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'pending');
    }

    public static function table(Table $table): Table
    {
        return DocumentTransactionResource::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendingRequests::route('/'),
        ];
    }
}
