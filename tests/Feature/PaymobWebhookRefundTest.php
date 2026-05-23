<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymobWebhookRefundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paymob.hmac_secret' => 'test-hmac-secret',
        ]);
    }

    public function test_refunded_booking_webhook_reverses_participant_payment(): void
    {
        $booking = Booking::factory()->create(['status' => 'confirmed']);
        $user = User::factory()->create();

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount_due' => 50.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $merchantOrderId = sprintf('booking_%d_user_%d', $booking->id, $user->id);
        $transaction = $this->transactionPayload(
            paymobId: 'txn_refund_001',
            merchantOrderId: $merchantOrderId,
            amountCents: 5000,
            success: false,
            isRefunded: true,
        );

        $this->postJson(
            '/api/webhooks/paymob/transaction-processed?hmac='.$this->computeHmac($transaction),
            ['obj' => $transaction],
        )
            ->assertOk()
            ->assertJson(['status' => 'booking_refunded']);

        $this->assertDatabaseHas('booking_participants', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'payment_status' => 'refunded',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'paymob_transaction_id' => 'txn_refund_001',
            'status' => 'refunded',
        ]);

        $booking->refresh();
        $this->assertSame('pending', $booking->status);
    }

    public function test_refunded_session_webhook_detaches_player(): void
    {
        $session = AcademySession::factory()->create();
        $user = User::factory()->create();
        $session->players()->attach($user->id, ['status' => 'registered']);

        $merchantOrderId = sprintf('session_%d_user_%d', $session->id, $user->id);
        $transaction = $this->transactionPayload(
            paymobId: 'txn_refund_session_001',
            merchantOrderId: $merchantOrderId,
            amountCents: 5000,
            success: false,
            isRefunded: true,
        );

        $this->postJson(
            '/api/webhooks/paymob/transaction-processed?hmac='.$this->computeHmac($transaction),
            ['obj' => $transaction],
        )
            ->assertOk()
            ->assertJson(['status' => 'session_refunded']);

        $this->assertDatabaseMissing('academy_session_user', [
            'academy_session_id' => $session->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_webhook_returns_503_when_hmac_secret_missing(): void
    {
        config(['services.paymob.hmac_secret' => '']);

        $transaction = $this->transactionPayload(
            paymobId: 'txn_no_secret',
            merchantOrderId: 'booking_1_user_1',
            amountCents: 1000,
            success: false,
            isRefunded: true,
        );

        $this->postJson('/api/webhooks/paymob/transaction-processed?hmac=abc', ['obj' => $transaction])
            ->assertStatus(503)
            ->assertJsonFragment([
                'message' => 'Paymob webhook is not configured. Set PAYMOB_HMAC_SECRET in your environment.',
            ]);

        $this->assertSame(0, PaymentTransaction::query()->count());
    }

    /**
     * @return array<string, mixed>
     */
    private function transactionPayload(
        string $paymobId,
        string $merchantOrderId,
        int $amountCents,
        bool $success,
        bool $isRefunded = false,
        bool $isVoided = false,
    ): array {
        return [
            'id' => $paymobId,
            'amount_cents' => $amountCents,
            'created_at' => '2026-05-23T12:00:00',
            'currency' => 'EGP',
            'error_occured' => false,
            'has_parent_transaction' => false,
            'integration_id' => 12345,
            'is_3d_secure' => false,
            'is_auth' => false,
            'is_capture' => false,
            'is_refunded' => $isRefunded,
            'is_standalone_payment' => true,
            'is_voided' => $isVoided,
            'owner' => 1,
            'pending' => false,
            'source_data' => [
                'pan' => '1234',
                'sub_type' => 'MasterCard',
                'type' => 'card',
            ],
            'success' => $success,
            'order' => [
                'id' => 999,
                'merchant_order_id' => $merchantOrderId,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function computeHmac(array $transaction): string
    {
        $orderedValues = [
            data_get($transaction, 'amount_cents'),
            data_get($transaction, 'created_at'),
            data_get($transaction, 'currency'),
            data_get($transaction, 'error_occured'),
            data_get($transaction, 'has_parent_transaction'),
            data_get($transaction, 'id'),
            data_get($transaction, 'integration_id'),
            data_get($transaction, 'is_3d_secure'),
            data_get($transaction, 'is_auth'),
            data_get($transaction, 'is_capture'),
            data_get($transaction, 'is_refunded'),
            data_get($transaction, 'is_standalone_payment'),
            data_get($transaction, 'is_voided'),
            data_get($transaction, 'order.id'),
            data_get($transaction, 'owner'),
            data_get($transaction, 'pending'),
            data_get($transaction, 'source_data.pan'),
            data_get($transaction, 'source_data.sub_type'),
            data_get($transaction, 'source_data.type'),
            data_get($transaction, 'success'),
        ];

        $payload = collect($orderedValues)
            ->map(function ($value) {
                if (is_bool($value)) {
                    return $value ? 'true' : 'false';
                }

                if ($value === null) {
                    return '';
                }

                return (string) $value;
            })
            ->implode('');

        return hash_hmac('sha512', $payload, (string) config('services.paymob.hmac_secret'));
    }
}
