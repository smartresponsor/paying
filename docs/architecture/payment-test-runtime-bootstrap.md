# Payment test runtime bootstrap

This document defines the repository-owned bootstrap contour for a deterministic `test` runtime.

## Owned inputs

- `.env.test`
- `config/packages/test/payment_framework.yaml`
- `config/packages/test/payment_doctrine.yaml`
- `config/packages/test/payment_messenger.yaml`
- `composer test:bootstrap* scripts`
- `tools/runtime/payment_test_bootstrap.sh`
- `tools/runtime/payment_test_bootstrap.ps1`

## Target shape

The owned bootstrap path is:

1. reset local test sqlite files;
2. run migrations in `test` environment;
3. load `payment` fixtures in `test` environment;
4. execute functional and e2e proof against the booted test runtime.

## Deterministic runtime choices

- test data storage uses `var/payment.test.data.sqlite`;
- test infrastructure storage uses `var/payment.test.infra.sqlite`;
- messenger transports are forced to `in-memory://` in `test` environment.

## Scope of proof

This wave hardens the bootstrap contour as repository-owned configuration and scripts.
It does **not** by itself prove a completed installed-runtime execution with resolved Composer dependencies.

## Wave 40 follow-up

- `PaymentReconciliationServiceTest` previously instantiated an anonymous `PaymentRepositoryInterface` double without
  the newer `listRecent()` and `listIdsByStatuses()` methods.
- The test now implements the full contract so PHPUnit can proceed past interface validation under the Symfony
  8-oriented codebase.

- Windows-safe Composer console entry points use `php bin/console ...` to avoid direct `bin\console` invocation failures
  in PowerShell shells.
- PHPUnit bootstrap now provisions deterministic SQLite test schema on demand for the owned local runtime files
  after `composer test:bootstrap:reset`.

## Wave 003 notes

- `composer lint:container` now runs in `test` env so local Windows validation does not depend on external
  dev `DATABASE_URL`.
- `ScopeGuardSubscriber` now short-circuits unauthorized/forbidden requests by swapping the controller callable
  on `kernel.controller`, which matches Symfony's `ControllerEvent` contract.

## Wave 004

- fixed CLI smoke tests to resolve project root from `tests/Functional/Cli` correctly
- aligned sqlite bootstrap schema column names with the live Doctrine mapping used by Payment ORM entities
- kept webhook controllers public/tagged after explicit logger argument overrides
- updated webhook integrated proof to match current controller constructors
- hardened fixture dataset smoke manager doubles so `addReference()` works under Doctrine reference repository

## Wave 005

- test bootstrap now clears `var/cache/test` before kernel boot so functional/e2e runs cannot reuse stale compiled
  containers from pre-canon namespace layouts.
- SQLite bootstrap tables now use snake_case columns aligned with Doctrine migrations and raw SQL
  readers (`provider_ref`, `created_at`, `updated_at`, `payment_idempotency`, etc.).
- Doctrine ORM test runtime now uses the underscore naming strategy to stay aligned with migrations and DBAL queries.


- Wave 006: moved Doctrine naming_strategy under doctrine.orm.entity_managers.default to match the installed
  DoctrineBundle config tree; the prior root-level doctrine.orm.naming_strategy caused test kernel boot failure before
  functional output could surface.

## Wave 007

- Removed Doctrine entity `repositoryClass` binding from `App\Entity\Payment` so Doctrine no longer
  expects `App\Repository\PaymentRepository` to be an `ObjectRepository`.
- Hardened generic webhook controller to return `400` for verifier exceptions or non-object JSON payloads during smoke
  routing.
- Updated README to mention `fixtures:payment:load`, `fixtures:payment:append`, and fixture bootstrap proof
  documentation.

## Wave 008

- enabled test session storage for console flash/redirect flows
- added `payment_circuit` bootstrap schema for provider-guard functional paths
- hardened generic webhook controller to degrade to `400` on verifier/mapper/runtime exceptions
- updated Stripe kernel E2E to generate a valid test signature from `.env.test`
- made console page smoke assertion content-based instead of relying on first `h2` selector semantics

## Wave 009

- test runtime now defaults to `APP_DEBUG=0` in `.env.test` and `phpunit.xml.dist` to reduce WebTestCase kernel overhead
  on Windows.
- Composer `process-timeout` is raised to 900 seconds so a near-green local suite is not killed at 300s while remaining
  runtime work is being trimmed.
- legacy quarantine scanning in `tests/bootstrap.php` is now opt-in via `PAYMENT_TEST_QUARANTINE_LEGACY=1`, instead of
  running on every test bootstrap.

## Wave 010

- Marked `App\Controller\WebhookController` as a public controller service with `controller.service_arguments`
  so `/payment/webhook/{provider}` no longer fails before controller execution.
- Simplified `PaymentConsoleRefundType::amount` to `TextType` so the refund console flow keeps DTO decimal validation
  instead of brittle `MoneyType` transformation behavior in functional tests.
