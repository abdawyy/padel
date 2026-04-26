<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Court;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
class CourtFactory extends Factory
{
    public function definition(): array
    {
        $sportType = fake()->randomElement(['padel', 'tennis', 'squash']);

        return [
            'club_id' => Club::factory(),
            'sport_type' => $sportType,
            'name' => 'Court ' . fake()->numberBetween(1, 20),
            'type' => fake()->randomElement(['indoor', 'outdoor']),
            'price_per_hour' => fake()->randomElement([50, 75, 100, 120, 150, 200]),
            'capacity' => $sportType === 'squash' ? 2 : 4,
            'slot_duration_minutes' => fake()->randomElement([60, 90]),
            'is_active' => fake()->boolean(90),
        ];
    }
}
