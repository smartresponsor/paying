# Payment installed-runtime proof track

// Marketing America Corp. Oleksandr Tishchenko

## Goal

Close the remaining gap between repository-owned configuration and true installed-runtime proof.

## What this track must prove

### Dependency proof
- `composer install` succeeds from repository-owned dependency declarations.
- A lock-backed dependency graph exists and is committed.
- QA tools and runtime bundles install from that graph.

### Application boot proof
- kernel boots with installed bundles;
- `lint:container` passes in the installed environment;
- routes and services load without relying on hypothetical tooling.

### Data proof
- `doctrine:migrations:migrate --no-interaction` succeeds;
- both data and infra storage contours are initialized correctly;
- `doctrine:fixtures:load --group=payment --no-interaction` succeeds.

### Flow proof
- create/read/start/finalize/refund work against installed stores;
- console submit flows work on fixture-backed state;
- CLI owned commands execute on installed stores;
- webhook -> outbox -> consumer works against installed persistence.

### Documentation proof
- README quick-start matches the real installed flow;
- release checklist matches the proven path;
- docs no longer depend on historical wave context to be usable.

## Minimum deliverables

- committed `composer.lock`;
- installed-runtime README update;
- fixture load smoke in installed context;
- one release-hardening workflow or local proof recipe;
- updated release checklist with explicit proven/unproven items.

## Why this track is next

The current slice already has strong repository ownership and proof across HTTP, UI, CLI, webhook, outbox, and QA configuration.

The largest remaining uncertainty is no longer architecture. It is reproducible installation and execution in a real environment.


- `docs/architecture/payment-test-runtime-bootstrap.md` defines the deterministic test-runtime bootstrap contour.


## Preflight contour

The current slice now includes repository-owned install preflight helpers before lock-backed install proof:

- `tools/runtime/payment_install_preflight.sh`
- `tools/runtime/payment_install_preflight.ps1`
- `composer install:preflight`
- `composer install:proof:checklist`

See also `docs/architecture/payment-install-preflight-proof.md`.

## Documentation tooling boundary

`phpDocumentor` is intentionally no longer modeled as a Composer dependency of the Symfony application graph.
For this repository, API/runtime dependencies and documentation generation dependencies are treated as separate contours.
The owned docs execution path is PHAR-based via:

- `composer docs:phpdoc`
- `composer docs:phpdoc:check`
- `tools/runtime/payment_phpdoc.sh`
- `tools/runtime/payment_phpdoc.ps1`

This avoids cross-major Symfony dependency deadlocks inside the application install graph while preserving repository-owned documentation generation entry points.
