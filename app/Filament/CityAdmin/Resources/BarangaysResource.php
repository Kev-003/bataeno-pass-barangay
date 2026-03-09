<?php

namespace App\Filament\CityAdmin\Resources;

use App\Filament\CityAdmin\Resources\BarangaysResource\Pages;
use App\Filament\CityAdmin\Resources\BarangaysResource\RelationManagers;
use App\Models\Barangay;
use App\Models\User;
use App\Models\BarangayTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangaysResource extends Resource
{
    protected static ?string $model = Barangay::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Barangay Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barangay_code')
                    ->label('Barangay Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activeCaptain.user.name')
                    ->label('Captain')
                    ->default('— No Captain —')
                    ->badge()
                    ->color(fn($state) => $state === '— No Captain —' ? 'danger' : 'success')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Resident Count')
                    ->counts('users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('families_count')
                    ->label('Family Count')
                    ->counts('families')
                    ->sortable(),
                Tables\Columns\TextColumn::make('all_households_count')
                    ->label('Household Count')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangays::route('/'),
        ];
    }
}
