# Paying Wave 17: Subscriber Layer / Name-Form Canonicalization

Wave 17 moves HTTP/event subscribers out of the infrastructure bucket and into the Symfony-oriented `src/Subscriber` layer.

## Canonicalized files

- `src/Subscriber/PaymentMetricSubscriber.php`
- `src/Subscriber/PaymentRateLimitSubscriber.php`
- `src/Subscriber/PaymentResponseHeaderSubscriber.php`
- `src/Subscriber/PaymentScopeGuardSubscriber.php`

## Retired legacy files

- `src/Infrastructure/PaymentMetricSubscriber.php`
- `src/Infrastructure/PaymentRateLimitSubscriber.php`
- `src/Infrastructure/PaymentResponseHeaderSubscriber.php`
- `src/Infrastructure/ScopeGuardSubscriber.php`
- `tests/Unit/ResponseHeaderSubscriberTest.php`
- `tests/Unit/ScopeGuardSubscriberTest.php`

## Contract safety

Public routes, provider keys, scope strings, Doctrine tables, event names, and environment variables are unchanged.

## Guard

Run:

```bash
composer report:subscriber-layer-name-form
```
