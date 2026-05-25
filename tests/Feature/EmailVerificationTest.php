<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.require_email_verification' => true]);
    }

    public function test_register_requires_verification_before_sensitive_actions(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Verify Me',
            'email' => 'verify@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('requires_email_verification', true)
            ->assertJsonMissingPath('token');

        $user = User::query()->where('email', 'verify@example.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->postJson('/api/bookings', [])
            ->assertForbidden()
            ->assertJsonPath('message', 'Your email address is not verified.');
    }

    public function test_user_can_verify_email_via_signed_link(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('message', 'Email address verified successfully.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verified_user_can_access_protected_booking_routes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Sanctum::actingAs($user);

        $this->postJson('/api/bookings', [])
            ->assertUnprocessable();
    }
}
