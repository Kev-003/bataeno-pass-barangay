<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Schema;

class ResidentsIndex extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $barangay_code;

    public function mount($barangay_code)
    {
        $this->barangay_code = \App\Models\Barangay::normalizeCode($barangay_code);
    }

    public function table(Table $table): Table
    {
        $excluded = ['password', 'remember_token', 'egov_data', 'email_verified_at'];
        $columns = array_diff(Schema::getColumnListing('users'), $excluded);

        return $table
            ->query(User::query()->where('barangay_code', $this->barangay_code))
            ->columns(
                collect($columns)->map(function ($column) {
                    return TextColumn::make($column)
                        ->sortable()
                        ->searchable()
                        ->label(str($column)->replace('_', ' ')->title());
                })->toArray()
            );
    }

    public function render()
    {
        return view('livewire.officials.residents-index');
    }
}
