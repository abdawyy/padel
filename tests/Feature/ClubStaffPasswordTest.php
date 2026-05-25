<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClubStaffPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_staff_requires_password(): void
    {
        $club = Club::factory()->create(['registration_status' => 'approved', 'subscription_status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $club->users()->attach($manager->id, ['role' => 'owner']);

        Sanctum::actingAs($manager);

        $this->postJson("/api/v1/clubs/{$club->id}/staff", [
            'name' => 'New Coach',
            'email' => 'newcoach@example.com',
            'role' => 'coach',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseMissing('users', [
            'email' => 'newcoach@example.com',
        ]);
    }
}
