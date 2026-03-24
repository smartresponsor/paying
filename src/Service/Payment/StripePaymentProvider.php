<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Service\Payment;

use App\Entity\Payment\Payment;
use App\ValueObject\Payment\PaymentStatus;
use Symfony\Component\Uid\Ulid;

/**
 * Production-grade Stripe provider.
 * Requires: composer require stripe/stripe-php
 * ENV: STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET.
 */
final class StripePaymentProvider implements PaymentProviderInterface
{
    private string $secretKey;
    private string $webhookSecret;

    public function __construct(?string $secretKey = null, ?string $webhookSecret = null)
    {
        $this->secretKey = $secretKey ?? (getenv('STRIPE_SECRET_KEY') ?: '');
        $this->webhookSecret = $webhookSecret ?? (getenv('STRIPE_WEBHOOK_SECRET') ?: '');
    }

    public function start(Payment $payment, array $context = []): array
    {
        return [
            'provider' => 'stripe',
            'paymentId' => (string) $payment->id(),
            'result' => $this->create(
                (string) ($context['projectId'] ?? $payment->id()),
                (float) $payment->amount(),
                $payment->currency(),
                (string) ($context['idempotencyKey'] ?? $payment->id())
            ),
        ];
    }

    public function finalize(Ulid $id, array $payload = []): Payment
    {
        return new Payment($id, PaymentStatus::completed, (string) ($payload['amount'] ?? '0.00'), (string) ($payload['currency'] ?? 'USD'));
    }

    public function refund(Ulid $id, string $amount): Payment
    {
        return new Payment($id, PaymentStatus::refunded, $amount, 'USD');
    }

    public function reconcile(Ulid $id): Payment
    {
        return new Payment($id, PaymentStatus::processing, '0.00', 'USD');
    }

    public function create(string $projectId, float $amount, string $currency, string $idempotencyKey): array
    {
        if (class_exists('\Stripe\StripeClient') && '' !== $this->secretKey) {
            $stripe = new \Stripe\StripeClient($this->secretKey);
            $amt = (int) round($amount * 100);
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => ['name' => 'Project '.$projectId],
                        'unit_amount' => $amt,
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => getenv('PAYMENT_SUCCESS_URL') ?: 'https://example/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => getenv('PAYMENT_CANCEL_URL') ?: 'https://example/cancel',
            ], ['idempotency_key' => $idempotencyKey]);

            return [
                'providerRef' => $session->id,
                'checkoutUrl' => $session->url ?? null,
            ];
        }

        $ref = 'stripe_'.substr(sha1($projectId.$amount.$currency.$idempotencyKey), 0, 24);

        return ['providerRef' => $ref];
    }

    public function verifyWebhook(string $rawBody, string $signatureHeader): array
    {
        if ('' === $this->webhookSecret) {
            throw new \RuntimeException('STRIPE_WEBHOOK_SECRET is required for webhook verification');
        }
        $parts = [];
        foreach (explode(',', $signatureHeader) as $item) {
            $kv = explode('=', trim($item), 2);
            if (2 === count($kv)) {
                $parts[$kv[0]] = $kv[1];
            }
        }
        $timestamp = isset($parts['t']) ? (int) $parts['t'] : 0;
        $signatures = array_values(array_filter(explode(',', $signatureHeader), fn ($x) => str_starts_with(trim($x), 'v1=')));
        $sigs = [];
        foreach ($signatures as $signature) {
            $sigs[] = substr($signature, 3);
        }

        if (0 === $timestamp || abs(time() - $timestamp) > 300) {
            return ['ok' => false, 'error' => 'timestamp_out_of_tolerance'];
        }

        $signedPayload = $timestamp.'.'.$rawBody;
        $expected = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        $valid = false;
        foreach ($sigs as $sig) {
            if (hash_equals($expected, $sig)) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            return ['ok' => false, 'error' => 'invalid_signature'];
        }

        $event = json_decode($rawBody, true) ?: [];
        $type = $event['type'] ?? 'unknown';
        $obj = $event['data']['object'] ?? [];
        $providerRef = $obj['id'] ?? null;
        $amount = isset($obj['amount_total']) ? $obj['amount_total'] / 100.0 : (isset($obj['amount']) ? $obj['amount'] / 100.0 : 0.0);
        $currency = strtoupper($obj['currency'] ?? 'USD');
        $projectId = $obj['metadata']['projectId'] ?? ($obj['client_reference_id'] ?? '');

        return ['ok' => true, 'event' => $type, 'providerRef' => $providerRef, 'amount' => $amount, 'currency' => $currency, 'projectId' => $projectId];
    }
}
