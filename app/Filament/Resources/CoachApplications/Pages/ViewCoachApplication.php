<?php

namespace App\Filament\Resources\CoachApplications\Pages;

use App\Filament\Resources\AcademySessions\AcademySessionResource;
use App\Filament\Resources\CoachApplications\CoachApplicationResource;
use App\Models\CoachApplication;
use App\Services\CoachApplicationResponseService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewCoachApplication extends ViewRecord
{
    protected static string $resource = CoachApplicationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('session.title')->label('Session'),
            TextEntry::make('session.club.name')->label('Club'),
            TextEntry::make('coach.name')->label('Coach'),
            TextEntry::make('coach.email'),
            TextEntry::make('status')->badge(),
            TextEntry::make('message')->placeholder('-')->columnSpanFull(),
            TextEntry::make('response_note')->placeholder('-')->columnSpanFull(),
            TextEntry::make('responded_at')->dateTime()->placeholder('-'),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewSession')
                ->label('View session')
                ->url(fn (CoachApplication $record): string => AcademySessionResource::getUrl('view', ['record' => $record->academy_session_id])),
            Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (CoachApplication $record): bool => $record->isPending())
                ->action(fn (CoachApplication $record) => $this->respond($record, 'accepted')),
            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->form([Textarea::make('response_note')->maxLength(500)])
                ->visible(fn (CoachApplication $record): bool => $record->isPending())
                ->action(fn (CoachApplication $record, array $data) => $this->respond($record, 'declined', $data['response_note'] ?? null)),
        ];
    }

    private function respond(CoachApplication $record, string $status, ?string $note = null): void
    {
        try {
            app(CoachApplicationResponseService::class)->respond($record, auth()->user(), $status, $note);
            Notification::make()->title('Application '.$status)->success()->send();
        } catch (\RuntimeException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }
}
