<?php

namespace App\Services;

use App\Models\AcademySession;
use App\Models\Booking;
use App\Models\ClubSaasSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaymobService
{
    public function createPaymentSessionForParticipant(Booking $booking, User $user, float $amountDue): array
    {
        $amountCents = (int) round($amountDue * 100);
        $merchantOrderId = sprintf('booking_%d_user_%d', $booking->id, $user->id);

        $authToken = $this->authenticate();
        $orderId = $this->registerOrder($authToken, $amountCents, $merchantOrderId);
        $paymentKey = $this->generatePaymentKey($authToken, $orderId, $amountCents, $user, $merchantOrderId);

        return [
            'payment_key' => $paymentKey,
            'iframe_url' => sprintf(
                '%s/acceptance/iframes/%s?payment_token=%s',
                rtrim(config('services.paymob.base_url'), '/api'),
                config('services.paymob.iframe_id'),
                $paymentKey,
            ),
            'order_id' => $orderId,
            'merchant_order_id' => $merchantOrderId,
            'amount_due' => $amountDue,
            'currency' => config('services.paymob.currency', 'EGP'),
        ];
    }

    protected function authenticate(): string
    {
        $response = Http::acceptJson()
            ->post($this->endpoint('/auth/tokens'), [
                'api_key' => config('services.paymob.api_key'),
            ])
            ->throw()
            ->json();

        $token = $response['token'] ?? null;
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Failed to authenticate with Paymob.');
        }

        return $token;
    }

    protected function registerOrder(string $authToken, int $amountCents, string $merchantOrderId): int
    {
        $response = Http::acceptJson()
            ->post($this->endpoint('/ecommerce/orders'), [
                'auth_token' => $authToken,
                'delivery_needed' => false,
                'amount_cents' => (string) $amountCents,
                'currency' => config('services.paymob.currency', 'EGP'),
                'merchant_order_id' => $merchantOrderId,
                'items' => [],
            ])
            ->throw()
            ->json();

        $orderId = $response['id'] ?? null;
        if (! is_int($orderId)) {
            throw new RuntimeException('Failed to register Paymob order.');
        }

        return $orderId;
    }

    protected function generatePaymentKey(string $authToken, int $orderId, int $amountCents, User $user, string $merchantOrderId): string
    {
        $response = Http::acceptJson()
            ->post($this->endpoint('/acceptance/payment_keys'), [
                'auth_token' => $authToken,
                'amount_cents' => (string) $amountCents,
                'expiration' => 3600,
                'order_id' => $orderId,
                'billing_data' => [
                    'apartment' => 'NA',
                    'email' => $user->email,
                    'floor' => 'NA',
                    'first_name' => $user->name ?: 'Player',
                    'street' => 'NA',
                    'building' => 'NA',
                    'phone_number' => $this->billingPhoneNumber($user),
                    'shipping_method' => 'NA',
                    'postal_code' => 'NA',
                    'city' => 'NA',
                    'country' => 'EG',
                    'last_name' => 'Player',
                    'state' => 'NA',
                ],
                'currency' => config('services.paymob.currency', 'EGP'),
                'integration_id' => (int) config('services.paymob.integration_id'),
                'lock_order_when_paid' => true,
                'merchant_order_id' => $merchantOrderId,
            ])
            ->throw()
            ->json();

        $paymentKey = $response['token'] ?? null;
        if (! is_string($paymentKey) || $paymentKey === '') {
            throw new RuntimeException('Failed to generate Paymob payment key.');
        }

        return $paymentKey;
    }

    protected function endpoint(string $path): string
    {
        return rtrim(config('services.paymob.base_url'), '/') . $path;
    }

    protected function billingPhoneNumber(User $user): string
    {
        $digits = preg_replace('/\D+/', '', (string) $user->phone) ?? '';

        if ($digits === '') {
            return (string) config('services.paymob.default_billing_phone', '+201000000000');
        }

        if (str_starts_with($digits, '20')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+20'.ltrim($digits, '0');
        }

        return '+'.$digits;
    }

    /**
     * Create a Paymob payment session for a SaaS subscription renewal.
     */
    public function createPaymentSessionForSaasSubscription(ClubSaasSubscription $subscription, User $user, float $amountDue): array
    {
        $amountCents     = (int) round($amountDue * 100);
        $merchantOrderId = sprintf('saas_%d_user_%d', $subscription->id, $user->id);

        $authToken  = $this->authenticate();
        $orderId    = $this->registerOrder($authToken, $amountCents, $merchantOrderId);
        $paymentKey = $this->generatePaymentKey($authToken, $orderId, $amountCents, $user, $merchantOrderId);

        return [
            'payment_key'       => $paymentKey,
            'iframe_url'        => sprintf(
                '%s/acceptance/iframes/%s?payment_token=%s',
                rtrim(config('services.paymob.base_url'), '/api'),
                config('services.paymob.iframe_id'),
                $paymentKey,
            ),
            'order_id'          => $orderId,
            'merchant_order_id' => $merchantOrderId,
            'amount_due'        => $amountDue,
            'currency'          => config('services.paymob.currency', 'EGP'),
        ];
    }

    /**
     * Create a Paymob payment session for a session enrollment (academy session fee).
     */
    public function createPaymentSessionForEnrollment(AcademySession $session, User $user, float $amountDue): array
    {
        $amountCents     = (int) round($amountDue * 100);
        $merchantOrderId = sprintf('session_%d_user_%d', $session->id, $user->id);

        $authToken  = $this->authenticate();
        $orderId    = $this->registerOrder($authToken, $amountCents, $merchantOrderId);
        $paymentKey = $this->generatePaymentKey($authToken, $orderId, $amountCents, $user, $merchantOrderId);

        return [
            'payment_key'       => $paymentKey,
            'iframe_url'        => sprintf(
                '%s/acceptance/iframes/%s?payment_token=%s',
                rtrim(config('services.paymob.base_url'), '/api'),
                config('services.paymob.iframe_id'),
                $paymentKey,
            ),
            'order_id'          => $orderId,
            'merchant_order_id' => $merchantOrderId,
            'amount_due'        => $amountDue,
            'currency'          => config('services.paymob.currency', 'EGP'),
        ];
    }

    /**
     * Request a refund for a captured Paymob transaction.
     *
     * @return array<string, mixed>
     */
    public function refundTransaction(string $paymobTransactionId, float $amount): array
    {
        $amountCents = (int) round($amount * 100);
        $authToken = $this->authenticate();

        $response = Http::acceptJson()
            ->post($this->endpoint('/acceptance/void_refund/refund'), [
                'auth_token' => $authToken,
                'transaction_id' => $paymobTransactionId,
                'amount_cents' => (string) $amountCents,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Paymob refund request failed.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Invalid Paymob refund response.');
        }

        return $payload;
    }

    public function createPaymentSessionForPackageSubscription(
        \App\Models\PackageSubscription $subscription,
        User $user,
        float $amountDue,
    ): array {
        $amountCents = (int) round($amountDue * 100);
        $merchantOrderId = sprintf('package_%d_user_%d', $subscription->id, $user->id);

        $authToken = $this->authenticate();
        $orderId = $this->registerOrder($authToken, $amountCents, $merchantOrderId);
        $paymentKey = $this->generatePaymentKey($authToken, $orderId, $amountCents, $user, $merchantOrderId);

        return [
            'payment_key' => $paymentKey,
            'iframe_url' => sprintf(
                '%s/acceptance/iframes/%s?payment_token=%s',
                rtrim(config('services.paymob.base_url'), '/api'),
                config('services.paymob.iframe_id'),
                $paymentKey,
            ),
            'order_id' => $orderId,
            'merchant_order_id' => $merchantOrderId,
            'amount_due' => $amountDue,
            'currency' => config('services.paymob.currency', 'EGP'),
        ];
    }
}
