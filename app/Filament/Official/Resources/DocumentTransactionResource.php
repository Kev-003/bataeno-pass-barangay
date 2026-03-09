<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\DocumentTransactionResource\Pages;
use App\Models\DocumentTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
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
                Select::make('requester_id')
                    ->relationship('requester', 'email')
                    ->searchable()
                    ->required(),
                Select::make('document_type_id')
                    ->relationship('documentType', 'name')
                    ->required(),
                TextInput::make('purpose')
                    ->required(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'issued' => 'Issued',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->disabled(fn(DocumentTransaction $record) => $record->status === 'issued'),
                Textarea::make('rejection_reason')
                    ->visible(fn(DocumentTransaction $record) => $record->status === 'rejected')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date Requested')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('requester.name')
                    ->label('Resident')
                    ->searchable(),
                TextColumn::make('documentType.name')
                    ->label('Document Type'),
                TextColumn::make('purpose')
                    ->limit(50),
                TextColumn::make('status')
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
                    ->requiresConfirmation(false)
                    ->modalHeading('Approve & Issue Document')
                    ->modalSubmitActionLabel('Confirm & Issue')
                    ->modalWidth('md')
                    ->modalIcon('heroicon-o-check-badge')
                    ->modalIconColor('success')
                    ->form(fn(DocumentTransaction $record) => [
                        Forms\Components\View::make('filament.official.resources.document-transaction-resource.approval-details')
                            ->viewData(['record' => $record]),

                        Forms\Components\Radio::make('signature_mode')
                            ->label('Signature Method')
                            ->options([
                                'esign' => 'E-Sign',
                                'ink' => 'Ink Sign',
                            ])
                            ->default('esign')
                            ->inline()
                            ->inlineLabel(false)
                            ->required(),
                    ])
                    ->action(function (DocumentTransaction $record, array $data) {
                        $official = auth()->user()->activeTerm()->first();

                        // Optimistically update status so UI reflects processing state
                        $record->update(['status' => 'processing']);

                        \App\Jobs\ProcessDocumentApproval::dispatch(
                            $record,
                            $official,
                            $data['signature_mode'] ?? 'esign'
                        );

                        Notification::make()
                            ->title('Document is being processed')
                            ->body('The document will be ready shortly.')
                            ->info()
                            ->send();
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

                        // Notify Resident via Reverb
                        Notification::make()
                            ->title('Request Rejected')
                            ->body("Your request for {$record->documentType->name} was rejected. Reason: {$data['rejection_reason']}")
                            ->danger()
                            ->sendToDatabase($record->requester)
                            ->broadcast($record->requester);

                        Notification::make()->title('Resident Notified')->send();
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
            'walk-in' => Pages\WalkInRequest::route('/walk-in'),
        ];
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $record->status !== 'issued';
    }
}
