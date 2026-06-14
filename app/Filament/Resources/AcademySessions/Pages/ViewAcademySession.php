<?php

namespace App\Filament\Resources\AcademySessions\Pages;

use App\Exceptions\BookingCancellationException;
use App\Filament\Resources\AcademySessions\AcademySessionResource;
use App\Filament\Resources\CoachApplications\CoachApplicationResource;
use App\Models\AcademySession;
use App\Services\AcademySessionCancellationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademySession extends ViewRecord
{
    protected static string $resource = AcademySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('coachApplications')
                ->label('Coach applications')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(fn (AcademySession $record): string => CoachApplicationResource::getUrl('index', [
                    'tableFilters' => [
                        'session' => ['value' => $record->id],
                    ],
                ])),
            $this->cancelSessionAction(),
            EditAction::make(),
        ];
    }

    private function cancelSessionAction(): Action
    {
        return Action::make('cancelSession')
            ->label('Cancel Session')
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
            });
    }
}
