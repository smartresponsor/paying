# OPERATIONS

## Install preflight

```bash
composer install:preflight
# or
bash tools/runtime/payment_install_preflight.sh
```

## Bootstrap / reset

Test bootstrap (reset + migrate + fixtures):

```bash
composer test:bootstrap
```

Manual equivalent (SQLite test topology):

```bash
rm -f var/payment.test.data.sqlite var/payment.test.infra.sqlite
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --group=payment --no-interaction
```

Manual equivalent (PostgreSQL user-data topology, local or Docker):

```bash
export DATABASE_URL="pgsql://app:app@127.0.0.1:5432/payment_test"
# or in docker-compose app container:
# export DATABASE_URL="pgsql://app:app@pg:5432/payment_test"
composer test:bootstrap
```

## Tests and QA

```bash
composer test
composer test:unit
composer test:functional
composer test:e2e
npm install
npx playwright install --with-deps chromium
npm run test:playwright
composer qa:style
composer qa:static
composer qa:test
composer qa:smoke
composer qa:report
```

## Pipeline

Local pipeline:

```bash
composer pipeline:local:sh
```

Includes Playwright Chromium UI run (`composer test:ui:playwright`), so ensure Node dependencies and browser are
installed before running the pipeline.

Full/strict contour (smokes + reports + fail-on-errors):

```bash
bash tools/ci/run-payment-local-pipeline.sh --include-smokes --include-reports --fail-on-errors
```

## Reconciliation walkthrough

1. Start or finalize a payment.
2. Deliver a webhook (`/webhook/stripe` or `/webhook/paypal`).
3. Process outbox:
   ```bash
   php bin/console payment:outbox:process --limit=50 --retry
   ```
4. (Optional) consume transport:
   ```bash
   php bin/console messenger:consume payment_events_in -vv
   ```
5. Validate payment state via `GET /api/payments/{id}` and UI payment card.

## Webhook log walkthrough

1. POST a valid webhook payload to provider endpoint.
2. Verify dedupe/status behavior in `payment_webhook_log` and UI webhook visibility table.
3. Re-send same event ID and confirm duplicate behavior (no double processing).

## Outbox / DLQ / retry proof

1. Trigger event ingestion.
2. Run outbox processor command.
3. Check `/payment/dlq` list endpoint for failed items.
4. Replay selected item:
   ```bash
   curl -X POST http://localhost:8000/payment/dlq/replay/{id}
   ```
5. Re-run outbox processor with `--retry` and validate state.

## SLA and report commands

```bash
composer report:runtime-proof
composer report:route-inventory
composer install:proof:checklist
composer proof:pack:sh
```

## Canonical proof pack

Use `docs/PROOF_PACK.md` for the single RC readiness flow and artifact contract.
