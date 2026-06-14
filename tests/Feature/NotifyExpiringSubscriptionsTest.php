<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
use App\Models\User;
use App\Notifications\SubscriptionExpiringNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyExpiringSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-05-23 09:00:00');
        Notification::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_notify_expiring_sends_only_to_club_owners(): void
    {
        $plan = SaasPlan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter-notify',
            'monthly_price' => 49,
            'yearly_price' => 490,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $club = Club::factory()->create();
        $owner = User::factory()->create();
        $manager = User::factory()->create();

        $club->users()->attach($owner->id, ['role' => 'owner']);
        $club->users()->attach($manager->id, ['role' => 'manager']);

        ClubSaasSubscription::query()->create([
            'club_id' => $club->id,
            'saas_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount_paid' => 49,
            'starts_at' => '2026-05-01',
            'ends_at' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        Artisan::call('saas:notify-expiring');

        Notification::assertSentTo($owner, SubscriptionExpiringNotification::class);
        Notification::assertNotSentTo($manager, SubscriptionExpiringNotification::class);
    }
}
