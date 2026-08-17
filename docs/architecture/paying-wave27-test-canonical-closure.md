# Paying Wave 27 — test canonical closure guard

Wave 27 closes the Paying test name-form canonization contour started by Waves 23-26.

## Scope

- Add a report-only guard for the canonical test contour.
- Verify the Wave 23-26 test reports are present.
- Verify the Wave 23-26 composer scripts are present.
- Verify mapped and formerly unmapped legacy tests have canonical `Payment*Test` replacements.
- Verify no `PaymentPayment*` double-prefix drift exists under `tests/`.

## Non-scope

- No runtime class rename.
- No route changes.
- No provider key changes.
- No Doctrine table changes.
- No test deletion in this wave.

## Command

```bash
composer report:test-canonical-closure
```

## Follow-up

If this guard fails, the next wave should be a narrow fix against the exact reported residual path instead of a broad test cleanup.
