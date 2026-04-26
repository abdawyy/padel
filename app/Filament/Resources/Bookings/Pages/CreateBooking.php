<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected array $participantIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->participantIds = $this->extractParticipantIds($data);

        $this->guardParticipantCapacity(
            ownerUserId: (int) $data['owner_user_id'],
            maxPlayers: (int) $data['max_players'],
        );

        unset($data['participant_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncParticipants($this->record);
    }

    private function extractParticipantIds(array $data): array
    {
        return collect($data['participant_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function guardParticipantCapacity(int $ownerUserId, int $maxPlayers): void
    {
        $totalPlayers = collect($this->participantIds)
            ->push($ownerUserId)
            ->unique()
            ->count();

        if ($totalPlayers > $maxPlayers) {
            throw ValidationException::withMessages([
                'participant_ids' => 'The selected players exceed the maximum allowed players for this match.',
            ]);
        }
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
