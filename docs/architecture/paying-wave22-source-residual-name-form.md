# Paying Wave 22 — Source Residual Name-Form Guard

Wave 22 adds a report-only closure guard for the source-level residuals left behind by the earlier canonicalization and retirement waves.

Scope:

- covered controller/controller-interface legacy duplicates;
- covered command, infrastructure, subscriber, attribute, value-object, exception, service, provider, gateway, mapper, webhook, and validation duplicates;
- `PaymentPayment*` double-prefix drift.

This wave intentionally does not rename runtime classes, does not change routes, does not change provider keys, does not change Doctrine tables, and does not retire files. It is a verification layer that should pass after the relevant touched-file retirement scripts have been applied.

The new guard is available through:

```bash
composer report:source-residual-name-form
```

If the report fails, the result means one of the previously covered unprefixed legacy source files still exists next to its canonical `Payment*` replacement and should be retired through a touched-file, backup-first patch rather than a repository-wide cleanup.
