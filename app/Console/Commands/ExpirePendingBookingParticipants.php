<?php

namespace App\Console\Commands;

use App\Services\BookingParticipantCapacity;
use Illuminate\Console\Command;

class ExpirePendingBookingParticipants extends Command
{
    protected $signature = 'bookings:expire-pending-participants';

    protected $description = 'Remove abandoned pending open-match participant rows past the payment hold window';

    public function handle(): int
    {
        $removed = BookingParticipantCapacity::expireStalePendingParticipants();

        $this->info("Removed {$removed} expired pending participant(s).");

        return self::SUCCESS;
    }
}
