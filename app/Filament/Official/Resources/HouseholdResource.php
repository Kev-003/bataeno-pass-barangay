<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\HouseholdResource\Pages;
use App\Filament\Official\Resources\HouseholdResource\RelationManagers;
use App\Models\Household;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HouseholdResource extends Resource
{
    protected static ?string $model = Household::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $tenantRelationshipName = 'barangay';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('house_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('household_head_id')
                    ->numeric(),
                Forms\Components\TextInput::make('ownership')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('monthly_utility_expense')
                    ->numeric(),
                Forms\Components\TextInput::make('total_income')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('expires_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('house_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('household_head_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ownership')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monthly_utility_expense')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_income')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListHouseholds::route('/'),
            'create' => Pages\CreateHousehold::route('/create'),
            'edit' => Pages\EditHousehold::route('/{record}/edit'),
        ];
    }
}
