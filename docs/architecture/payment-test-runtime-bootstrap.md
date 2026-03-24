# Payment test runtime bootstrap

This document defines the repository-owned bootstrap contour for a deterministic `test` runtime.

## Owned inputs

- `.env.test`
- `config/packages/test/framework.yaml`
- `config/packages/test/doctrine.yaml`
- `config/packages/test/messenger.yaml`
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
- `PaymentReconciliationServiceTest` previously instantiated an anonymous `PaymentRepositoryInterface` double without the newer `listRecent()` and `listIdsByStatuses()` methods.
- The test now implements the full contract so PHPUnit can proceed past interface validation under the Symfony 8-oriented codebase.
