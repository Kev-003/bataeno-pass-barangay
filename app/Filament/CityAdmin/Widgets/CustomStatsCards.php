<?php

namespace App\Filament\CityAdmin\Widgets;

use Filament\Widgets\Widget;
use App\Models\Barangay;
use App\Models\User;

class CustomStatsCards extends Widget
{
    protected static string $view = 'filament.city-admin.widgets.custom-stats-cards';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getStats(): array
    {
        $tenant = filament()->getTenant();

        $barangayIds = Barangay::where('municity_code', $tenant->id)->pluck('id');

        return [
            [
                'title' => 'Total Barangays',
                'value' => $barangayIds->count(),
                'color' => 'blue'
            ],
            [
                'title' => 'Total Registered Residents',
                'value' => User::whereIn('barangay_id', $barangayIds)->count(),
                'color' => 'blue'
            ]
        ];
    }
}
