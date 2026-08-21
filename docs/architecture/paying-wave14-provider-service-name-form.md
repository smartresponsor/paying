# Paying Wave 14 — Provider service name-form canonicalization

Wave 14 canonicalizes the remaining first-party payment provider service class names so provider implementations follow the same `Payment*` class/file prefix rule used by the rest of the component.

## Canonical renames

| Legacy class | Canonical class |
| --- | --- |
| `InternalPaymentProvider` | `PaymentInternalProvider` |
| `StripePaymentProvider` | `PaymentStripeProvider` |
| `PayPalPaymentProvider` | `PaymentPayPalProvider` |

The public provider keys remain unchanged:

- `internal`
- `stripe`
- `paypal`

This keeps `PaymentProviderRouter` behavior stable while removing class/file name-form drift.

## Runtime boundary

No route names, Symfony command names, provider keys, Doctrine entities, or public API paths are changed in this wave.

## Guard

Run:

```bash
composer report:provider-service-name-form
```

The guard fails when legacy provider classes or legacy provider references remain in `src`, `tests`, or `config`.
