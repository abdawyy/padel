<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected array $participantIds = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['participant_ids'] = $this->record
            ->participants()
            ->pluck('users.id')
            ->reject(fn ($id) => (int) $id === (int) $this->record->owner_user_id)
            ->values()
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->participantIds = collect($data['participant_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $totalPlayers = collect($this->participantIds)
            ->push((int) $data['owner_user_id'])
            ->unique()
            ->count();

        if ($totalPlayers > (int) $data['max_players']) {
            throw ValidationException::withMessages([
                'participant_ids' => 'The selected players exceed the maximum allowed players for this match.',
            ]);
        }

        unset($data['participant_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncParticipants($this->record);
    }

    private function syncParticipants(Model $record): void
    {
        if (! $record instanceof Booking) {
            return;
        }

        $participantIds = collect($this->participantIds)
            ->push((int) $record->owner_user_id)
            ->unique()
            ->values();

        $amountDue = round(((float) $record->total_price) / max($participantIds->count(), 1), 2);

        $record->participants()->sync(
            $participantIds
                ->mapWithKeys(fn (int $participantId) => [$participantId => [
                    'amount_due' => $amountDue,
                    'payment_status' => 'pending',
                ]])
                ->all(),
        );
    }
}
