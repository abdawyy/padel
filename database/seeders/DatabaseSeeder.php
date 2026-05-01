<?php

namespace Database\Seeders;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Club;
use App\Models\Court;
use App\Models\CourtSlot;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── SaaS Plans ───────────────────────────────────────────────
        $this->call(SaasPlanSeeder::class);

        // ── Super Admin ──────────────────────────────────────────────
        User::factory()->create([
            'name'     => 'Super Admin',
            'email'    => 'admin@padel.test',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
            'is_active' => true,
        ]);

        // ── Coaches (10) ─────────────────────────────────────────────
        $coaches = User::factory(10)->create([
            'role'      => 'coach',
            'is_active' => true,
        ]);

        // ── Players (50) ─────────────────────────────────────────────
        $players = User::factory(50)->create([
            'role'      => 'player',
            'is_active' => true,
        ]);

        // ── Clubs (5) ────────────────────────────────────────────────
        $clubs = Club::factory(5)->create();

        // Attach the super admin + coaches to each club
        $adminUser = User::where('email', 'admin@padel.test')->first();

        foreach ($clubs as $club) {
            $club->users()->attach($adminUser->id, ['role' => 'owner']);

            // Assign 2 coaches per club as staff
            $coaches->random(2)->each(function ($coach) use ($club) {
                $club->users()->syncWithoutDetaching([$coach->id => ['role' => 'staff']]);
            });

            // ── Courts (3–4 per club) ─────────────────────────────
            $courts = Court::factory(rand(3, 4))->create([
                'club_id'    => $club->id,
                'sport_type' => $club->sport_type,
            ]);

            foreach ($courts as $court) {
                // ── Court Slots (5–8 per court) ───────────────────
                $slotCoach = $coaches->random();
                CourtSlot::factory(rand(5, 8))->create([
                    'court_id'      => $court->id,
                    'sport_type'    => $court->sport_type,
                    'coach_user_id' => $slotCoach->id,
                ]);

                // ── Bookings (8–15 per court) ─────────────────────
                Booking::factory(rand(8, 15))->create([
                    'court_id'       => $court->id,
                    'sport_type'     => $court->sport_type,
                    'owner_user_id'  => $players->random()->id,
                    'coach_user_id'  => fake()->boolean(30) ? $coaches->random()->id : null,
                ]);
            }

            // ── Academy Sessions (6–10 per club) ─────────────────
            AcademySession::factory(rand(6, 10))->create([
                'club_id'             => $club->id,
                'court_id'            => $courts->random()->id,
                'sport_type'          => $club->sport_type,
                'coach_user_id'       => $coaches->random()->id,
                'created_by_user_id'  => $adminUser->id,
            ]);

            // ── Packages (2–3 per club) ───────────────────────────
            $packages = Package::factory(rand(2, 3))->create([
                'club_id' => $club->id,
            ]);

            foreach ($packages as $package) {
                // Assign 4–10 random players with subscription details
                $players->random(rand(4, 10))->each(function ($player) use ($package) {
                    $startsAt  = Carbon::today()->subDays(rand(0, 60));
                    $expiresAt = $startsAt->copy()->addDays(
                        $package->duration_days ?? match ($package->type) {
                            'monthly'   => 30,
                            'quarterly' => 90,
                            'yearly'    => 365,
                            'sessions'  => 60,
                            default     => 30,
                        }
                    );

                    $status = $expiresAt->isPast() ? 'expired' : 'active';

                    $package->subscribers()->attach($player->id, [
                        'starts_at'          => $startsAt->toDateString(),
                        'expires_at'         => $expiresAt->toDateString(),
                        'sessions_remaining' => $package->session_count,
                        'status'             => $status,
                        'notes'              => null,
                    ]);
                });
            }
        }
    }
}

