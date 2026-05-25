<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Exceptions\BookingCancellationException;
use App\Models\Booking;
use App\Services\BookingCancellationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('court.name')
                    ->label('Court')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('coach.name')
                    ->label('Coach')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('total_price')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('match_type')
                    ->badge(),
                TextColumn::make('session_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('max_players')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('match_type')
                    ->options([
                        'private' => 'Private',
                        'open_match' => 'Open match',
                    ]),
                SelectFilter::make('court')
                    ->relationship('court', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('start_date')
                    ->form([\Filament\Forms\Components\DatePicker::make('from')->label('From')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['from'] ?? null),
                        fn (Builder $q) => $q->whereDate('start_time', '>=', $data['from'])
                    )),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('cancelBooking')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record): bool => in_array($record->status, ['pending', 'confirmed'], true))
                    ->action(function (Booking $record): void {
                        try {
                            app(BookingCancellationService::class)->cancel($record, auth()->user());

                            Notification::make()->title('Booking cancelled')->success()->send();
                        } catch (BookingCancellationException $exception) {
                            Notification::make()
                                ->title('Cancellation failed')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
