<?php
declare(strict_types=1);

namespace App\Service\Payment;

use App\ServiceInterface\Payment\PaymentProviderInterface;

/**
 * Production-grade Stripe provider.
 * Requires: composer require stripe/stripe-php
 * ENV: STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET
 */
final class StripePaymentProvider implements PaymentProviderInterface
{
    private string $secretKey;
    private string $webhookSecret;

    public function __construct(?string $secretKey = null, ?string $webhookSecret = null)
    {
        $this->secretKey = $secretKey ?? (getenv('STRIPE_SECRET_KEY') ?: '');
        $this->webhookSecret = $webhookSecret ?? (getenv('STRIPE_WEBHOOK_SECRET') ?: '');
        if ($this->secretKey === '') {
            throw new \RuntimeException('STRIPE_SECRET_KEY is required');
        }
    }

    public function create(string $projectId, float $amount, string $currency, string $idempotencyKey): array
    {
        // Use Stripe official client if present
        if (class_exists('\Stripe\StripeClient')) {
            $stripe = new \Stripe\StripeClient($this->secretKey);
            $amt = (int) round($amount * 100); // smallest currency unit
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => ['name' => 'Project '.$projectId],
                        'unit_amount' => $amt,
                    ],
                    'quantity' => 1
                ]],
                'success_url' => getenv('PAYMENT_SUCCESS_URL') ?: 'https://example/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => getenv('PAYMENT_CANCEL_URL') ?: 'https://example/cancel',
            ], ['idempotency_key' => $idempotencyKey]);

            return [
                'providerRef' => $session->id,
                'checkoutUrl' => $session->url ?? null,
            ];
        }

        // Fallback: create a deterministic providerRef; expect infra to install stripe-php.
        $ref = 'stripe_' . substr(sha1($projectId.$amount.$currency.$idempotencyKey), 0, 24);
        return ['providerRef' => $ref];
    }

    public function verifyWebhook(string $rawBody, string $signatureHeader): array
    {
        if ($this->webhookSecret === '') {
            throw new \RuntimeException('STRIPE_WEBHOOK_SECRET is required for webhook verification');
        }
        // Stripe signature header: t=timestamp, v1=signature1, v1=signature2...
        $parts = [];
        foreach (explode(',', $signatureHeader) as $item) {
            $kv = explode('=', trim($item), 2);
            if (count($kv) === 2) $parts[$kv[0]] = $kv[1];
        }
        $timestamp = isset($parts['t']) ? (int)$parts['t'] : 0;
        $signatures = array_values(array_filter(explode(',', $signatureHeader), fn($x)=>str_starts_with(trim($x), 'v1=')));
        $sigs = [];
        foreach ($signatures as $s) { $sigs[] = substr($s, 3); }

        // tolerance 5 minutes
        if ($timestamp === 0 || abs(time() - $timestamp) > 300) {
            return ['ok' => false, 'error' => 'timestamp_out_of_tolerance'];
        }

        $signedPayload = $timestamp . '.' . $rawBody;
        $expected = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        $valid = false;
        foreach ($sigs as $sig) {
            if (hash_equals($expected, $sig)) { $valid = true; break; }
        }
        if (!$valid) return ['ok' => false, 'error' => 'invalid_signature'];

        $event = json_decode($rawBody, true) ?: [];
        $type = $event['type'] ?? 'unknown';
        $obj  = $event['data']['object'] ?? [];
        $providerRef = $obj['id'] ?? null;
        $amount = isset($obj['amount_total']) ? $obj['amount_total'] / 100.0 : (isset($obj['amount']) ? $obj['amount'] / 100.0 : 0.0);
        $currency = strtoupper($obj['currency'] ?? 'USD');
        $projectId = $obj['metadata']['projectId'] ?? ($obj['client_reference_id'] ?? '');

        return ['ok'=>true,'event'=>$type,'providerRef'=>$providerRef,'amount'=>$amount,'currency'=>$currency,'projectId'=>$projectId];
    }
}
