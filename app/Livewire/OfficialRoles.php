<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Spatie\Permission\Models\Role;

class OfficialRoles extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;
    protected static ?string $navigationIcon = 'carbon-assignment-action-usage';

    public function render()
    {
        return view('livewire.official-roles');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Role::query()->whereIn('name', ['Captain', 'Secretary', 'Treasurer', 'Kagawad'])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Official Position')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('permissions.name')
                    ->label('Assigned Permissions')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->wrap()
            ])
            ->actions([
                // Standard Edit Action
                \Filament\Tables\Actions\EditAction::make()
                    ->form([
                        // This is where we put the draggable UI for editing
                        \Filament\Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->columns(2)
                    ]),
            ]);
    }
}
