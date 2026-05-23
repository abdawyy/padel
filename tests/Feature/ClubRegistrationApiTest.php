<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\SaasPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClubRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_cannot_create_club_via_post_clubs(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Sanctum::actingAs($user);

        $this->postJson('/api/clubs', [
            'name' => 'Rogue Academy',
            'address' => '123 Test St',
        ])->assertNotFound();
    }

    public function test_register_club_creates_pending_inactive_club(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $plan = SaasPlan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter-reg',
            'monthly_price' => 49,
            'yearly_price' => 490,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/register-club', [
            'name' => 'New Academy',
            'address' => '456 Main St',
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ]);

        $response->assertCreated();

        $club = Club::query()->where('name', 'New Academy')->first();

        $this->assertNotNull($club);
        $this->assertSame('pending', $club->registration_status);
        $this->assertSame('inactive', $club->subscription_status);
        $this->assertTrue($user->fresh()->canManageClub($club));

        $subscription = $club->latestSaasSubscription;
        $this->assertNotNull($subscription);
        $this->assertNull($subscription->starts_at);
        $this->assertNull($subscription->ends_at);
    }

    public function test_pending_clubs_are_not_listed_publicly(): void
    {
        Club::factory()->create([
            'name' => 'Pending Club',
            'subscription_status' => 'active',
            'registration_status' => 'pending',
        ]);

        Club::factory()->create([
            'name' => 'Approved Club',
            'subscription_status' => 'active',
            'registration_status' => 'approved',
        ]);

        $response = $this->getJson('/api/clubs');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name')->all();

        $this->assertContains('Approved Club', $names);
        $this->assertNotContains('Pending Club', $names);
    }
}
