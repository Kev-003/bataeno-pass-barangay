<?php

namespace App\Filament\CityAdmin\Resources;

use App\Filament\CityAdmin\Resources\HouseholdResource\Pages;
use App\Filament\CityAdmin\Resources\HouseholdResource\RelationManagers;
use App\Models\Household;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\BarangayFilter;

class HouseholdResource extends \App\Filament\Official\Resources\HouseholdResource
{
    protected static ?string $model = Household::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationGroup = 'Management';

    public static function getEloquentQuery(): Builder
    {
        $tenant = filament()->getTenant();
        $barangayIds = \App\Models\Barangay::where('municity_code', $tenant->id)->pluck('id');

        // Household links to House which links to Barangay
        $houseIds = \App\Models\House::whereIn('barangay_id', $barangayIds)->pluck('id');

        return parent::getEloquentQuery()->whereIn('house_id', $houseIds);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                BarangayFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListHouseholds::route('/'),
        ];
    }
}
