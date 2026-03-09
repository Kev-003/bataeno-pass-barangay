<?php

namespace App\Filament\CityAdmin\Resources;

use App\Filament\CityAdmin\Resources\ResidentResource\Pages;
use App\Filament\CityAdmin\Resources\ResidentResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\BarangayFilter;

class ResidentResource extends \App\Filament\Official\Resources\ResidentResource
{
    protected static bool $isScopedToTenant = false;
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
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
                Tables\Actions\Action::make('view_tree')
                    ->label('Lineage')
                    ->icon('heroicon-o-arrows-up-down')
                    ->modalHeading(fn(User $record) => "{$record->name}'s Family Tree")
                    ->modalContent(fn(User $record) => view('filament.official.components.lineage-tree', [
                        'user' => $record->load([
                            'father' => fn($q) => $q->withTrashed()->with(['father' => fn($q2) => $q2->withTrashed(), 'mother' => fn($q2) => $q2->withTrashed()]),
                            'mother' => fn($q) => $q->withTrashed()->with(['father' => fn($q2) => $q2->withTrashed(), 'mother' => fn($q2) => $q2->withTrashed()]),
                        ]),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalWidth('5xl'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // Disable create/edit pages
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
            'index' => Pages\ListResidents::route('/'),
        ];
    }
}
