<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed fixed admin / manager / owner accounts so every portal is
     * immediately accessible without guessing random factory emails.
     *
     * Accounts created:
     *  Super-admin    admin@padel.test         / password
     *  SaaS viewer    saas@padel.test          / password  (super_admin role → /saas panel)
     *
     *  Per academy (3 demo clubs):
     *    owner        owner@{slug}.test        / password  (academy_admin → /admin panel)
     *    manager      manager@{slug}.test      / password  (academy_admin → /admin panel)
     *    head coach   coach@{slug}.test        / password  (coach → /coach panel)
     *    player       player@{slug}.test       / password  (player → /player panel)
     */
    public function run(): void
    {
        // ── Super admin (SaaS portal) ────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@padel.test'],
            [
                'name'       => 'Super Admin',
                'password'   => Hash::make('password'),
                'role'       => 'super_admin',
                'is_active'  => true,
                'skill_level'=> 3,
            ]
        );

        // ── Second SaaS-only account ─────────────────────────────────
        User::firstOrCreate(
            ['email' => 'saas@padel.test'],
            [
                'name'       => 'SaaS Owner',
                'password'   => Hash::make('password'),
                'role'       => 'super_admin',
                'is_active'  => true,
                'skill_level'=> 3,
            ]
        );

        $plan = SaasPlan::first();

        // ── Demo clubs ───────────────────────────────────────────────
        $demos = [
            [
                'name'       => 'Cairo Padel Club',
                'slug'       => 'cairo',
                'sport_type' => 'padel',
                'address'    => '5 Tahrir Square, Cairo, Egypt',
                'sport_rules'=> [
                    'padel' => ['max_players' => 4, 'match_duration_minutes' => 90],
                ],
            ],
            [
                'name'       => 'Alexandria Tennis Academy',
                'slug'       => 'alex',
                'sport_type' => 'tennis',
                'address'    => '12 Corniche Road, Alexandria, Egypt',
                'sport_rules'=> [
                    'tennis' => ['max_players' => 2, 'match_duration_minutes' => 60],
                ],
            ],
            [
                'name'       => 'Giza Sports Hub',
                'slug'       => 'giza',
                'sport_type' => 'padel',
                'address'    => '88 Pyramids Street, Giza, Egypt',
                'sport_rules'=> [
                    'padel'      => ['max_players' => 4, 'match_duration_minutes' => 90],
                    'pickleball' => ['max_players' => 4, 'match_duration_minutes' => 60],
                ],
            ],
        ];

        foreach ($demos as $data) {
            $slug = $data['slug'];

            // ── Club ─────────────────────────────────────────────────
            $club = Club::firstOrCreate(
                ['name' => $data['name']],
                [
                    'sport_type'          => $data['sport_type'],
                    'address'             => $data['address'],
                    'sport_rules'         => $data['sport_rules'],
                    'subscription_status' => 'active',
                    'registration_status' => 'approved',
                    'approved_at'         => now(),
                    'approved_by'         => $superAdmin->id,
                ]
            );

            // ── Active SaaS subscription ──────────────────────────────
            if ($plan && ! $club->saasSubscriptions()->where('status', 'active')->exists()) {
                ClubSaasSubscription::create([
                    'club_id'       => $club->id,
                    'saas_plan_id'  => $plan->id,
                    'billing_cycle' => 'monthly',
                    'amount_paid'   => $plan->monthly_price,
                    'starts_at'     => Carbon::today()->toDateString(),
                    'ends_at'       => Carbon::today()->addMonth()->toDateString(),
                    'status'        => 'active',
                    'notes'         => 'Seeded demo subscription.',
                ]);
            }

            // ── Owner (academy_admin) ─────────────────────────────────
            $owner = User::firstOrCreate(
                ['email' => "owner@{$slug}.test"],
                [
                    'name'       => ucfirst($slug) . ' Club Owner',
                    'password'   => Hash::make('password'),
                    'role'       => 'academy_admin',
                    'is_active'  => true,
                    'phone'      => '+201000' . rand(100000, 999999),
                    'skill_level'=> 3,
                ]
            );
            $club->users()->syncWithoutDetaching([$owner->id => ['role' => 'owner']]);

            // ── Manager (academy_admin) ───────────────────────────────
            $manager = User::firstOrCreate(
                ['email' => "manager@{$slug}.test"],
                [
                    'name'       => ucfirst($slug) . ' Club Manager',
                    'password'   => Hash::make('password'),
                    'role'       => 'academy_admin',
                    'is_active'  => true,
                    'phone'      => '+201001' . rand(100000, 999999),
                    'skill_level'=> 3,
                ]
            );
            $club->users()->syncWithoutDetaching([$manager->id => ['role' => 'manager']]);

            // ── Head coach ────────────────────────────────────────────
            $coach = User::firstOrCreate(
                ['email' => "coach@{$slug}.test"],
                [
                    'name'       => ucfirst($slug) . ' Head Coach',
                    'password'   => Hash::make('password'),
                    'role'       => 'coach',
                    'is_active'  => true,
                    'phone'      => '+201002' . rand(100000, 999999),
                    'skill_level'=> 5,
                ]
            );
            $club->users()->syncWithoutDetaching([$coach->id => ['role' => 'staff']]);

            // ── Demo player ───────────────────────────────────────────
            User::firstOrCreate(
                ['email' => "player@{$slug}.test"],
                [
                    'name'       => ucfirst($slug) . ' Demo Player',
                    'password'   => Hash::make('password'),
                    'role'       => 'player',
                    'is_active'  => true,
                    'phone'      => '+201003' . rand(100000, 999999),
                    'skill_level'=> rand(1, 4),
                    'preferred_sport' => $data['sport_type'],
                ]
            );
        }

        $this->command->info('✅  Admin seeder complete.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin (SaaS)',     'admin@padel.test',        'password'],
                ['SaaS Owner',             'saas@padel.test',         'password'],
                ['Cairo Owner',            'owner@cairo.test',        'password'],
                ['Cairo Manager',          'manager@cairo.test',      'password'],
                ['Cairo Coach',            'coach@cairo.test',        'password'],
                ['Cairo Player',           'player@cairo.test',       'password'],
                ['Alex Owner',             'owner@alex.test',         'password'],
                ['Alex Manager',           'manager@alex.test',       'password'],
                ['Alex Coach',             'coach@alex.test',         'password'],
                ['Alex Player',            'player@alex.test',        'password'],
                ['Giza Owner',             'owner@giza.test',         'password'],
                ['Giza Manager',           'manager@giza.test',       'password'],
                ['Giza Coach',             'coach@giza.test',         'password'],
                ['Giza Player',            'player@giza.test',        'password'],
            ]
        );
    }
}
