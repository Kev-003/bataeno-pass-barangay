<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\DocumentTransactionResource\Pages;
use App\Models\DocumentTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class DocumentTransactionResource extends Resource
{
    protected static ?string $model = DocumentTransaction::class;

    protected static ?string $navigationGroup = 'Document Requests';

    protected static ?string $label = 'All Requests';

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('requester_id')
                    ->relationship('requester', 'email')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('document_type_id')
                    ->relationship('documentType', 'name')
                    ->required(),
                Forms\Components\TextInput::class::make('purpose')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'issued' => 'Issued',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->disabled(fn(DocumentTransaction $record) => $record->status === 'issued'),
                Forms\Components\Textarea::make('rejection_reason')
                    ->visible(fn(DocumentTransaction $record) => $record->status === 'rejected')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Requested')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Resident')
                    ->searchable(),
                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Document Type'),
                Tables\Columns\TextColumn::make('purpose')
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'issued' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'issued' => 'Issued',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hidden(fn(DocumentTransaction $record) => $record->status === 'pending')
                    ->modalContent(fn(DocumentTransaction $record) => view('filament.official.resources.document-transaction-resource.approval-details', ['record' => $record])),
                Tables\Actions\Action::make('approve_and_issue')
                    ->label('Approve & Issue')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->hidden(fn(DocumentTransaction $record) => $record->status === 'issued')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Document')
                    ->modalDescription('Please review the details below before issuing the document.')
                    ->modalSubmitActionLabel('Confirm Approval & Issue')
                    ->modalContent(fn(DocumentTransaction $record) => view('filament.official.resources.document-transaction-resource.approval-details', ['record' => $record]))
                    ->action(function (DocumentTransaction $record) {
                        $official = auth()->user()->activeTerm;

                        if (!$official) {
                            Notification::make()
                                ->title('Action denied')
                                ->body('You do not have an active official term to sign this document.')
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $service = app(\App\Services\DocumentApprovalService::class);
                            $service->generateAndSign($record, $official);

                            \Filament\Notifications\Notification::make()
                                ->title('Document Issued')
                                ->body("The document has been successfully generated and issued.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Approval Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->hidden(fn(DocumentTransaction $record) => in_array($record->status, ['issued', 'rejected']))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->label('Reason for Rejection'),
                    ])
                    ->action(function (DocumentTransaction $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Request Rejected')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTransactions::route('/'),
            'walk-in' => Pages\EmptyPage::route('/walk-in'),
        ];
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $record->status !== 'issued';
    }
}
