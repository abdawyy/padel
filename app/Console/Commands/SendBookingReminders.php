<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendBookingReminders extends Command
{
    protected $signature = 'booking:remind';

    protected $description = 'Send booking reminder emails 24h and 2h before start time';

    public function handle(): int
    {
        $windows = [
            24 => [now()->addHours(23), now()->addHours(25)],
            2 => [now()->addHours(1)->addMinutes(45), now()->addHours(2)->addMinutes(15)],
        ];

        $sent = 0;

        foreach ($windows as $hours => [$from, $to]) {
            $bookings = Booking::query()
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereBetween('start_time', [$from, $to])
                ->with(['court.club', 'participants', 'owner'])
                ->get();

            foreach ($bookings as $booking) {
                $cacheKey = "booking_reminder:{$booking->id}:{$hours}h";

                if (Cache::has($cacheKey)) {
                    continue;
                }

                $userIds = $booking->participants->pluck('id')->push($booking->owner_user_id)->unique();

                User::query()->whereIn('id', $userIds)->get()->each(
                    fn (User $user) => $user->notify(new BookingReminderNotification($booking, $hours))
                );

                Cache::put($cacheKey, true, $booking->start_time);
                $sent++;
            }
        }

        $this->info("Sent reminders for {$sent} booking window(s).");

        return self::SUCCESS;
    }
}
