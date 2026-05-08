# Paying Wave 2 — Controller Name-Form Canonicalization

## Scope

This wave performs the first small structural cleanup after the baseline audit. It keeps the component namespace as `App\Paying\...` and only canonicalizes ambiguous controller and controller-interface names that did not carry the Payment prefix.

## Renamed HTTP boundary classes

| Previous file | Canonical file |
| --- | --- |
| `src/Controller/StartController.php` | `src/Controller/PaymentStartController.php` |
| `src/Controller/FinalizeController.php` | `src/Controller/PaymentFinalizeController.php` |
| `src/Controller/StatusController.php` | `src/Controller/PaymentStatusController.php` |
| `src/Controller/WebhookController.php` | `src/Controller/PaymentWebhookController.php` |
| `src/Controller/MetricController.php` | `src/Controller/PaymentMetricController.php` |
| `src/Controller/DlqController.php` | `src/Controller/PaymentDlqController.php` |

The mirrored interface layer was renamed with the same prefix rule under `src/ControllerInterface/`.

## Why this wave is intentionally narrow

The repository contains richer provider, gateway, reconciliation, outbox, idempotency, console, and webhook surfaces. Renaming all of them in one pass would create a high-risk patch with many indirect references. This wave only fixes the most visible HTTP boundary drift and leaves provider/gateway/service decomposition for the next milestone wave.

## Validation commands

```bash
composer dump-autoload
composer report:canon-structure
composer report:controller-name-form
composer test:unit
```

When runtime dependencies are available, also run:

```bash
composer lint
composer stan
```
