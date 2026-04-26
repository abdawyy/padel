<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['sessions', 'monthly', 'quarterly', 'yearly', 'custom']);

        return [
            'club_id'       => Club::factory(),
            'name'          => fake()->randomElement([
                'Beginner Starter Pack',
                'Pro Monthly',
                'Academy Yearly',
                'Summer Intensive',
                'Weekend Warrior',
                'Elite Training Pack',
                'Junior Academy',
                'Open Court Pass',
            ]),
            'sport_type'    => fake()->randomElement(['padel', 'tennis', 'squash']),
            'type'          => $type,
            'session_count' => $type === 'sessions' ? fake()->numberBetween(5, 30) : null,
            'duration_days' => $type === 'custom' ? fake()->randomElement([14, 30, 60, 90]) : null,
            'price'         => fake()->randomElement([100, 200, 350, 500, 750, 1000, 1500]),
            'description'   => fake()->optional()->sentence(),
            'is_active'     => fake()->boolean(85),
        ];
    }
}
