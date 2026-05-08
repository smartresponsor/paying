# Paying Wave 19 — Inspection Script Registry Repair

Wave 19 is a report-only registry repair wave.

## Scope

This wave does not rename runtime classes, move services, change routes, alter provider keys, or touch Doctrine tables. It only makes the accumulated canonicalization reports addressable through Composer and adds a guard that verifies the report registry itself.

## Why this wave exists

The previous canonicalization waves added multiple `tools/inspection/*` report files. Some reports were present on disk but were not consistently exposed through `composer.json` after later overlays. That makes the local verification surface weaker than the actual files available in the repository.

## Added/updated commands

- `composer report:residual-legacy-duplicate-retirement`
- `composer report:canonical-name-form`
- `composer report:post-subscriber-residual-retirement`
- `composer report:inspection-script-registry`

## Guard

`tools/inspection/PayingInspectionScriptRegistryReport.php` checks that every canonicalization report from Waves 1 through 19 has:

1. a Composer script entry;
2. the expected `@php tools/php/php84.php ...` command form;
3. a backing report file on disk.

## Runtime contract

No public runtime contract changes are introduced in this wave.
