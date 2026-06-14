<?php

namespace Tests\Unit;

use App\Models\AcademySession;
use App\Models\Booking;
use Carbon\Carbon;
use Tests\TestCase;

class DisplayStatusTest extends TestCase
{
    public function test_past_confirmed_booking_displays_as_completed(): void
    {
        $booking = new Booking([
            'status' => 'confirmed',
            'start_time' => Carbon::now()->subDays(2),
            'end_time' => Carbon::now()->subDays(2)->addHour(),
        ]);

        $this->assertSame('completed', $booking->display_status);
    }

    public function test_active_session_displays_as_ongoing(): void
    {
        $session = new AcademySession(['status' => 'active']);

        $this->assertSame('ongoing', $session->display_status);
        $this->assertSame('Ongoing', $session->display_status_label);
    }
}
