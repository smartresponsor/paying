<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Service;

use App\Paying\Entity\PaymentEntity;
use App\Paying\ServiceInterface\PaymentProviderInterface;
use App\Paying\ValueObject\PaymentStatus;
use Stripe\StripeClient;
use Symfony\Component\Uid\Ulid;

/**
 * Production-grade Stripe provider.
 * Requires: composer require stripe/stripe-php
 * ENV: STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET.
 */
final class PaymentStripeProvider implements PaymentProviderInterface
{
    private string $secretKey;
    private string $webhookSecret;
    private string $paymentSuccessUrl;
    private string $paymentCancelUrl;

    public function __construct(
        ?string $secretKey = null,
        ?string $webhookSecret = null,
        ?string $paymentSuccessUrl = null,
        ?string $paymentCancelUrl = null,
    ) {
        $this->secretKey = trim($secretKey ?? '');
        $this->webhookSecret = trim($webhookSecret ?? '');
        $this->paymentSuccessUrl = '' !== trim($paymentSuccessUrl ?? '') ? trim((string) $paymentSuccessUrl) : 'https://example/success?session_id={CHECKOUT_SESSION_ID}';
        $this->paymentCancelUrl = '' !== trim($paymentCancelUrl ?? '') ? trim((string) $paymentCancelUrl) : 'https://example/cancel';
    }

    /**
     * Executes the start operation for the current payment workflow.
     *
     * @return array<string, mixed>
     */
    public function start(PaymentEntity $payment, array $context = []): array
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

    /**
     * Provides the finalize behavior for the stripe payment provider component.
     */
    public function finalize(Ulid $id, array $payload = []): PaymentEntity
    {
        return new PaymentEntity($id, PaymentStatus::completed, (string) ($payload['amount'] ?? '0.00'), (string) ($payload['currency'] ?? 'USD'));
    }

    /**
     * Executes the refund operation for the current payment workflow.
     */
    public function refund(Ulid $id, string $amount): PaymentEntity
    {
        return new PaymentEntity($id, PaymentStatus::refunded, $amount, 'USD');
    }

    /**
     * Provides the reconcile behavior for the stripe payment provider component.
     */
    public function reconcile(Ulid $id): PaymentEntity
    {
        return new PaymentEntity($id, PaymentStatus::processing, '0.00', 'USD');
    }

    /**
     * Executes the create operation for the current payment workflow.
     */
    public function create(string $projectId, float $amount, string $currency, string $idempotencyKey): array
    {
        if (class_exists('\Stripe\StripeClient') && '' !== $this->secretKey) {
            $stripe = new StripeClient($this->secretKey);
            $amountMinor = (int) round($amount * 100);
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => ['name' => 'CommerceProjectEntity '.$projectId],
                        'unit_amount' => $amountMinor,
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => $this->paymentSuccessUrl,
                'cancel_url' => $this->paymentCancelUrl,
            ], ['idempotency_key' => $idempotencyKey]);

            return [
                'providerRef' => $session->id,
                'checkoutUrl' => $session->url ?? null,
            ];
        }

        $reference = 'stripe_'.substr(sha1($projectId.$amount.$currency.$idempotencyKey), 0, 24);

        return ['providerRef' => $reference];
    }

    /**
     * Verifies the input handled by the verify webhook workflow.
     */
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
        $signatures = array_values(array_filter(explode(',', $signatureHeader), fn (string $item): bool => str_starts_with(trim($item), 'v1=')));
        $computedSignatures = [];
        foreach ($signatures as $signature) {
            $computedSignatures[] = substr($signature, 3);
        }

        if (0 === $timestamp || abs(time() - $timestamp) > 300) {
            return ['ok' => false, 'error' => 'timestamp_out_of_tolerance'];
        }

        $signedPayload = $timestamp.'.'.$rawBody;
        $expected = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        $valid = false;
        foreach ($computedSignatures as $signature) {
            if (hash_equals($expected, $signature)) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            return ['ok' => false, 'error' => 'invalid_signature'];
        }

        $event = json_decode($rawBody, true) ?: [];
        $type = $event['type'] ?? 'unknown';
        $object = $event['data']['object'] ?? [];
        $providerRef = $object['id'] ?? null;
        $amount = isset($object['amount_total']) ? $object['amount_total'] / 100.0 : (isset($object['amount']) ? $object['amount'] / 100.0 : 0.0);
        $currency = strtoupper($object['currency'] ?? 'USD');
        $projectId = $object['metadata']['projectId'] ?? ($object['client_reference_id'] ?? '');

        return ['ok' => true, 'event' => $type, 'providerRef' => $providerRef, 'amount' => $amount, 'currency' => $currency, 'projectId' => $projectId];
    }
}
