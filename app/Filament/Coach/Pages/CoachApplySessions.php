<?php

namespace App\Filament\Coach\Pages;

use App\Models\AcademySession;
use App\Models\CoachApplication;
use App\Notifications\CoachApplicationSubmittedNotification;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoachApplySessions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Apply to Sessions';

    protected static ?string $title = 'Sessions Needing a Coach';

    protected static ?int $navigationSort = 3;

    public function getView(): string
    {
        return 'filament.coach.pages.coach-apply-sessions';
    }

    public function table(Table $table): Table
    {
        $coachId = auth()->id();
        $clubIds = auth()->user()?->clubs()->pluck('clubs.id') ?? collect();

        return $table
            ->query(
                AcademySession::query()
                    ->whereNull('coach_user_id')
                    ->whereIn('status', ['scheduled', 'active'])
                    ->where('start_time', '>', now())
                    ->whereIn('club_id', $clubIds)
                    ->with(['club', 'court'])
            )
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('club.name')->label('Club'),
                TextColumn::make('court.name')->label('Court'),
                TextColumn::make('start_time')->dateTime('D, d M Y H:i')->sortable(),
                TextColumn::make('session_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('application_status')
                    ->label('Your application')
                    ->badge()
                    ->placeholder('Not applied')
                    ->getStateUsing(fn (AcademySession $record): ?string => CoachApplication::query()
                        ->where('academy_session_id', $record->id)
                        ->where('coach_user_id', $coachId)
                        ->value('status'))
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'declined' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('apply')
                    ->label('Apply')
                    ->visible(fn (AcademySession $record): bool => ! CoachApplication::query()
                        ->where('academy_session_id', $record->id)
                        ->where('coach_user_id', $coachId)
                        ->exists())
                    ->action(fn (AcademySession $record) => $this->applyToSession($record)),
                \Filament\Actions\Action::make('withdraw')
                    ->label('Withdraw')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (AcademySession $record): bool => CoachApplication::query()
                        ->where('academy_session_id', $record->id)
                        ->where('coach_user_id', $coachId)
                        ->where('status', 'pending')
                        ->exists())
                    ->action(fn (AcademySession $record) => $this->withdrawApplication($record)),
            ])
            ->defaultSort('start_time');
    }

    private function applyToSession(AcademySession $session): void
    {
        $user = auth()->user();

        if (! $user->belongsToClub($session->club)) {
            Notification::make()->title('Not a club member')->danger()->send();

            return;
        }

        if (CoachApplication::query()
            ->where('academy_session_id', $session->id)
            ->where('coach_user_id', $user->id)
            ->exists()) {
            Notification::make()->title('Already applied')->warning()->send();

            return;
        }

        $application = CoachApplication::query()->create([
            'academy_session_id' => $session->id,
            'coach_user_id' => $user->id,
            'status' => 'pending',
        ]);

        $application->load('coach');
        $session->load('club');

        $session->club?->users()
            ->wherePivotIn('role', ['owner', 'manager'])
            ->get()
            ->each(fn ($manager) => $manager->notify(
                new CoachApplicationSubmittedNotification($application, $session)
            ));

        Notification::make()->title('Application submitted')->success()->send();
    }

    private function withdrawApplication(AcademySession $session): void
    {
        $deleted = CoachApplication::query()
            ->where('academy_session_id', $session->id)
            ->where('coach_user_id', auth()->id())
            ->where('status', 'pending')
            ->delete();

        if ($deleted) {
            Notification::make()->title('Application withdrawn')->success()->send();
        } else {
            Notification::make()->title('Nothing to withdraw')->warning()->send();
        }
    }
}
