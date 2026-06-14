<?php

namespace Tests\Feature;

use App\Filament\Player\Pages\MyMatches;
use App\Filament\Player\Pages\PlayerProfile;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnhUx008025Test extends TestCase
{
    use RefreshDatabase;

    public function test_money_formatter_uses_app_currency(): void
    {
        config(['app.currency' => 'EGP']);

        $this->assertStringContainsString('EGP', Money::format(100));
    }

    public function test_player_profile_page_is_available_to_players(): void
    {
        $player = User::factory()->create(['role' => 'player']);

        $this->actingAs($player)
            ->get(PlayerProfile::getUrl(panel: 'player'))
            ->assertOk();
    }

    public function test_booking_confirmation_email_uses_player_portal_url(): void
    {
        $user = User::factory()->create(['role' => 'player']);
        $booking = \App\Models\Booking::factory()->create(['owner_user_id' => $user->id]);

        $mail = (new \App\Notifications\BookingConfirmedNotification($booking))
            ->toMail($user);

        $this->assertStringContainsString(MyMatches::getUrl(panel: 'player'), (string) $mail->actionUrl);
        $this->assertStringContainsString('#booking-'.$booking->id, (string) $mail->actionUrl);
    }

    public function test_web_layout_includes_portal_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/player/login', false)
            ->assertSee('/coach/login', false);
    }
}
