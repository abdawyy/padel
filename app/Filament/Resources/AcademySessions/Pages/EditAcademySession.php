<?php

namespace App\Filament\Resources\AcademySessions\Pages;

use App\Exceptions\BookingCancellationException;
use App\Filament\Resources\AcademySessions\AcademySessionResource;
use App\Models\AcademySession;
use App\Services\AcademySessionCancellationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAcademySession extends EditRecord
{
    protected static string $resource = AcademySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->cancelSessionAction(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    private function cancelSessionAction(): Action
    {
        return Action::make('cancelSession')
            ->label('Cancel Session')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (): bool => in_array($this->record->status, ['scheduled', 'active'], true))
            ->action(function (): void {
                try {
                    app(AcademySessionCancellationService::class)->cancel($this->record, auth()->user());

                    Notification::make()->title('Session cancelled')->success()->send();
                } catch (BookingCancellationException $exception) {
                    Notification::make()->title('Cancellation failed')->body($exception->getMessage())->danger()->send();
                }
            });
    }
}
