# Payment technical readiness review

// Marketing America Corp. Oleksandr Tishchenko

## Scope of this review

This review is based strictly on the current cumulative slice after wave 31.
It is a technical triage document, not a release approval.

## What is already materially in place

The current slice already has an app-owned Symfony contour:

- controller-owned HTTP/API layer under `src/Controller/Payment`
- dedicated provider webhook controllers under `src/Controller/Webhook`
- payment aggregate and supporting entities under `src/Entity/Payment`
- repository, service, infrastructure and message layers under `src/*/Payment`
- Nelmio/OpenAPI foundation under `config/packages/nelmio_api_doc.yaml`
- fixture classes under `src/Infrastructure/Payment/Fixture`
- Twig/Form internal console under `templates/payment/console.html.twig`
- owned operational CLI layer under `src/Infrastructure/Payment/Console`
- PHPUnit harness through `phpunit.xml.dist` and `tests/bootstrap.php`

The current slice contains:

- 22 PHPUnit test files under `tests/Payment`
- 8 owned operational commands under `src/Infrastructure/Payment/Console`
- controller-owned API endpoints for create, read, start, finalize and refund
- controller-owned internal UI flows for create, start, finalize and refund

## What is already proved at a useful level

The current proof contour is no longer just structural.
There is evidence in the slice for:

- API access and validation smoke
- a connected create -> start -> finalize -> read -> refund functional vertical
- internal console page rendering and submit-flow smoke
- CLI registration smoke
- CLI execution smoke for projection, outbox, reconcile, DLQ replay, idempotency purge, SLA report and gate/SLO
- unit coverage around webhook payload normalization, reconciliation logic and outbox retry behavior

## What is still not convincingly proved

### 1. Fixture execution proof is still missing

Fixtures exist in the repository, but the current slice does not yet prove the full bootstrap path around:

- `doctrine:database:create` / schema availability
- `doctrine:migrations:migrate`
- `doctrine:fixtures:load`
- post-load UI/API/CLI behavior against that loaded state

The repository is therefore fixture-capable, but not yet fixture-proved.

### 2. Webhook -> outbox -> consumer remains the highest-value missing vertical proof

The component has all the structural pieces:

- webhook controllers
- signature/verifier/normalizer layer
- webhook log
- outbox message/publisher/worker
- consumer and reconciliation

However, the current slice still lacks one decisive integrated proof that executes the full chain from incoming webhook through normalized outbox message into consumer-driven payment mutation.

### 3. Quality gates are present as intent, but not closed as an operational system

The repository contains:

- `.php-cs-fixer.dist.php`
- `phpstan.neon`
- `rector.php`
- composer scripts for `cs`, `stan`, `rector`, `lint:yaml`, `lint:container`

But the current slice still has major operational gaps:

- no `composer.lock`
- no GitHub CI workflow in the slice
- no documented green baseline output
- no baseline/strictness strategy for PHPStan
- no installed `friendsofphp/php-cs-fixer` package in `composer.json`
- no phpDocumentor package/configuration in the slice yet

### 4. Documentation is improved, but still not complete against the target state

The current slice already has strong engineering docs, but still lacks:

- phpDocumentor project configuration
- explicit DocBlock coverage plan enforced in code review
- a final post-hardening README that explains boot, migrate, load fixtures, run smoke, and inspect docs as one canonical journey

## Risk-based next priorities

### Priority A — prove bootstrap and fixtures

The next highest-value proof is to harden and prove the bootstrap path:

1. database/app storage boot
2. migrations apply cleanly
3. fixtures load cleanly
4. console/UI/API can operate on the loaded fixture state

### Priority B — prove webhook to consumer vertical

The next business-critical proof is the full asynchronous vertical:

1. webhook request enters controller
2. signature verification succeeds
3. payload is normalized
4. webhook log persists
5. outbox message persists
6. consumer receives normalized transport message
7. payment aggregate changes persist correctly

### Priority C — close the quality/documentation system

The next engineering-hardening step after proof contour is to close the operational quality loop:

1. install missing QA/doc tooling in `composer.json`
2. add CI workflow(s)
3. add phpDocumentor config
4. define and document a green pipeline

## Triage verdict

The component is no longer a cleanup candidate.
It is now a hardening/proof candidate with a meaningful controller-owned, UI-owned and CLI-owned execution contour.

The most valuable next work is not more structural cleanup.
It is proof of:

- fixture/bootstrap viability
- webhook -> outbox -> consumer execution
- closed quality/documentation pipeline
