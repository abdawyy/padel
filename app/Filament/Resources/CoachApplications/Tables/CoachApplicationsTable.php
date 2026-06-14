<?php

namespace App\Filament\Resources\CoachApplications\Tables;

use App\Models\CoachApplication;
use App\Services\CoachApplicationResponseService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoachApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('session.title')->label('Session')->searchable()->sortable(),
                TextColumn::make('session.club.name')->label('Club')->sortable(),
                TextColumn::make('coach.name')->label('Coach')->searchable(),
                TextColumn::make('coach.email')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('message')->limit(40)->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('session')
                    ->form([\Filament\Forms\Components\TextInput::make('value')->numeric()->label('Session ID')])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $q) => $q->where('academy_session_id', (int) $data['value'])
                    )),
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'accepted' => 'Accepted',
                    'declined' => 'Declined',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CoachApplication $record): bool => $record->isPending())
                    ->action(function (CoachApplication $record): void {
                        self::respond($record, 'accepted');
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Textarea::make('response_note')->label('Note to coach')->maxLength(500),
                    ])
                    ->visible(fn (CoachApplication $record): bool => $record->isPending())
                    ->action(function (CoachApplication $record, array $data): void {
                        self::respond($record, 'declined', $data['response_note'] ?? null);
                    }),
            ]);
    }

    private static function respond(CoachApplication $record, string $status, ?string $note = null): void
    {
        try {
            app(CoachApplicationResponseService::class)->respond($record, auth()->user(), $status, $note);
            Notification::make()->title('Application '.$status)->success()->send();
        } catch (\RuntimeException $exception) {
            Notification::make()
                ->title('Action failed')
                ->body(match ($exception->getMessage()) {
                    'already_responded' => 'Already responded.',
                    'coach_already_assigned' => 'Session already has a coach.',
                    'coach_not_in_club' => 'Coach is not a club member.',
                    default => $exception->getMessage(),
                })
                ->danger()
                ->send();
        }
    }
}
