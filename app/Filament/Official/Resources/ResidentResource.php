<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\ResidentResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResidentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Management';

    protected static ?string $tenantRelationshipName = 'users';

    protected static ?string $tenantIdAttribute = 'barangay_id';

    protected static ?string $label = 'Residents';

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Basic Information')
                ->schema([
                    Forms\Components\TextInput::make('uuid')
                        ->label('Portal UUID')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Auto-filled from portal')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('first_name')->required(),
                    Forms\Components\TextInput::make('middle_name'),
                    Forms\Components\TextInput::make('last_name')->required(),
                    Forms\Components\TextInput::make('suffix')
                        ->label('Suffix / Ext. Name')
                        ->placeholder('Jr., Sr., III, etc.'),
                    Forms\Components\TextInput::make('email')->email()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('contact_number')
                        ->label('Contact Number')
                        ->tel(),
                    Forms\Components\DatePicker::make('date_of_birth'),
                    Forms\Components\TextInput::make('place_of_birth'),
                    Forms\Components\Select::make('gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female',
                        ]),
                    Forms\Components\Select::make('civil_status')
                        ->options([
                            'Single' => 'Single',
                            'Married' => 'Married',
                            'Widowed' => 'Widowed',
                            'Separated' => 'Separated',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('blood_type')
                        ->placeholder('e.g. O+, A-, AB+'),
                    Forms\Components\TextInput::make('occupation'),
                ])->columns(2),

            Forms\Components\Section::make('Location')
                ->description('Municipality and barangay resolved from Portal or scan data.')
                ->schema([
                    Forms\Components\Select::make('municity_id')
                        ->label('Municipality / City')
                        ->relationship('municity', 'name')
                        ->searchable()
                        ->preload()
                        ->reactive(),
                    Forms\Components\Select::make('barangay_id')
                        ->label('Barangay')
                        ->relationship(
                            'barangay',
                            'name',
                            fn(Builder $query, $get) => $query->when(
                                $get('municity_id'),
                                fn($q, $municityId) => $q->where('municity_code', $municityId)
                            )
                        )
                        ->searchable()
                        ->preload(),
                ])->columns(2),

            Forms\Components\Section::make('Portal Data')
                ->description('Auto-filled from Bataan Portal or PhilID scan.')
                ->schema([
                    Forms\Components\TextInput::make('profile_photos')
                        ->label('Profile Photo URL')
                        ->disabled()
                        ->dehydrated()
                        ->placeholder('Auto-filled from portal'),
                    Forms\Components\Hidden::make('egov_data'),
                ])->columns(1)
                ->collapsed(),

            Forms\Components\Section::make('Lineage & Family')
                ->description('Managing these fields will automatically update and clean up family records.')
                ->schema([
                    Forms\Components\Select::make('father_id')
                        ->label('Father')
                        ->relationship('father', 'first_name', fn(Builder $query) => $query->withTrashed()->where('gender', 'Male'))
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload(),
                    Forms\Components\Select::make('mother_id')
                        ->label('Mother')
                        ->relationship('mother', 'first_name', fn(Builder $query) => $query->withTrashed()->where('gender', 'Female'))
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                        ->searchable(['first_name', 'last_name'])
                        ->preload(),
                    Forms\Components\Select::make('family_id')
                        ->label('Current Family Unit')
                        ->relationship('family', 'family_name')
                        ->searchable()
                        ->preload()
                        ->helperText('Assigning a lone parent to their child\'s family ID here will dissolve their old empty family.'),
                ])->columns(2),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),

                Tables\Columns\TextColumn::make('family.family_name')
                    ->label('Family')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('lineage')
                    ->label('Parents')
                    ->state(function (User $record) {
                        $father = $record->father?->name ?? 'Unknown';
                        $mother = $record->mother?->name ?? 'Unknown';

                        if (!$record->father_id && !$record->mother_id) {
                            return '—';
                        }

                        return "F: {$father}\nM: {$mother}";
                    })
                    ->wrap()
                    ->size('sm'),

                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Male' => 'info',
                        'Female' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Birthday')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('civil_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Single' => 'gray',
                        'Married' => 'success',
                        'Widowed' => 'warning',
                        'Separated' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('civil_status')
                    ->options([
                        'Single' => 'Single',
                        'Married' => 'Married',
                        'Widowed' => 'Widowed',
                        'Separated' => 'Separated',
                    ]),
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ]),
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
            'index' => Pages\ListResidents::route('/'),
            'create' => Pages\CreateResident::route('/create'),
            'edit' => Pages\EditResident::route('/{record}/edit'),
        ];
    }
}
