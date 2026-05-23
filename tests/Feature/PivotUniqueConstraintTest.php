<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PivotUniqueConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_booking_participant_fails_at_database(): void
    {
        $booking = Booking::factory()->create();
        $user = User::factory()->create();

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount_due' => 10,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount_due' => 10,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_duplicate_club_user_fails_at_database(): void
    {
        $club = Club::factory()->create();
        $user = User::factory()->create();

        $club->users()->attach($user->id, ['role' => 'owner']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $club->users()->attach($user->id, ['role' => 'staff']);
    }
}
