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

Manual equivalent:
```bash
rm -f var/payment.test.data.sqlite var/payment.test.infra.sqlite
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --group=payment --no-interaction
```

## Tests
```bash
composer test
composer test:unit
composer test:functional
composer test:e2e
```

## Pipeline
Local pipeline (shell):
```bash
composer pipeline:local:sh
```

Extended pipeline (smokes + reports):
```bash
bash tools/ci/run-payment-local-pipeline.sh --include-smokes --include-reports
```

## Demo flow
1. Start app.
2. Create/start payment via API or `/payment/console`.
3. Send provider webhook (`/webhook/stripe` or `/webhook/paypal`).
4. Process outbox:
   ```bash
   php bin/console payment:outbox:process
   ```
5. (Optional) Consume queue:
   ```bash
   php bin/console messenger:consume payment_events_in -vv
   ```
6. Inspect `/status`, `/metrics`, `/payment/dlq`.

CLI helper:
```bash
php bin/console payment:e2e:demo
```
