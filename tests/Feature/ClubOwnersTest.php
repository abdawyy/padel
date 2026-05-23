<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubOwnersTest extends TestCase
{
    use RefreshDatabase;

    public function test_club_owners_returns_only_users_with_owner_pivot_role(): void
    {
        $club = Club::factory()->create();
        $owner = User::factory()->create();
        $staff = User::factory()->create();

        $club->users()->attach($owner->id, ['role' => 'owner']);
        $club->users()->attach($staff->id, ['role' => 'staff']);

        $owners = $club->owners()->get();

        $this->assertCount(1, $owners);
        $this->assertTrue($owners->first()->is($owner));
    }
}
