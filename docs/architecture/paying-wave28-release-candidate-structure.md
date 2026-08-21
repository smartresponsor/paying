# Paying Wave 28 — Release-candidate structure guard

Wave 28 is a report-only closure wave for the Paying canonicalization track.

## Scope

This wave does not rename runtime classes, does not change public routes, does not change provider keys, does not change database table names, and does not retire files.

It adds a final structural guard that checks the high-level canonicalization surface created by Waves 1–27:

- baseline structure audit is present;
- canonical name-form summary is present;
- canonical structure closure is present;
- Entity-first persistence report is present;
- source residual name-form report is present;
- test canonical closure report is present;
- the new release-candidate structure report is registered in `composer.json`;
- no `PaymentPayment*` double-prefix drift exists under source, tests, or inspection tools.

## Command

```bash
composer report:release-candidate-structure
```

## Intent

This is a checkpoint before deciding whether to start runtime proof, deeper provider/payment execution consolidation, or final release-candidate cleanup. It intentionally keeps runtime behavior untouched.
