# Paying entity-first persistence map

## Canonical reading order

Paying should be reviewed from persistence outward, not from controllers or provider adapters inward.

1. `src/Entity/Business` is the component business persistence surface.
2. `src/Entity/Operational` is the operational/internal persistence surface used for audit, idempotency, projections, and circuit state.
3. `src/Repository` owns direct Doctrine manager access for both persistence contours.
4. `src/Service` coordinates payment lifecycle behavior through repository contracts.
5. Controllers, commands, handlers, event subscribers, and message handlers are entrypoints/integration roles only.

## Business entity surface

| Entity | Table | Responsibility |
| --- | --- | --- |
| `PaymentEntity` | `payment` | Canonical payment aggregate snapshot. |
| `PaymentTransactionEntity` | `payment_transaction` | Captured provider/payment transaction facts. |
| `PaymentRefundEntity` | `payment_refund` | Refund records tied to payment lifecycle. |
| `PaymentOutboxMessageEntity` | `payment_outbox_message` | Domain message outbox persistence. |
| `PaymentDlqEntity` | `payment_dlq` | Dead-letter queue records for failed outbox replay. |
| `PaymentGatewayEntity` | `payment_gateway` | Configured gateway code catalog. |
| `PaymentMethodEntity` | `payment_method` | Configured payment method catalog. |
| `PaymentWebhookLogEntity` | `payment_webhook_log` | Provider webhook receipt and deduplication state. |

## Operational entity surface

| Entity | Table | Responsibility |
| --- | --- | --- |
| `PaymentAuditEntity` | `payment_audit` | Operator/system audit records. |
| `PaymentCircuitEntity` | `payment_circuit` | Circuit-breaker state. |
| `PaymentIdempotencyEntity` | `payment_idempotency` | Idempotency key storage. |
| `PaymentProjectionEntity` | `payment_projection` | Read-side projection snapshot. |
| `PaymentProjectionMetaEntity` | `payment_projection_meta` | Projection watermark/state metadata. |

## Table-prefix canon

Every Doctrine-generated or Doctrine-managed table in Paying must use the `payment` prefix. The root aggregate table may be exactly `payment`; all other tables must start with `payment_`.

This follows the ecosystem-wide table naming canon and avoids unowned generic names such as `transaction`, `refund`, `outbox`, `audit`, or `projection`.

## Current boundary decision

There is no generic `src/Infrastructure` application layer. Operational entities live under `src/Entity/Operational`; direct Doctrine manager access is repository-owned under `src/Repository`; event integration lives under `src/EventSubscriber`; other application classes remain under explicit Symfony technical-role roots.

## Validation

Run:

```bash
composer report:entity-first-persistence
```

The report is non-mutating and checks:

- entity files live only in the accepted Symfony-oriented entity layers;
- entity class files use `Payment*Entity` name-form;
- Doctrine table names are explicit;
- mapped and migrated table names use the `payment` prefix.
