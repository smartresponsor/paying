# Paying Wave 6 — Infrastructure Name-Form Canonicalization

## Scope

Wave 6 canonicalizes the infrastructure support layer so operational classes carry the `Payment` prefix consistently with the component namespace `App\Paying`.

## Renamed infrastructure classes

- `AuditLogger` → `PaymentAuditLogger`
- `OutboxPublisher` → `PaymentOutboxPublisher`
- `OutboxWorker` → `PaymentOutboxWorker`
- `DbalIdempotencyStore` → `PaymentDbalIdempotencyStore`
- `RedisIdempotencyStore` → `PaymentRedisIdempotencyStore`
- `MetricSubscriber` → `PaymentMetricSubscriber`
- `RateLimitSubscriber` → `PaymentRateLimitSubscriber`
- `ResponseHeaderSubscriber` → `PaymentResponseHeaderSubscriber`
- `PublisherTransportLog` → `PaymentPublisherTransportLog`

## Renamed infrastructure contracts

- `AuditLoggerInterface` → `PaymentAuditLoggerInterface`
- `OutboxPublisherInterface` → `PaymentOutboxPublisherInterface`
- `PublisherTransportInterface` → `PaymentPublisherTransportInterface`

## Runtime contract

Symfony command names, route names, public API payloads, and database table names are not changed by this wave.

## Guard

Run:

```bash
composer report:infrastructure-name-form
```
