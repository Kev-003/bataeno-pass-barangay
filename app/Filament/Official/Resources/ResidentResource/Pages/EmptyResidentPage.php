<?php

namespace App\Filament\Official\Resources\ResidentResource\Pages;

use App\Filament\Official\Resources\ResidentResource;
use Filament\Resources\Pages\Page;

class EmptyResidentPage extends Page
{
    protected static string $resource = ResidentResource::class;

    protected static string $view = 'filament.official.resources.resident-resource.pages.empty-page';

    protected static ?string $title = 'New Resident';
}
