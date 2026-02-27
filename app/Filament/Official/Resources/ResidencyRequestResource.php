<?php

namespace App\Filament\Official\Resources;

use App\Filament\Official\Resources\ResidencyRequestResource\Pages;
use App\Filament\Official\Resources\ResidencyRequestResource\RelationManagers;
use App\Models\ResidencyRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResidencyRequestResource extends Resource
{
    protected static ?string $model = ResidencyRequest::class;

    protected static ?string $navigationGroup = 'Household Management';

    protected static ?string $tenantRelationshipName = 'barangay';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requester')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('street')
                    ->label('Address')
                    ->description(fn(ResidencyRequest $record) => trim("{$record->housing_unit} {$record->subdivision}"))
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Head' => 'primary',
                        'Boarder' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(ResidencyRequest $record) => $record->status === 'Pending')
                    ->requiresConfirmation()
                    ->action(function (ResidencyRequest $record) {
                        \DB::transaction(function () use ($record) {
                            // 1. Find or create House (if no existing household_id)
                            $household = null;
                            if ($record->household_id) {
                                $household = \App\Models\Household::find($record->household_id);
                            }

                            if (!$household) {
                                $house = \App\Models\House::firstOrCreate([
                                    'barangay_id' => $record->barangay_id,
                                    'street' => $record->street,
                                    'subdivision' => $record->subdivision,
                                    'housing_unit' => $record->housing_unit,
                                ]);

                                // 2. Find or create Household
                                $household = \App\Models\Household::firstOrCreate([
                                    'house_id' => $house->id,
                                    'ownership' => $record->ownership,
                                ]);
                            }

                            // 3. Mark other profiles as Absent before making this one Present
                            \App\Models\HouseholdMemberProfile::where('user_id', $record->user_id)
                                ->update(['presence_status' => 'Absent']);

                            // 4. Create HouseholdMemberProfile
                            $profile = \App\Models\HouseholdMemberProfile::updateOrCreate(
                                ['user_id' => $record->user_id, 'household_id' => $household->id],
                                [
                                    'role' => $record->role,
                                    'membership_type' => $record->membership_type,
                                    'presence_status' => 'Present',
                                    'started_at' => now(),
                                ]
                            );

                            if ($record->role === 'Head') {
                                $household->update(['household_head_id' => $profile->id]);
                            }

                            // 5. Update User's primary barangay
                            $record->user->update(['barangay_id' => $record->barangay_id]);

                            $record->update([
                                'status' => 'Approved',
                                'approver_id' => auth()->id(),
                                'actioned_at' => now(),
                            ]);
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Residency Request Approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn(ResidencyRequest $record) => $record->status === 'Pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required(),
                    ])
                    ->action(function (ResidencyRequest $record, array $data) {
                        $record->update([
                            'status' => 'Rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'approver_id' => auth()->id(),
                            'actioned_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Residency Request Rejected')
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResidencyRequests::route('/'),
            'create' => Pages\CreateResidencyRequest::route('/create'),
            'edit' => Pages\EditResidencyRequest::route('/{record}/edit'),
        ];
    }
}
