<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\CompletedRequestResource\Pages;
use App\Models\DocumentTransaction;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompletedRequestResource extends Resource
{
    protected static ?string $model = DocumentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Document Requests';

    protected static ?string $label = 'Completed';

    protected static ?string $tenantRelationshipName = 'barangay';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('status', ['issued', 'rejected']);
    }

    public static function table(Table $table): Table
    {
        return DocumentTransactionResource::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompletedRequests::route('/'),
        ];
    }
}
