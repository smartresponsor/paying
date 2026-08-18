# Paying entity-first migration retirement

## Scope

Current platform slice: `Paying` component from `www-clean-20260610-170157(1).zip`.
Old monolith donor: `Entity-src(6).zip`, restricted to `Entity/Payment` and `EntityInterface/Payment`.

## Result

`Paying/Migrations/**` is retired as an executable schema source. Doctrine entities are now the canonical schema source for the component.

## Migration-only coverage

The current component already had entity coverage for the migration tables:

- `payment` -> `PaymentEntity`
- `payment_transaction` -> `PaymentTransactionEntity`
- `payment_refund` -> `PaymentRefundEntity`
- `payment_outbox_message` -> `PaymentOutboxMessageEntity`
- `payment_dlq` -> `PaymentDlqEntity`
- `payment_webhook_log` -> `PaymentWebhookLogEntity`
- `payment_audit` -> `Infrastructure/Entity/PaymentAuditEntity`
- `payment_circuit` -> `Infrastructure/Entity/PaymentCircuitEntity`
- `payment_idempotency` -> `Infrastructure/Entity/PaymentIdempotencyEntity`
- `payment_projection` -> `Infrastructure/Entity/PaymentProjectionEntity`
- `payment_projection_meta` -> `Infrastructure/Entity/PaymentProjectionMetaEntity`

## Old monolith relation recovery

Restored into canonical Symfony-oriented entities:

- `PaymentEnUs` -> `PaymentTranslationEntity`
- `PaymentToken` -> `PaymentTokenEntity`
- `PaymentRecurring` -> `PaymentRecurringEntity`
- `PaymentGateway.paymentMethod` relation -> `PaymentGatewayEntity.paymentMethods`
- `PaymentMethod.gateway` relation -> `PaymentMethodEntity.gateway`
- `PaymentGateway` metadata: `currencies`, `sandboxMode`, `logoUrl`, `sortOrder`
- `PaymentMethod.methodName`

The old `Payment.vendor` relation was intentionally not reintroduced as a hard Doctrine relation. In the new component boundary, Paying should not directly depend on Vendoring entity classes. Cross-component ownership should stay as scalar correlation (`orderId`, future `vendorId` if needed) or be modeled through the platform integration layer.

## Objecting decision

Newly restored entities use Objecting embeddable traits for generic identity/audit/state/delete fields instead of duplicating local `ObjectAuditTrait` / `ObjectTrait` behavior from the old monolith.

Existing runtime entities with already-used payment schema columns were not mechanically rewritten to Objecting embeddables in this pass to avoid breaking repository/query code without a full Doctrine metadata validation host.

## Retired files

- `Paying/Migrations/**`

Run `remove-paying-retired-files.ps1 -Apply` after applying the entity-first patch.
