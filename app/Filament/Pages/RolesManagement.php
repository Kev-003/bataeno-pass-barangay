<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\User;

class RolesManagement extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;
    protected static ?string $navigationIcon = 'carbon-assignment-action-usage';

    protected static string $view = 'filament.pages.roles-management';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->whereIn('id', [1, 2]))
            ->columns([
                TextColumn::make('name')
                    ->label('Community Role')
                    ->badge()
                    ->color('success'),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->listWithLineBreaks() // Stack them if there are multiple
                    ->limitList(3) // Only show 3, with a "+X more" indicator
                    ->expandableLimitedList()
            ]);
    }

}
