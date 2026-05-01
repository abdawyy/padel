<?php

namespace Database\Seeders;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Club;
use App\Models\CoachApplication;
use App\Models\Court;
use App\Models\PaymentTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewFlowsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Grab seeded demo accounts ────────────────────────────────
        $demos = [
            'cairo' => Club::where('name', 'Cairo Padel Club')->first(),
            'alex'  => Club::where('name', 'Alexandria Tennis Academy')->first(),
            'giza'  => Club::where('name', 'Giza Sports Hub')->first(),
        ];

        $coaches = User::where('role', 'coach')->get();
        $players = User::where('role', 'player')->get();

        // ── Flow 7: Skill matchmaking ─────────────────────────────────
        // Seed open-match bookings with skill ranges so the filter API
        // returns meaningful results.
        $this->seedSkillMatchBookings($demos, $players, $coaches);

        // ── Flow 6: Coach applications ────────────────────────────────
        // Some sessions have no coach yet → coaches apply → some accepted.
        $this->seedCoachApplications($demos, $coaches);

        // ── Flow 5: Session enrollment payment ───────────────────────
        // Players already enrolled (paid) + players pending payment.
        $this->seedSessionEnrollments($demos, $players);

        // ── Flow 9: Multi-sport rules already on clubs via AdminSeeder ─
        // Add sport_rules to any factory-created clubs that are missing them.
        $this->patchSportRules();

        $this->command->info('✅  NewFlowsSeeder complete.');
    }

    // ──────────────────────────────────────────────────────────────────
    // Flow 7 – Skill-ranged open-match bookings
    // ──────────────────────────────────────────────────────────────────
    private function seedSkillMatchBookings(array $demos, $players, $coaches): void
    {
        $skillBrackets = [
            ['label' => 'Beginners Only',        'min' => 1, 'max' => 2],
            ['label' => 'Intermediate',          'min' => 2, 'max' => 4],
            ['label' => 'Advanced Players',      'min' => 4, 'max' => 5],
            ['label' => 'All Welcome',           'min' => 1, 'max' => 5],
            ['label' => 'Competitive (Adv+)',    'min' => 3, 'max' => 5],
        ];

        foreach ($demos as $slug => $club) {
            if (! $club) {
                continue;
            }

            $court = Court::where('club_id', $club->id)->first();
            if (! $court) {
                continue;
            }

            foreach ($skillBrackets as $i => $bracket) {
                $start = Carbon::now()->addDays($i + 1)->setTime(18, 0);
                $end   = $start->copy()->addMinutes(90);
                $owner = $players->random();

                $booking = Booking::create([
                    'court_id'       => $court->id,
                    'sport_type'     => $club->sport_type,
                    'owner_user_id'  => $owner->id,
                    'coach_user_id'  => fake()->boolean(40) ? $coaches->random()->id : null,
                    'start_time'     => $start,
                    'end_time'       => $end,
                    'total_price'    => 120.00,
                    'coach_fee'      => 0,
                    'match_type'     => 'open_match',
                    'session_type'   => 'standard',
                    'max_players'    => 4,
                    'skill_min'      => $bracket['min'],
                    'skill_max'      => $bracket['max'],
                    'status'         => 'confirmed',
                    'notes'          => $bracket['label'],
                ]);

                // Add 1-2 participants who match the skill range
                $eligible = $players->filter(fn ($p) =>
                    $p->skill_level >= $bracket['min'] && $p->skill_level <= $bracket['max']
                )->take(2);

                foreach ($eligible as $participant) {
                    \Illuminate\Support\Facades\DB::table('booking_participants')->insertOrIgnore([
                        'booking_id'     => $booking->id,
                        'user_id'        => $participant->id,
                        'payment_status' => 'paid',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }

        $this->command->info('  → Flow 7: skill-ranged open-match bookings seeded.');
    }

    // ──────────────────────────────────────────────────────────────────
    // Flow 6 – Coach applications
    // ──────────────────────────────────────────────────────────────────
    private function seedCoachApplications(array $demos, $coaches): void
    {
        foreach ($demos as $slug => $club) {
            if (! $club) {
                continue;
            }

            // Pick sessions that have no coach assigned
            $openSessions = AcademySession::where('club_id', $club->id)
                ->whereNull('coach_user_id')
                ->whereIn('status', ['scheduled', 'active'])
                ->take(3)
                ->get();

            foreach ($openSessions as $session) {
                // 3 coaches apply; first gets accepted, others declined
                $applicants = $coaches->random(min(3, $coaches->count()));

                foreach ($applicants as $index => $coach) {
                    $isFirst = $index === 0;

                    CoachApplication::create([
                        'academy_session_id' => $session->id,
                        'coach_user_id'      => $coach->id,
                        'status'             => $isFirst ? 'accepted' : 'declined',
                        'message'            => fake()->sentence(),
                        'response_note'      => $isFirst
                            ? 'Great experience, welcome aboard!'
                            : 'We have already filled this slot.',
                        'responded_at'       => now(),
                    ]);
                }

                // Assign the accepted coach to the session
                $session->update(['coach_user_id' => $applicants->first()->id]);
            }

            // Also seed a session with PENDING applications (realistic open state)
            $pendingSession = AcademySession::where('club_id', $club->id)
                ->whereNull('coach_user_id')
                ->whereIn('status', ['scheduled'])
                ->skip(3)
                ->first();

            if ($pendingSession) {
                $coaches->random(min(2, $coaches->count()))->each(function ($coach) use ($pendingSession) {
                    CoachApplication::firstOrCreate(
                        ['academy_session_id' => $pendingSession->id, 'coach_user_id' => $coach->id],
                        [
                            'status'  => 'pending',
                            'message' => fake()->sentence(),
                        ]
                    );
                });
            }
        }

        $this->command->info('  → Flow 6: coach applications seeded.');
    }

    // ──────────────────────────────────────────────────────────────────
    // Flow 5 – Session enrollment + payment records
    // ──────────────────────────────────────────────────────────────────
    private function seedSessionEnrollments(array $demos, $players): void
    {
        foreach ($demos as $slug => $club) {
            if (! $club) {
                continue;
            }

            $paidSessions = AcademySession::where('club_id', $club->id)
                ->where('price_per_player', '>', 0)
                ->whereIn('status', ['scheduled', 'active'])
                ->take(3)
                ->get();

            foreach ($paidSessions as $session) {
                // 3 players fully paid & enrolled
                $enrolled = $players->random(3);
                foreach ($enrolled as $player) {
                    $alreadyIn = $session->players()->where('users.id', $player->id)->exists();
                    if (! $alreadyIn) {
                        $session->players()->attach($player->id, [
                            'status' => 'registered',
                            'notes'  => 'Enrolled via payment.',
                        ]);
                    }

                    // Payment transaction record
                    PaymentTransaction::firstOrCreate(
                        [
                            'paymob_transaction_id' => 'SEED_S' . $session->id . '_U' . $player->id,
                        ],
                        [
                            'booking_id' => null,
                            'user_id'    => $player->id,
                            'amount'     => $session->price_per_player,
                            'status'     => 'success',
                            'provider_payload' => [
                                'seeded'       => true,
                                'session_id'   => $session->id,
                                'merchant_order_id' => 'session_' . $session->id . '_user_' . $player->id,
                            ],
                        ]
                    );
                }

                // 1 player pending payment (applied but not paid yet)
                $pendingPlayer = $players->whereNotIn('id', $enrolled->pluck('id'))->random();
                // Not enrolled — just a transaction record showing pending
                PaymentTransaction::firstOrCreate(
                    [
                        'paymob_transaction_id' => 'SEED_PEND_S' . $session->id . '_U' . $pendingPlayer->id,
                    ],
                    [
                        'booking_id' => null,
                        'user_id'    => $pendingPlayer->id,
                        'amount'     => $session->price_per_player,
                        'status'     => 'pending',
                        'provider_payload' => [
                            'seeded'       => true,
                            'session_id'   => $session->id,
                            'merchant_order_id' => 'session_' . $session->id . '_user_' . $pendingPlayer->id,
                        ],
                    ]
                );
            }
        }

        $this->command->info('  → Flow 5: session enrollments + payment records seeded.');
    }

    // ──────────────────────────────────────────────────────────────────
    // Flow 9 – Patch sport_rules on factory-created clubs
    // ──────────────────────────────────────────────────────────────────
    private function patchSportRules(): void
    {
        $defaults = [
            'padel'      => ['max_players' => 4, 'match_duration_minutes' => 90,  'court_dimensions' => '20x10'],
            'tennis'     => ['max_players' => 2, 'match_duration_minutes' => 60,  'court_dimensions' => '23.77x8.23'],
            'squash'     => ['max_players' => 2, 'match_duration_minutes' => 45,  'court_dimensions' => '9.75x6.4'],
            'pickleball' => ['max_players' => 4, 'match_duration_minutes' => 60,  'court_dimensions' => '13.41x6.1'],
        ];

        Club::whereNull('sport_rules')->each(function (Club $club) use ($defaults) {
            $sport = $club->sport_type ?? 'padel';
            $club->update([
                'sport_rules' => [$sport => $defaults[$sport] ?? $defaults['padel']],
            ]);
        });

        $this->command->info('  → Flow 9: sport_rules patched on all clubs.');
    }
}
