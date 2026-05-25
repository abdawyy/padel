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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                SelectFilter::make('club_id')
                    ->relationship('club', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('session_type')
                    ->options([
                        'group_training' => 'Group training',
                        'private_training' => 'Private training',
                        'academy_class' => 'Academy class',
                    ]),
                SelectFilter::make('session_date')
                    ->form([\Filament\Forms\Components\DatePicker::make('on')->label('On date')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['on'] ?? null),
                        fn (Builder $q) => $q->whereDate('start_time', $data['on'])
                    )),
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
