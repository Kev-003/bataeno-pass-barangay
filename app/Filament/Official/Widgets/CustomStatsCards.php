<?php

namespace App\Filament\Official\Widgets;

use App\Models\DocumentTransaction;
use App\Models\User;
use Filament\Widgets\Widget;

class CustomStatsCards extends Widget
{
    protected static string $view = 'filament.official.widgets.custom-stats-cards';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getStats(): array
    {
        $tenant = filament()->getTenant();

        return [
            [
                'title' => 'Total Residents',
                'value' => User::where('barangay_code', $tenant->barangay_code)->count(),
                'color' => 'blue'
            ],
            [
                'title' => 'Total Requests',
                'value' => DocumentTransaction::where('barangay_code', $tenant->id)->count(),
                'color' => 'emerald'
            ],
            [
                'title' => 'Pending Requests',
                'value' => DocumentTransaction::where('barangay_code', $tenant->id)
                    ->where('status', 'pending')
                    ->count(),
                'color' => 'rose'
            ],
        ];
    }
}
