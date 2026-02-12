<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class UserManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fluentui-person-16-o';

    protected static string $view = 'filament.pages.user-management';

    public function table(Table $table): Table
    {
        $excluded = ['password', 'remember_token', 'egov_data', 'email_verified_at'];
        $columns = array_diff(Schema::getColumnListing('users'), $excluded);

        return $table
            ->query(User::query())
            ->columns(
                collect($columns)->map(function ($column) {
                    return TextColumn::make($column)
                        ->sortable()
                        ->searchable()
                        // This turns 'first_name' into 'First Name' for the label
                        ->label(str($column)->replace('_', ' ')->title());
                })->toArray()
            );
    }
}
