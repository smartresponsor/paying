# Payment webhook → outbox → consumer integrated proof

## What is now explicitly proven

The current slice contains integrated proof that the owned webhook contour can:

1. accept a provider webhook,
2. validate and normalize it,
3. persist a `PaymentWebhookLog`,
4. enqueue a `PaymentOutboxMessage`,
5. publish it through `PaymentOutboxProcessor`,
6. consume the resulting `PaymentTransportMessage`,
7. reconcile the `Payment` aggregate,
8. notify the order sync seam.

## Proof files

- `tests/Functional/Webhook/PaymentWebhookOutboxConsumerIntegratedProofTest.php`
- `tests/E2E/PaymentWebhookToOrderFlowTest.php`
- `tests/E2E/Kernel/StripeWebhookKernelFlowTest.php`

## Provider-specific proof currently covered

### Stripe captured flow

- `payment_intent.succeeded`
- normalized to `payment.captured`
- reconciles payment to `completed`
- updates provider reference from gateway transaction id
- emits order sync `captured`

### PayPal refunded flow

- `PAYMENT.CAPTURE.REFUNDED`
- normalized to `payment.refunded`
- reconciles payment to `refunded`
- updates provider reference from PayPal capture id
- emits order sync `refunded`

## What is still not fully proven

This proof still runs as in-memory/application-level integration, not as fully installed runtime proof.
The following remain separate release-hardening concerns:

- real DB migrations + fixture load + webhook execution in installed runtime
- messenger transport running end-to-end in test environment
- generic `/payment/webhook/{provider}` route integrated with the same outbox pipeline
- failure-path proof for `payment.failed`
- duplicate webhook replay proof in installed runtime

## Why this matters

This closes the largest remaining behavioral gap after the API Platform evacuation: the component now has owned proof
for the most important asynchronous business seam, not only for HTTP/UI/CLI entry points.
