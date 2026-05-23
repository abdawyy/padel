<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\SaasPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaasRenewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paymob.api_key' => 'test-key',
            'services.paymob.integration_id' => 12345,
            'services.paymob.iframe_id' => 'iframe-1',
            'services.paymob.base_url' => 'https://accept.paymob.com/api',
        ]);

        Http::fake([
            'https://accept.paymob.com/api/auth/tokens' => Http::response(['token' => 'auth-token']),
            'https://accept.paymob.com/api/ecommerce/orders' => Http::response(['id' => 9001]),
            'https://accept.paymob.com/api/acceptance/payment_keys' => Http::response(['token' => 'payment-key']),
        ]);
    }

    public function test_renew_creates_pending_subscription_and_returns_payment_session(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $club = Club::factory()->create([
            'subscription_status' => 'inactive',
            'registration_status' => 'approved',
        ]);
        $club->users()->attach($owner->id, ['role' => 'owner']);

        $plan = SaasPlan::query()->create([
            'name' => 'Growth',
            'slug' => 'growth',
            'monthly_price' => 120,
            'yearly_price' => 1200,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/clubs/{$club->id}/saas-subscription", [
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ]);

        $response
            ->assertStatus(402)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonStructure(['payment' => ['payment_key', 'iframe_url', 'merchant_order_id']]);

        $this->assertDatabaseHas('club_saas_subscriptions', [
            'club_id' => $club->id,
            'status' => 'pending',
            'amount_paid' => 120,
        ]);

        $club->refresh();
        $this->assertSame('inactive', $club->subscription_status);
    }
}
