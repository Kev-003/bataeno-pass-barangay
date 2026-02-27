<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\OfficialResource\Pages;
use App\Models\BarangayTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfficialResource extends Resource
{
    protected static ?string $model = BarangayTerm::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Officials';

    protected static ?string $label = 'Barangay Official';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('position_id')
                    ->relationship('position', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('started_at')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('ended_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Official Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->label('Position')
                    ->badge(),
                Tables\Columns\TextColumn::make('started_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->date()
                    ->placeholder('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->queries(
                        true: fn(Builder $query) => $query->where(fn($q) => $q->whereNull('ended_at')->orWhere('ended_at', '>=', now())),
                        false: fn(Builder $query) => $query->where('ended_at', '<', now()),
                    ),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficials::route('/'),
            'create' => Pages\CreateOfficial::route('/create'),
            'edit' => Pages\EditOfficial::route('/{record}/edit'),
        ];
    }
}