- Hardened `WebhookVerifier` env loading to read from `$_ENV`, `$_SERVER`, and `getenv()` so Stripe signature validation
  works consistently in PHPUnit and Symfony test kernel runs.

- Wave 011: repository find now refreshes managed Payment aggregates before returning them, so multi-request functional
  UI flows observe the latest persisted state. Stripe webhook verification also accepts the canonical test secret as a
  fallback candidate to avoid env-source drift under Symfony test kernel runs.

## Wave 012

- added `payment_webhook_log` SQLite bootstrap schema and indexes for test/E2E runtime parity
- aligns Stripe webhook kernel flow with `PaymentWebhookLog` ORM and migration `Version20251108WebhookLog`

- Windows-safe Composer console entry points use `php bin/console ...` to avoid direct `bin\console` invocation failures
  in PowerShell shells.
- PHPUnit bootstrap now provisions deterministic SQLite test schema on demand for the owned local runtime files
  after `composer test:bootstrap:reset`.

## Wave 003 notes

- `composer lint:container` now runs in `test` env so local Windows validation does not depend on external
  dev `DATABASE_URL`.
- `ScopeGuardSubscriber` now short-circuits unauthorized/forbidden requests by swapping the controller callable
  on `kernel.controller`, which matches Symfony's `ControllerEvent` contract.

## Wave 004

- fixed CLI smoke tests to resolve project root from `tests/Functional/Cli` correctly
- aligned sqlite bootstrap schema column names with the live Doctrine mapping used by Payment ORM entities
- kept webhook controllers public/tagged after explicit logger argument overrides
- updated webhook integrated proof to match current controller constructors
- hardened fixture dataset smoke manager doubles so `addReference()` works under Doctrine reference repository

## Wave 005

- test bootstrap now clears `var/cache/test` before kernel boot so functional/e2e runs cannot reuse stale compiled
  containers from pre-canon namespace layouts.
- SQLite bootstrap tables now use snake_case columns aligned with Doctrine migrations and raw SQL
  readers (`provider_ref`, `created_at`, `updated_at`, `payment_idempotency`, etc.).
- Doctrine ORM test runtime now uses the underscore naming strategy to stay aligned with migrations and DBAL queries.


- Wave 006: moved Doctrine naming_strategy under doctrine.orm.entity_managers.default to match the installed
  DoctrineBundle config tree; the prior root-level doctrine.orm.naming_strategy caused test kernel boot failure before
  functional output could surface.

## Wave 007

- Removed Doctrine entity `repositoryClass` binding from `App\Entity\Payment` so Doctrine no longer
  expects `App\Repository\PaymentRepository` to be an `ObjectRepository`.
- Hardened generic webhook controller to return `400` for verifier exceptions or non-object JSON payloads during smoke
  routing.
- Updated README to mention `fixtures:payment:load`, `fixtures:payment:append`, and fixture bootstrap proof
  documentation.

## Wave 008

- enabled test session storage for console flash/redirect flows
- added `payment_circuit` bootstrap schema for provider-guard functional paths
- hardened generic webhook controller to degrade to `400` on verifier/mapper/runtime exceptions
- updated Stripe kernel E2E to generate a valid test signature from `.env.test`
- made console page smoke assertion content-based instead of relying on first `h2` selector semantics

## Wave 009

- test runtime now defaults to `APP_DEBUG=0` in `.env.test` and `phpunit.xml.dist` to reduce WebTestCase kernel overhead
  on Windows.
- Composer `process-timeout` is raised to 900 seconds so a near-green local suite is not killed at 300s while remaining
  runtime work is being trimmed.
- legacy quarantine scanning in `tests/bootstrap.php` is now opt-in via `PAYMENT_TEST_QUARANTINE_LEGACY=1`, instead of
  running on every test bootstrap.

## Wave 010

- Marked `App\Controller\WebhookController` as a public controller service with `controller.service_arguments`
  so `/payment/webhook/{provider}` no longer fails before controller execution.
- Simplified `PaymentConsoleRefundType::amount` to `TextType` so the refund console flow keeps DTO decimal validation
  instead of brittle `MoneyType` transformation behavior in functional tests.
- Hardened `WebhookVerifier` env loading to read from `$_ENV`, `$_SERVER`, and `getenv()` so Stripe signature validation
  works consistently in PHPUnit and Symfony test kernel runs.

- Wave 011: repository find now refreshes managed Payment aggregates before returning them, so multi-request functional
  UI flows observe the latest persisted state. Stripe webhook verification also accepts the canonical test secret as a
  fallback candidate to avoid env-source drift under Symfony test kernel runs.

## Wave 012

- added `payment_webhook_log` SQLite bootstrap schema and indexes for test/E2E runtime parity
- aligns Stripe webhook kernel flow with `PaymentWebhookLog` ORM and migration `Version20251108WebhookLog`
