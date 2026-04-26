<?php

namespace Database\Factories;

use App\Models\Court;
use App\Models\CourtSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourtSlot>
 */
class CourtSlotFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->numberBetween(7, 20);
        $duration = fake()->randomElement([60, 90]);
        $endHour = $startHour + intdiv($duration, 60);
        $endMinute = $duration % 60;

        return [
            'court_id' => Court::factory(),
            'title' => fake()->randomElement(['Morning Training', 'Evening Session', 'Weekend Match', 'Pro Session', 'Open Play', 'Beginner Class']),
            'sport_type' => fake()->randomElement(['padel', 'tennis', 'squash']),
            'slot_type' => fake()->randomElement(['training', 'match', 'open_play', 'academy']),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:%02d:00', $endHour, $endMinute),
            'coach_user_id' => null,
            'max_players' => fake()->randomElement([2, 4]),
            'price' => fake()->randomElement([30, 50, 75, 100]),
            'skill_level' => fake()->optional()->randomElement(['beginner', 'intermediate', 'advanced']),
            'is_active' => fake()->boolean(85),
        ];
    }
}
