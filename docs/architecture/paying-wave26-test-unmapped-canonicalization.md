# Paying Wave 26 — Test unmapped canonicalization

Status: prepared as touched-files patch.

Wave 26 canonicalizes the remaining unmapped unit-test residuals that were intentionally left out of the mapped duplicate retirement wave.

## Canonicalized tests

- `OutboxPublisherEnqueueTest` → `PaymentOutboxPublisherEnqueueTest`
- `OutboxWorkerRetryTest` → `PaymentOutboxWorkerRetryTest`
- `PayPalEventNormalizerTest.php` → `PaymentPayPalEventNormalizerTest.php`
- `StripeEventNormalizerTest` → `PaymentStripeEventNormalizerTest`

The Stripe normalizer test also moves from the retired unprefixed `StripeEventNormalizer` class reference to `PaymentStripeEventNormalizer`.

## Scope boundaries

This wave does not change runtime services, routes, provider keys, scope strings, Doctrine tables, public command names, or message contracts.

## Verification

Run:

```bash
composer dump-autoload
composer report:test-unmapped-canonicalization
composer report:test-unmapped-residual-name-form
composer test:unit
```
