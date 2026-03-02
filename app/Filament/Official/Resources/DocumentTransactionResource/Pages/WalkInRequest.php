<?php

namespace App\Filament\Official\Resources\DocumentTransactionResource\Pages;

use App\Filament\Official\Resources\DocumentTransactionResource;
use Filament\Resources\Pages\Page;

class WalkInRequest extends Page
{
    protected static string $resource = DocumentTransactionResource::class;

    protected static string $view = 'filament.official.resources.document-transaction-resource.pages.walk-in-request';
}
