<?php

namespace Database\Seeders;

use App\Models\SaasPlan;
use Illuminate\Database\Seeder;

class SaasPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'description'   => 'Perfect for small clubs just getting started.',
                'monthly_price' => 49.00,
                'yearly_price'  => 490.00,
                'sort_order'    => 1,
                'features'      => [
                    'max_courts'              => '3',
                    'max_staff_users'         => '5',
                    'max_bookings_per_month'  => '100',
                    'academy_sessions'        => 'true',
                    'analytics'               => 'basic',
                ],
            ],
            [
                'name'          => 'Professional',
                'slug'          => 'professional',
                'description'   => 'For growing academies that need more power.',
                'monthly_price' => 99.00,
                'yearly_price'  => 990.00,
                'sort_order'    => 2,
                'features'      => [
                    'max_courts'              => '10',
                    'max_staff_users'         => '20',
                    'max_bookings_per_month'  => '500',
                    'academy_sessions'        => 'true',
                    'analytics'               => 'advanced',
                    'custom_branding'         => 'true',
                ],
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'description'   => 'Unlimited access for large multi-sport facilities.',
                'monthly_price' => 199.00,
                'yearly_price'  => 1990.00,
                'sort_order'    => 3,
                'features'      => [
                    'max_courts'              => 'unlimited',
                    'max_staff_users'         => 'unlimited',
                    'max_bookings_per_month'  => 'unlimited',
                    'academy_sessions'        => 'true',
                    'analytics'               => 'advanced',
                    'custom_branding'         => 'true',
                    'priority_support'        => 'true',
                    'api_access'              => 'true',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SaasPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
