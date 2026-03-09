<?php

namespace App\Filament\CityAdmin\Resources;

use App\Filament\CityAdmin\Resources\FamilyResource\Pages;
use App\Filament\CityAdmin\Resources\FamilyResource\RelationManagers;
use App\Models\Family;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FamilyResource extends \App\Filament\Official\Resources\FamilyResource
{
    protected static ?string $model = Family::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Management';

    protected static bool $isScopedToTenant = false;

    public static function getEloquentQuery(): Builder
    {
        $tenant = filament()->getTenant();

        $barangayIds = \App\Models\Barangay::where('municity_code', $tenant->id)
            ->pluck('id');

        return parent::getEloquentQuery()
            ->whereIn('barangay_id', $barangayIds);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->actions([
                Tables\Actions\Action::make('view_members')
                    ->label('Members')
                    ->icon('heroicon-o-users')
                    ->modalHeading(fn(Family $record) => "{$record->family_name} Family Members")
                    ->modalContent(fn(Family $record) => view('filament.official.components.family-members', [
                        'family' => $record->load(['members' => fn($q) => $q->withTrashed()]),
                    ]))
                    ->modalSubmitAction(false),
            ])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilies::route('/'),
        ];
    }
}
