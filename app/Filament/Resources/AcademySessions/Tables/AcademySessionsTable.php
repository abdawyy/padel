<?php

namespace App\Filament\Resources\AcademySessions\Tables;

use App\Exceptions\BookingCancellationException;
use App\Models\AcademySession;
use App\Services\AcademySessionCancellationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademySessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('club.name')
                    ->label('Club')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('court.name')
                    ->label('Court')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('coach.name')
                    ->label('Coach')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session_type')
                    ->label('Package Type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('players_count')
                    ->counts('players')
                    ->label('Players'),
                TextColumn::make('max_players')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price_per_player')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('cancelSession')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (AcademySession $record): bool => in_array($record->status, ['scheduled', 'active'], true))
                    ->action(function (AcademySession $record): void {
                        try {
                            app(AcademySessionCancellationService::class)->cancel($record, auth()->user());

                            Notification::make()->title('Session cancelled')->success()->send();
                        } catch (BookingCancellationException $exception) {
                            Notification::make()->title('Cancellation failed')->body($exception->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
