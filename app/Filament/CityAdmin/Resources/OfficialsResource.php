<?php

namespace App\Filament\CityAdmin\Resources;

use App\Filament\CityAdmin\Resources\OfficialsResource\Pages;
use App\Models\BarangayTerm;
use App\Models\Municipality;
use App\Models\Barangay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BarangayRole;
use App\Filament\Filters\BarangayFilter;

class OfficialsResource extends Resource
{
    protected static ?string $model = BarangayTerm::class;

    // Remove tenantOwnershipRelationshipName entirely — getEloquentQuery handles scoping
    protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getEloquentQuery(): Builder
    {
        $tenant = filament()->getTenant();

        $barangayIds = Barangay::where('municity_code', $tenant->id)
            ->pluck('id');

        return parent::getEloquentQuery()
            ->with(['user', 'barangay.municipality', 'position'])
            ->whereIn('barangay_id', $barangayIds);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->required(),


                Forms\Components\Select::make('barangay_id')
                    ->relationship(
                        'barangay',
                        'name',
                        modifyQueryUsing: fn(Builder $query, $get) =>
                        $query->when(
                            $get('municity_id'),
                            fn($q, $municityId) => $q->where('municity_code', $municityId)
                        )
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('position_id')
                    ->relationship('position', 'name', fn($query) => $query->whereIn('name', BarangayRole::officialPositions()))
                    ->required()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Official')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position.name')
                    ->label('Position')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Captain' => 'success',
                        'Secretary' => 'info',
                        'Kagawad' => 'warning',
                        default => 'gray',  // ← was missing
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barangay.name')
                    ->label('Barangay')
                    ->searchable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Since')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Term Status')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state
                        ? \Carbon\Carbon::parse($state)->format('M d, Y')
                        : 'Ongoing')
                    ->badge()
                    ->color(fn($state) => $state
                        ? 'gray'
                        : 'success'),
            ])
            ->defaultSort('ended_at', 'desc')
            ->filters([
                // Default: show only active terms
                Tables\Filters\Filter::make('active')
                    ->label('Active Officials Only')
                    ->query(fn(Builder $query) => $query->where(function ($q) {
                        $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
                    }))
                    ->default(),
                BarangayFilter::make(),

                Tables\Filters\SelectFilter::make('position_id')
                    ->label('Position')
                    ->options(\App\Models\BarangayRole::officialPositionOptions()),

                // Useful: officials with no ended_at (permanent/unset terms)
                Tables\Filters\Filter::make('no_end_date')
                    ->label('No End Date Set')
                    ->query(fn(Builder $query) => $query->whereNull('ended_at')),

                // Useful: terms that have already ended (historical records)
                Tables\Filters\Filter::make('ended')
                    ->label('Past Terms Only')
                    ->query(fn(Builder $query) => $query->where('ended_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListOfficials::route('/'),
        ];
    }
}