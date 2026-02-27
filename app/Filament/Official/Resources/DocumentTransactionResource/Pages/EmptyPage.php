<?php

namespace App\Filament\Official\Resources\DocumentTransactionResource\Pages;

use App\Filament\Official\Resources\DocumentTransactionResource;
use Filament\Resources\Pages\Page;

class EmptyPage extends Page
{
    protected static string $resource = DocumentTransactionResource::class;

    protected static string $view = 'filament.official.resources.document-transaction-resource.pages.empty-page';

    protected static ?string $title = 'Walk-In Request';
}
