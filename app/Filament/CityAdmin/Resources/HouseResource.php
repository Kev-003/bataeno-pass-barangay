<?php

namespace App\Filament\CityAdmin\Resources;

use App\Filament\CityAdmin\Resources\HouseResource\Pages;
use App\Filament\CityAdmin\Resources\HouseResource\RelationManagers;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\BarangayFilter;

class HouseResource extends \App\Filament\Official\Resources\HouseResource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationGroup = 'Management';

    public static function getEloquentQuery(): Builder
    {
        $tenant = filament()->getTenant();
        $barangayIds = \App\Models\Barangay::where('municity_code', $tenant->id)->pluck('id');

        return parent::getEloquentQuery()->whereIn('barangay_id', $barangayIds);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                BarangayFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_inhabitants')
                    ->label('Inhabitants')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalHeading(fn(House $record) => "Residents of " . ($record->housing_unit ? "{$record->housing_unit}, " : "") . $record->street)
                    ->modalContent(fn(House $record) => view('filament.official.resources.house.inhabitants', [
                        'house' => $record->load('households.members.user'),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
            'index' => Pages\ListHouses::route('/'),

        ];
    }
}
