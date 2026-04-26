<?php

namespace Database\Factories;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\Court;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademySession>
 */
class AcademySessionFactory extends Factory
{
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-15 days', '+45 days');
        $durationMinutes = fake()->randomElement([60, 90, 120]);
        $endTime = (clone $startTime)->modify("+{$durationMinutes} minutes");

        return [
            'club_id' => Club::factory(),
            'court_id' => Court::factory(),
            'coach_user_id' => null,
            'created_by_user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Beginner Padel Clinic',
                'Advanced Techniques',
                'Youth Training Session',
                'Pro Players Workshop',
                'Weekend Boot Camp',
                'Ladies Morning Session',
                'Mixed Doubles Practice',
                'Fitness & Padel',
            ]),
            'sport_type' => fake()->randomElement(['padel', 'tennis', 'squash']),
            'session_type' => fake()->randomElement(['group_training', 'private_lesson', 'clinic', 'tournament']),
            'skill_level' => fake()->optional()->randomElement(['beginner', 'intermediate', 'advanced', 'all_levels']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'max_players' => fake()->randomElement([4, 6, 8, 10, 12]),
            'price_per_player' => fake()->randomElement([20, 30, 40, 50, 75]),
            'status' => fake()->randomElement(['scheduled', 'scheduled', 'in_progress', 'completed', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
