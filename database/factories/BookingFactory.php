<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-30 days', '+30 days');
        $durationMinutes = fake()->randomElement([60, 90, 120]);
        $endTime = (clone $startTime)->modify("+{$durationMinutes} minutes");
        $pricePerHour = fake()->randomElement([50, 75, 100, 120, 150]);
        $totalPrice = $pricePerHour * ($durationMinutes / 60);

        return [
            'court_id' => Court::factory(),
            'sport_type' => fake()->randomElement(['padel', 'tennis', 'squash']),
            'owner_user_id' => User::factory(),
            'coach_user_id' => null,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_price' => $totalPrice,
            'coach_fee' => 0,
            'match_type' => fake()->randomElement(['private', 'open_match']),
            'session_type' => fake()->randomElement(['standard', 'coached', 'academy']),
            'max_players' => fake()->randomElement([2, 4]),
            'skill_level' => fake()->optional()->randomElement(['beginner', 'intermediate', 'advanced']),
            'status' => fake()->randomElement(['confirmed', 'confirmed', 'confirmed', 'pending', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
