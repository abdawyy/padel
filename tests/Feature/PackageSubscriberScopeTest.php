<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageSubscriberScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_subscriber_options_only_include_club_players(): void
    {
        $club = Club::factory()->create();
        $otherClub = Club::factory()->create();

        $clubPlayer = User::factory()->create(['role' => 'player', 'name' => 'Club Player']);
        $otherPlayer = User::factory()->create(['role' => 'player', 'name' => 'Other Player']);

        $club->users()->attach($clubPlayer->id, ['role' => 'staff']);
        $otherClub->users()->attach($otherPlayer->id, ['role' => 'staff']);

        $package = Package::factory()->create(['club_id' => $club->id]);

        $clubMemberIds = User::query()
            ->where('role', 'player')
            ->whereHas('clubs', fn ($query) => $query->where('clubs.id', $package->club_id))
            ->pluck('id')
            ->all();

        $this->assertContains($clubPlayer->id, $clubMemberIds);
        $this->assertNotContains($otherPlayer->id, $clubMemberIds);
    }
}
