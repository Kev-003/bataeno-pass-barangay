<?php

namespace App\Filament\Official\Widgets;

use Filament\Widgets\Widget;

class WelcomeOfficial extends Widget
{
    protected static string $view = 'filament.official.widgets.welcome-official';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';
}
