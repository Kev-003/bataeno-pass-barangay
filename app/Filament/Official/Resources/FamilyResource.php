<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\FamilyResource\Pages;
use App\Models\Family;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FamilyResource extends Resource
{
    protected static ?string $model = Family::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $label = 'Families';

    protected static ?string $tenantRelationshipName = 'barangay';

    protected static ?string $tenantIdAttribute = 'barangay_id';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('family_name')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('family_name')
                    ->label('Family Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('father.name')
                    ->label('Father')
                    ->placeholder('None'),

                Tables\Columns\TextColumn::make('mother.name')
                    ->label('Mother')
                    ->placeholder('None'),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Count')
                    ->counts('members')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('household.house.street')
                    ->label('Street')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('household.house.subdivision')
                    ->label('Subdivision')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilies::route('/'),
        ];
    }
}
