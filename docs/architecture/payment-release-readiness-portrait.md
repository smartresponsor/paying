# Payment release-readiness portrait

// Marketing America Corp. Oleksandr Tishchenko

## What the component is now

The current Payment slice is an App-owned Symfony payment execution component rather than a cleanup candidate or an API
Platform resource wrapper.

It now owns these contours directly:

- controller-owned HTTP endpoints for create, read, start, finalize, and refund;
- dedicated provider webhook endpoints for Stripe and PayPal;
- webhook normalization, webhook logging, outbox persistence, consumer handling, reconciliation, and order-sync seams;
- internal Twig/Form/Bootstrap console flows for create, start, finalize, and refund;
- projection, DLQ, outbox, reconciliation, idempotency, and SLO CLI commands;
- Nelmio/OpenAPI documentation entrypoints;
- grouped fixtures for payments, gateways, methods, and webhook logs;
- repo-owned QA and architecture documentation.

## What is materially proven

### 1. HTTP proof

The current slice has controller-owned app routes with functional coverage around:

- create;
- read;
- start;
- finalize;
- refund;
- validation and access behavior.

### 2. UI proof

The current slice has an internal operator console with:

- render smoke;
- submit-flow smoke for create, start, finalize, and refund;
- recent-payments read-side table.

### 3. CLI proof

The current slice has both registration and execution smoke for owned commands around:

- projection sync/rebuild;
- outbox run;
- reconciliation;
- DLQ replay;
- idempotency purge;
- SLA reporting;
- gate SLO.

### 4. Webhook behavioral proof

The current slice now has integrated proof for:

- Stripe captured path;
- PayPal refunded path;
- webhook log creation;
- outbox message creation;
- transport publication;
- consumer handling;
- payment aggregate mutation;
- order-sync seam mutation.

### 5. Documentation and ownership proof

The current slice now owns:

- Nelmio/OpenAPI routes;
- phpDocumentor config;
- QA scripts;
- a repository CI workflow;
- release-readiness and quality-closure docs.

## What is still not fully proven

### 1. Installed-runtime proof

The repository now owns the required configuration and scripts, but the current slice still does not prove the full
installed runtime path end-to-end:

- `composer install` from a lock-backed dependency graph;
- `doctrine:migrations:migrate` against real data/infra stores;
- `doctrine:fixtures:load` in a booted environment;
- console, UI, API, and webhook operation on the installed dataset;
- CI execution against that same installed environment.

This is now the most important remaining hardening track.

### 2. Final docs consolidation

The repository has many strong docs, but they still read as a layered trail of waves rather than one final consolidated
operator/developer package.

### 3. Lock-backed reproducibility

`composer.lock` is still absent from the current slice, so declared ownership is stronger than actual reproducible
installation proof.

## Practical readiness reading

### What can be said confidently

The component is now:

- structurally canonical enough to continue real development;
- behaviorally strong enough to support meaningful Payment vertical work;
- documented enough to hand off to the next hardening phase;
- no longer blocked by API Platform tail, parallel roots, or major legacy naming drift.

### What should not yet be claimed

It should **not** yet be described as fully release-ready in an installed-runtime sense.

That stronger claim should wait until the installed-runtime proof track is completed.

## Recommended next phase

The next phase should be called **installed-runtime hardening**, not cleanup and not generic refactoring.

That phase should close these items in order:

1. lock-backed dependency installation proof;
2. migration + fixtures proof in a booted environment;
3. installed execution proof for API, console, CLI, and webhook flows;
4. final documentation consolidation;
5. final release checklist closure.


- `docs/architecture/payment-test-runtime-bootstrap.md` defines the deterministic test-runtime bootstrap contour.

## Installed runtime preflight

The repository now contains preflight helpers for installed-runtime hardening, but final closure still depends on
committed lock + successful install + migrations + fixtures execution.
