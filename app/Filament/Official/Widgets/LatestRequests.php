<?php

namespace App\Filament\Official\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestRequests extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getTableHeading(): string|null
    {
        return 'Recent Document Requests';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\DocumentTransaction::query()
                    ->where('barangay_code', filament()->getTenant()->id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Resident'),
                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Document Type'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'issued' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ]);
    }
}
