<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\DelegationResource\Pages;
use App\Models\Delegation;
use App\Models\BarangayTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DelegationResource extends Resource
{
    protected static ?string $model = Delegation::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Officials';

    protected static ?string $label = 'Authority Delegations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('delegate_term_id')
                    ->label('Delegate')
                    ->options(function () {
                        // Get officials of the current barangay
                        return BarangayTerm::where('barangay_id', filament()->getTenant()->id)
                            ->where('user_id', '!=', auth()->id())
                            ->get()
                            ->mapWithKeys(fn($term) => [$term->id => $term->user->name . ' (' . $term->position->name . ')']);
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('expires_at')
                    ->label('Expiration Date')
                    ->helperText('Leave empty for indefinite authority.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('granterTerm.user.name')
                    ->label('Granter'),
                Tables\Columns\TextColumn::make('delegateTerm.user.name')
                    ->label('Delegate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->placeholder('Indefinite'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->state(fn(Delegation $record): string => $record->expires_at && $record->expires_at->isPast() ? 'Expired' : 'Active')
                    ->color(fn(string $state): string => $state === 'Active' ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('revoke')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(fn(Delegation $record) => $record->update(['expires_at' => now()])),
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
            'index' => Pages\ListDelegations::route('/'),
            'create' => Pages\CreateDelegation::route('/create'),
            'edit' => Pages\EditDelegation::route('/{record}/edit'),
        ];
    }
}
