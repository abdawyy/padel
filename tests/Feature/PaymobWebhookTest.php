<?php

namespace Tests\Feature;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\Club;
use App\Models\ClubSaasSubscription;
use App\Models\PaymentTransaction;
use App\Models\SaasPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymobWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paymob.hmac_secret' => 'test-hmac-secret',
        ]);
    }

    public function test_session_payment_webhook_creates_transaction_and_enrolls_user(): void
    {
        $session = AcademySession::factory()->create([
            'max_players' => 8,
            'price_per_player' => 50.00,
        ]);
        $user = User::factory()->create();

        $merchantOrderId = sprintf('session_%d_user_%d', $session->id, $user->id);
        $transaction = $this->paymobTransactionPayload(
            paymobId: 'txn_session_001',
            merchantOrderId: $merchantOrderId,
            amountCents: 5000,
            success: true,
        );

        $response = $this->postJson(
            '/api/webhooks/paymob/transaction-processed?hmac='.$this->computeHmac($transaction),
            ['obj' => $transaction],
        );

        $response->assertOk()->assertJson(['status' => 'session_enrolled']);

        $this->assertDatabaseHas('payment_transactions', [
            'paymob_transaction_id' => 'txn_session_001',
            'academy_session_id' => $session->id,
            'booking_id' => null,
            'user_id' => $user->id,
            'amount' => 50.00,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('academy_session_user', [
            'academy_session_id' => $session->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);
    }

    public function test_booking_payment_webhook_stores_booking_id_not_session_id(): void
    {
        $booking = Booking::factory()->create();
        $user = User::factory()->create();

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount_due' => 25.00,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $merchantOrderId = sprintf('booking_%d_user_%d', $booking->id, $user->id);
        $transaction = $this->paymobTransactionPayload(
            paymobId: 'txn_booking_001',
            merchantOrderId: $merchantOrderId,
            amountCents: 2500,
            success: true,
        );

        $response = $this->postJson(
            '/api/webhooks/paymob/transaction-processed?hmac='.$this->computeHmac($transaction),
            ['obj' => $transaction],
        );

        $response->assertOk()->assertJson(['status' => 'processed']);

        $payment = PaymentTransaction::query()
            ->where('paymob_transaction_id', 'txn_booking_001')
            ->first();

        $this->assertNotNull($payment);
        $this->assertSame($booking->id, $payment->booking_id);
        $this->assertNull($payment->academy_session_id);

        $this->assertDatabaseHas('booking_participants', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_saas_webhook_activates_pending_subscription(): void
    {
        $club = Club::factory()->create(['subscription_status' => 'inactive']);
        $user = User::factory()->create();
        $plan = SaasPlan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro-saas',
            'monthly_price' => 99,
            'yearly_price' => 990,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $subscription = ClubSaasSubscription::query()->create([
            'club_id' => $club->id,
            'saas_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'amount_paid' => 99.00,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'status' => 'pending',
        ]);

        $merchantOrderId = sprintf('saas_%d_user_%d', $subscription->id, $user->id);
        $transaction = $this->paymobTransactionPayload(
            paymobId: 'txn_saas_001',
            merchantOrderId: $merchantOrderId,
            amountCents: 9900,
            success: true,
        );

        $this->postJson(
            '/api/webhooks/paymob/transaction-processed?hmac='.$this->computeHmac($transaction),
            ['obj' => $transaction],
        )->assertOk()->assertJson(['status' => 'saas_activated']);

        $subscription->refresh();
        $club->refresh();

        $this->assertSame('active', $subscription->status);
        $this->assertSame('active', $club->subscription_status);
        $this->assertDatabaseHas('payment_transactions', [
            'club_saas_subscription_id' => $subscription->id,
            'status' => 'success',
        ]);
    }

    public function test_webhook_rejects_underpayment_for_booking(): void
    {
        $booking = Booking::factory()->create(['total_price' => 100]);
        $user = User::factory()->create();

        DB::table('booking_participants')->insert([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount_due' => 50.00,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $merchantOrderId = sprintf('booking_%d_user_%d', $booking->id, $user->id);
        $transaction = $this->paymobTransactionPayload(
            paymobId: 'txn_underpay_001',
            merchantOrderId: $merchantOrderId,
            amountCents: 1000,
            success: true,
        );

        $this->postJson(
            '/api/webhooks/paymob/transaction-processed?hmac='.$this->computeHmac($transaction),
            ['obj' => $transaction],
        )->assertUnprocessable();

        $this->assertDatabaseHas('booking_participants', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_session_webhook_rejects_invalid_merchant_order_id(): void
    {
        $transaction = $this->paymobTransactionPayload(
            paymobId: 'txn_bad_001',
            merchantOrderId: 'invalid_format',
            amountCents: 1000,
            success: true,
        );

        $this->postJson(
            '/api/webhooks/paymob/transaction-processed?hmac='.$this->computeHmac($transaction),
            ['obj' => $transaction],
        )->assertUnprocessable();
    }

    /**
     * @return array<string, mixed>
     */
    private function paymobTransactionPayload(
        string $paymobId,
        string $merchantOrderId,
        int $amountCents,
        bool $success,
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
            'is_refunded' => false,
            'is_standalone_payment' => true,
            'is_voided' => false,
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
