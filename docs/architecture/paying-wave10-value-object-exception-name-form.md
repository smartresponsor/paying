# Paying Wave 10 — value-object and exception name-form canonicalization

Status: prepared as a touched-files patch.

## Purpose

Wave 10 closes the remaining generic-looking value-object and outbox exception names that still escaped the `Payment*` component prefix rule. The change keeps the existing Symfony-oriented layout and does not introduce a `/src/Domain` tree or a ports/adapters rewrite.

## Canonicalized symbols

- `Money` → `PaymentMoney`
- `GatewayCode` → `PaymentGatewayCode`
- `TransactionId` → `PaymentTransactionId`
- `OutboxOperationException` → `PaymentOutboxOperationException`
- `MoneyTest` → `PaymentMoneyTest`

## Non-scope

- No route, command, table, or provider payload names are changed.
- No Entity/Doctrine schema decision is changed.
- No repository-wide cleanup or cumulative snapshot is produced.
- Legacy files are retired only by the apply script after backup and SHA-256 verification.

## Verification

Run:

```bash
composer dump-autoload
composer report:value-object-exception-name-form
composer report:legacy-duplicate-retirement
composer test:unit
```
