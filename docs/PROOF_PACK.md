# PROOF PACK (Internal RC)

This is the canonical proof entrypoint for runtime + operations readiness.

## Goal
Produce one reproducible artifact set that allows saying:
- install path is clean,
- configuration path is predictable,
- QA/tests/pipeline are green,
- operator walkthrough is executable.

## Canonical command
```bash
composer proof:pack:sh
```

## Artifact contract
Generated under:
- `var/report/proof-pack/<UTC-timestamp>/`
- `var/report/proof-pack/latest/`

Expected files:
- `summary.md` — compact pass/fail table
- `report.json` — machine-readable status and step metadata
- `logs/*.log` — per-step logs
- `logs/*.cmd.txt` — exact executed commands

## Included steps
1. `composer install:preflight`
2. `composer qa:style`
3. `composer qa:static`
4. `composer qa:test`
5. `composer qa:smoke`
6. `composer qa:report`
7. strict local pipeline:
   ```bash
   bash tools/ci/run-payment-local-pipeline.sh --include-smokes --include-reports --fail-on-errors
   ```

## Operator walkthrough (canonical)
1. Bootstrap runtime:
   ```bash
   composer install
   cp .env.example .env
   php bin/console doctrine:migrations:migrate --no-interaction
   ```
2. Open UI `/payment/console` and create/start payment.
3. Deliver webhook to `/webhook/stripe` or `/webhook/paypal`.
4. Process outbox:
   ```bash
   php bin/console payment:outbox:process --limit=50 --retry
   ```
5. Inspect queue lag and failures via `/payment/dlq`, then replay as needed.
6. Validate health/metrics with `/status` and `/metrics`.

## Internal RC acceptance gate
Internal RC is declared only when all proof-pack steps are green in `summary.md`.
