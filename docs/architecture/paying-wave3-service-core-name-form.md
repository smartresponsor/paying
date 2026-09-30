# Paying Wave 3 — Service Core Name-Form Patch

This wave canonicalizes the payment execution core service names without changing the runtime responsibility model.

## Intent

The repository already uses the component namespace `App\Paying\...`, but several core service classes had generic names that were too easy to confuse with framework-wide or ecosystem-wide services. Wave 3 makes these symbols component-explicit.

## Changed class forms

| Legacy | Canonical |
| --- | --- |
| `CircuitBreaker` | `PaymentCircuitBreaker` |
| `Metric` | `PaymentMetric` |
| `ProviderGuard` | `PaymentProviderGuard` |
| `ProviderRouter` | `PaymentProviderRouter` |
| `RetryExecutor` | `PaymentRetryExecutor` |
| `CircuitBreakerInterface` | `PaymentCircuitBreakerInterface` |
| `MetricInterface` | `PaymentMetricInterface` |
| `ProviderGuardInterface` | `PaymentProviderGuardInterface` |
| `ProviderRouterInterface` | `PaymentProviderRouterInterface` |
| `RetryExecutorInterface` | `PaymentRetryExecutorInterface` |

## Boundary rule preserved

The patch keeps the current Symfony component shape:

- concrete services remain under `src/Service`;
- mirrored contracts remain under `src/ServiceInterface`;
- provider implementations remain service-layer collaborators;
- no ports-and-adapters split is introduced;
- no `/src/Domain` layer is introduced.

## Apply behavior

The patch archive contains only touched files. The PowerShell apply script copies changed files and then retires the old unprefixed service/interface/test files only when their SHA-256 hash matches the expected current-slice content.

## Validation

Run:

```bash
composer dump-autoload
composer report:service-core-name-form
composer report:controller-name-form
composer report:canon-structure
composer test:unit
```
