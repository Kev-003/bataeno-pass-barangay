<?php

namespace App\Filament\Filters;

use App\Models\Barangay;
use App\Models\Municipality;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

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
                    // Check if municipality filter is active
                    $activeMunicipalityId = request()->query('tableFilters')['municity_id']['municity_id'] ?? null;

                    if ($activeMunicipalityId) {
                        return Barangay::where('municity_code', $activeMunicipalityId)
                            ->pluck('name', 'id');
                    }

                    return Barangay::where('municity_code', $tenant->id)
                        ->pluck('name', 'id');
                }

                return [];
            })
            ->visible(fn() => filament()->getCurrentPanel()?->getId() === 'city');
    }
}