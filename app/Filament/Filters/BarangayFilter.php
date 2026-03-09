<?php

namespace App\Filament\Filters;

use App\Models\Barangay;
use App\Models\Municipality;
use Filament\Tables\Filters\SelectFilter;

class BarangayFilter
{
    public static function make(): SelectFilter
    {
        return SelectFilter::make('barangay_id')
            ->label('Barangay')
            ->options(function () {
                $tenant = filament()->getTenant();
                if (!$tenant)
                    return [];

                if ($tenant instanceof Barangay) {
                    return Barangay::where('id', $tenant->id)->pluck('name', 'id');
                }

                if ($tenant instanceof Municipality) {
                    return Barangay::where('municity_code', $tenant->id)->pluck('name', 'id');
                }

                return [];
            })
            ->visible(fn() => filament()->getCurrentPanel()?->getId() === 'city');
    }
}