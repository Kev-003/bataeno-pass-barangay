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
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\Select::make('house_id')
                            ->relationship('house', 'street')
                            ->getOptionLabelFromRecordUsing(fn($record) => ($record->housing_unit ? "{$record->housing_unit}, " : "") . $record->street)
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Forms\Components\Section::make('Management')
                    ->schema([
                        Forms\Components\Select::make('household_head_id')
                            ->label('Head of Household')
                            ->relationship('members', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->user?->name ?? "Member #{$record->id}")
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('ownership')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Financials')
                    ->schema([
                        Forms\Components\TextInput::make('monthly_utility_expense')
                            ->numeric()
                            ->prefix('₱'),
                        Forms\Components\TextInput::make('total_income')
                            ->numeric()
                            ->prefix('₱'),
                        Forms\Components\DateTimePicker::make('expires_at'),
                    ])->columns(3),
            ]);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                // Left column — static info
                \Filament\Infolists\Components\Group::make()
                    ->columnSpan(1)
                    ->schema([
                        \Filament\Infolists\Components\Section::make('House Details')
                            ->icon('heroicon-o-home')
                            ->compact()
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('house.address')
                                    ->label('Full Address')
                                    ->state(fn($record) => ($record->house->housing_unit ? "{$record->house->housing_unit}, " : "") . $record->house->street . ($record->house->subdivision ? ", {$record->house->subdivision}" : "")),
                                \Filament\Infolists\Components\TextEntry::make('house.street')
                                    ->label('Street Name'),
                            ]),

                        \Filament\Infolists\Components\Section::make('Household Management')
                            ->icon('heroicon-o-user-group')
                            ->compact()
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('headOfHousehold.user.name')
                                    ->label('Head of Household')
                                    ->placeholder('No head assigned')
                                    ->weight('bold')
                                    ->color('primary'),
                                \Filament\Infolists\Components\TextEntry::make('ownership')
                                    ->badge()
                                    ->color('info'),
                            ])->columns(2),

                        \Filament\Infolists\Components\Section::make('Financial Information')
                            ->icon('heroicon-o-banknotes')
                            ->compact()
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('monthly_utility_expense')
                                    ->money('PHP'),
                                \Filament\Infolists\Components\TextEntry::make('total_income')
                                    ->money('PHP'),
                                \Filament\Infolists\Components\TextEntry::make('expires_at')
                                    ->dateTime()
                                    ->placeholder('Never'),
                            ])->columns(3),
                    ]),

                // Right column — members list
                \Filament\Infolists\Components\Section::make('Members')
                    ->icon('heroicon-o-users')
                    ->compact()
                    ->columnSpan(1)
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('members')
                            ->label('')
                            ->schema([
                                \Filament\Infolists\Components\Grid::make([
                                    'default' => 2,  // forces 2 columns even on mobile
                                    'sm' => 2,
                                    'md' => 2,
                                    'lg' => 2,
                                ])
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('user.name')
                                            ->label('')
                                            ->weight('medium')
                                            ->color('gray')
                                            ->grow(true),
                                        \Filament\Infolists\Components\TextEntry::make('role')
                                            ->label('')
                                            ->badge()
                                            ->color('info')
                                            ->placeholder('Member'),
                                    ])
                            ])
                            ->contained(false)
                            ->placeholder('No members registered'),
                    ]),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('house.address')
                    ->label('Address')
                    ->state(fn($record) => ($record->house->housing_unit ? "{$record->house->housing_unit}, " : "") . $record->house->street)
                    ->searchable(['housing_unit', 'street'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('barangay.name')
                    ->label('Barangay')
                    ->placeholder('—')
                    ->visible(fn() => filament()->getCurrentPanel()?->getId() === 'city')
                    ->sortable(),
                Tables\Columns\TextColumn::make('headOfHousehold.user.name')
                    ->label('Head of Household')
                    ->placeholder('None')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ownership')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_income')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('members_count')
                    ->label('Members')
                    ->counts('members')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
