<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    public function definition(): array
    {
        $cities = ['Dubai', 'Abu Dhabi', 'Riyadh', 'Doha', 'Kuwait City', 'Casablanca', 'Cairo', 'Tunis'];

        return [
            'name' => fake()->company() . ' Padel Club',
            'sport_type' => fake()->randomElement(['padel', 'tennis', 'squash']),
            'address' => fake()->streetAddress() . ', ' . fake()->randomElement($cities),
            'subscription_status' => fake()->randomElement(['active', 'inactive', 'trial']),
            'settings' => [
                'currency' => fake()->randomElement(['USD', 'AED', 'SAR', 'QAR']),
                'timezone' => fake()->randomElement(['Asia/Dubai', 'Asia/Riyadh', 'Africa/Cairo']),
                'opening_hour' => 6,
                'closing_hour' => 23,
            ],
        ];
    }
}
