# Paying Wave 23 — Test residual name-form guard

Wave 23 is a report-only canonicalization guard for the Paying test layer.

## Scope

This wave adds a test-layer residual report that checks whether legacy unprefixed tests still sit beside canonical `Payment*Test` replacements.

## Non-goals

- No runtime class rename.
- No route changes.
- No provider key changes.
- No Doctrine table or entity changes.
- No file retirement in this wave.

## Added command

```bash
composer report:test-residual-name-form
```

## Interpretation

The report distinguishes two cases:

- errors: legacy duplicate tests remain next to already-present canonical replacements;
- warnings: legacy tests remain but still need explicit canonical mapping before a later touched-only retirement wave.

This keeps the current wave safe and prepares a later cleanup-only test retirement patch.
