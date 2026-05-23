<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class P3BugsTest extends TestCase
{
    use RefreshDatabase;

    public function test_club_destroy_returns_empty_204(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $club = Club::factory()->create();
        $club->users()->attach($manager->id, ['role' => 'owner']);

        Sanctum::actingAs($manager);

        $this->deleteJson("/api/clubs/{$club->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('clubs', ['id' => $club->id]);
    }

    public function test_user_bookings_rejects_invalid_type(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Sanctum::actingAs($user);

        $this->getJson('/api/user/bookings?type=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_my_sessions_rejects_invalid_type(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Sanctum::actingAs($user);

        $this->getJson('/api/user/academy-sessions?type=soon')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_public_index_excludes_yesterdays_sessions(): void
    {
        $creator = User::factory()->create();
        $club = Club::factory()->create([
            'registration_status' => 'approved',
            'subscription_status' => 'active',
        ]);

        AcademySession::factory()->create([
            'club_id' => $club->id,
            'created_by_user_id' => $creator->id,
            'status' => 'scheduled',
            'start_time' => Carbon::yesterday()->setTime(18, 0),
            'end_time' => Carbon::yesterday()->setTime(19, 0),
            'title' => 'Yesterday Session',
        ]);

        AcademySession::factory()->create([
            'club_id' => $club->id,
            'created_by_user_id' => $creator->id,
            'status' => 'scheduled',
            'start_time' => Carbon::now()->addHours(2),
            'end_time' => Carbon::now()->addHours(3),
            'title' => 'Future Session',
        ]);

        $titles = collect($this->getJson('/api/academy-sessions')->json('data'))->pluck('title')->all();

        $this->assertNotContains('Yesterday Session', $titles);
        $this->assertContains('Future Session', $titles);
    }

    public function test_sport_rules_rejects_unknown_sport(): void
    {
        $club = Club::factory()->create();

        $this->getJson("/api/clubs/{$club->id}/sport-rules/cricket")
            ->assertUnprocessable();
    }

    public function test_register_club_leaves_subscription_dates_null_until_activation(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $plan = SaasPlan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter-dates',
            'monthly_price' => 49,
            'yearly_price' => 490,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/register-club', [
            'name' => 'Date Test Academy',
            'address' => '789 Road',
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ])->assertCreated();

        $sub = ClubSaasSubscription::query()->whereHas('club', fn ($q) => $q->where('name', 'Date Test Academy'))->first();

        $this->assertNotNull($sub);
        $this->assertNull($sub->starts_at);
        $this->assertNull($sub->ends_at);
    }

    public function test_duplicate_pending_club_registration_is_rejected(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $plan = SaasPlan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter-dup',
            'monthly_price' => 49,
            'yearly_price' => 490,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Club::factory()->create([
            'name' => 'Existing Pending',
            'registration_status' => 'pending',
            'subscription_status' => 'inactive',
        ])->users()->attach($user->id, ['role' => 'owner']);

        Sanctum::actingAs($user);

        $this->postJson('/api/register-club', [
            'name' => 'Second Academy',
            'address' => '999 Lane',
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'You already have a club registration awaiting approval.',
            ]);
    }

    public function test_paymob_uses_user_phone_in_billing_data(): void
    {
        config([
            'services.paymob.api_key' => 'test-key',
            'services.paymob.integration_id' => 12345,
            'services.paymob.iframe_id' => 'iframe-1',
            'services.paymob.base_url' => 'https://accept.paymob.com/api',
        ]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-token']),
            'https://accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 9001]),
            'https://accept.paymob.com/api/acceptance/payment_keys' => function ($request) {
                $body = $request->data();
                $this->assertSame('+201012345678', $body['billing_data']['phone_number']);

                return Http::response(['token' => 'payment-key']);
            },
        ]);

        $user = User::factory()->create(['phone' => '01012345678']);
        $booking = \App\Models\Booking::factory()->create();

        app(\App\Services\PaymobService::class)->createPaymentSessionForParticipant($booking, $user, 25.0);
    }
}
