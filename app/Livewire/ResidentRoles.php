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

class ResidentRoles extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;
    protected static ?string $navigationIcon = 'carbon-assignment-action-usage';

    public function render()
    {
        return view('livewire.resident-roles');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query()->whereIn('name', ['Resident', 'Household Head']))
            ->columns([
                TextColumn::make('name')
                    ->label('Community Role')
                    ->color('primary')
                    ->weight('bold'),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->listWithLineBreaks() // Stack them if there are multiple
                    ->limitList(3) // Only show 3, with a "+X more" indicator
                    ->expandableLimitedList()
            ]);
    }
}
