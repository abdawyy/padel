<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExpireSaasSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-05-23 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_past_due_subscription_past_grace_becomes_expired_and_club_inactive(): void
    {
        $plan = SaasPlan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter-test',
            'monthly_price' => 49,
            'yearly_price' => 490,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $club = Club::factory()->create(['subscription_status' => 'active']);

        $subscription = ClubSaasSubscription::query()->create([
            'club_id' => $club->id,
            'saas_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount_paid' => 49,
            'starts_at' => '2026-04-01',
            'ends_at' => '2026-05-19',
            'status' => 'past_due',
        ]);

        Artisan::call('saas:expire-subscriptions');

        $subscription->refresh();
        $club->refresh();

        $this->assertSame('expired', $subscription->status);
        $this->assertSame('inactive', $club->subscription_status);
    }

    public function test_active_subscription_within_grace_becomes_past_due(): void
    {
        $plan = SaasPlan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-test',
            'monthly_price' => 99,
            'yearly_price' => 990,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $club = Club::factory()->create(['subscription_status' => 'active']);

        $subscription = ClubSaasSubscription::query()->create([
            'club_id' => $club->id,
            'saas_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount_paid' => 99,
            'starts_at' => '2026-05-01',
            'ends_at' => '2026-05-22',
            'status' => 'active',
        ]);

        Artisan::call('saas:expire-subscriptions');

        $subscription->refresh();
        $club->refresh();

        $this->assertSame('past_due', $subscription->status);
        $this->assertSame('active', $club->subscription_status);
    }

    public function test_active_subscription_past_grace_becomes_expired(): void
    {
        $plan = SaasPlan::query()->create([
            'name' => 'Elite',
            'slug' => 'elite-test',
            'monthly_price' => 199,
            'yearly_price' => 1990,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $club = Club::factory()->create(['subscription_status' => 'active']);

        $subscription = ClubSaasSubscription::query()->create([
            'club_id' => $club->id,
            'saas_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount_paid' => 199,
            'starts_at' => '2026-04-01',
            'ends_at' => '2026-05-15',
            'status' => 'active',
        ]);

        Artisan::call('saas:expire-subscriptions');

        $subscription->refresh();
        $club->refresh();

        $this->assertSame('expired', $subscription->status);
        $this->assertSame('inactive', $club->subscription_status);
    }

    public function test_trial_subscription_past_end_date_becomes_expired(): void
    {
        $club = Club::factory()->create(['subscription_status' => 'trial']);

        $subscription = ClubSaasSubscription::query()->create([
            'club_id' => $club->id,
            'saas_plan_id' => null,
            'billing_cycle' => 'monthly',
            'amount_paid' => 0,
            'starts_at' => '2026-05-01',
            'ends_at' => '2026-05-22',
            'status' => 'trial',
        ]);

        Artisan::call('saas:expire-subscriptions');

        $subscription->refresh();
        $club->refresh();

        $this->assertSame('expired', $subscription->status);
        $this->assertSame('inactive', $club->subscription_status);
    }
}
