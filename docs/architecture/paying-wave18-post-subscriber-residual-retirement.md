# Paying Wave 18 — Post-subscriber residual retirement

Wave 18 is a cleanup-only wave after the subscriber layer canonicalization.

## Scope

This wave retires residual legacy duplicate files left by earlier name-form waves when their canonical `Payment*` or `Subscriber` placement already exists.

## Invariants

- No route names or route paths are changed.
- No provider keys are changed.
- No scope strings are changed.
- No Doctrine table names are changed.
- No runtime contract is intentionally changed.
- Retirement is guarded by backup and SHA256 verification in the PowerShell applier.

## Added guard

Run:

```bash
composer report:post-subscriber-residual-retirement
```

The report fails while tracked residual files still exist and passes after the retirement portion of the applier completes.

## Retirement count

Tracked residual files: 114.
