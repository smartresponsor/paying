# Paying Wave 13 — Webhook Controller Name-Form Canonicalization

## Scope

Wave 13 canonicalizes provider-specific webhook controller class names so webhook execution boundaries follow the same `Payment*Controller` class-form already used by the main payment controllers.

## Renames

| Legacy file | Canonical file |
| --- | --- |
| `src/Controller/Webhook/PayPalWebhookController.php` | `src/Controller/Webhook/PaymentPayPalWebhookController.php` |
| `src/Controller/Webhook/StripeWebhookController.php` | `src/Controller/Webhook/PaymentStripeWebhookController.php` |

## Runtime contract

Route paths remain unchanged:

- `/webhook/paypal`
- `/webhook/stripe`

The patch updates route and service references only. It does not change payload shape, signature verification, outbox ingestion, or public endpoint semantics.

## Guard

Run:

```bash
composer report:webhook-controller-name-form
```

The report fails when legacy unprefixed webhook controller files or references remain.
