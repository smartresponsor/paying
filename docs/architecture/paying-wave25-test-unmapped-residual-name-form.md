# Paying Wave 25 — Test unmapped residual name-form guard

Wave 25 is a report-only continuation after the mapped test duplicate retirement wave.

It does not rename runtime classes, does not change routes, provider keys, scope strings, or database tables, and does not delete files.

## Purpose

The earlier test retirement wave only removed mapped legacy duplicates where the canonical `Payment*Test` replacement was already known. Some legacy tests can remain because they need an explicit mapping decision before any touched-file retirement is safe.

This wave adds a narrow inspection script that identifies those unmapped residual tests and separates two cases:

* legacy test still present while a canonical candidate exists;
* legacy test present without a canonical file yet, requiring a next-wave mapping or rename.

## Added command

```bash
composer report:test-unmapped-residual-name-form
```

## Safety

This is intentionally a guard-only wave. Any retirement of tests must be handled later as a mapped touched-file wave with backup and hash verification.
