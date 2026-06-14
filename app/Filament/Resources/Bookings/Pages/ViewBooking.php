<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Exceptions\BookingCancellationException;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use App\Services\BookingCancellationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->cancelBookingAction(),
            EditAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    private function cancelBookingAction(): Action
    {
        return Action::make('cancelBooking')
            ->label('Cancel Booking')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Booking $record): bool => in_array($record->status, ['pending', 'confirmed'], true))
            ->action(function (Booking $record): void {
                try {
                    app(BookingCancellationService::class)->cancel($record, auth()->user());

                    Notification::make()
                        ->title('Booking cancelled')
                        ->success()
                        ->send();
                } catch (BookingCancellationException $exception) {
                    Notification::make()
                        ->title('Cancellation failed')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
