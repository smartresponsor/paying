# Payment release-readiness snapshot

// Marketing America Corp. Oleksandr Tishchenko

## Current state

The current Payment slice is no longer a cleanup candidate. It is now an App-owned Symfony component with:

- controller-owned HTTP contour for create, read, start, finalize, and refund;
- Nelmio/OpenAPI foundation and documented endpoints;
- Twig/Form/Bootstrap internal console surface;
- fixture baseline for payments, gateways, methods, and webhook logs;
- owned CLI contour with registration and execution smoke;
- outbox, DLQ, reconciliation, projection, and webhook handling seams.

## What is already proven

### HTTP/UI

- create endpoint functional coverage;
- start/finalize validation coverage;
- read/refund validation coverage;
- console render smoke;
- console submit flow smoke;
- access smoke for read/write surfaces.

### CLI

- command registration proof for owned Payment commands;
- execution smoke for projection, outbox, reconciliation, DLQ replay, idempotency purge, SLA reporting, and gate SLO.

### Runtime/documentation

- post-API-Platform controller-owned runtime contour;
- Nelmio/OpenAPI routes at `/api/docs` and `/api/docs.json`;
- repo-owned engineering docs for dependency audit, vertical matrix, access matrix, CLI ownership, and CLI execution
  smoke.

## Remaining gaps before calling this release-ready

1. Real fixture bootstrap proof around `doctrine:fixtures:load` in a booted application context.
2. Stronger container smoke around `lint:container` and bundle wiring in a fully installed environment.
3. Broader behavioural proof for webhook -> outbox -> consumer -> reconciliation in one continuous integration path.
4. Wider UI proof for operator-only read/diagnostic pages beyond the console baseline.
5. phpDocumentor/DocBlock refresh for the latest post-API-Platform architecture.
6. Final documentation pass that consolidates README, architecture docs, and operational runbooks.

## Recommended next phase

The next useful phase is no longer structural cleanup. It is release-hardening:

- prove fixture/bootstrap loading in a booted environment;
- widen behavioural vertical tests;
- refresh documentation as a cohesive post-cleanup package;
- define the green gate for local and CI execution.

## Post-wave-31 triage update

The current slice now has a stronger proof contour across HTTP, UI and CLI than earlier release-readiness snapshots.
However, three gaps remain the main blockers before any stronger readiness claim:

1. fixture/bootstrap execution proof is not yet closed;
2. webhook -> outbox -> consumer is still not proved as one integrated vertical;
3. the QA/documentation toolchain is still incomplete because the slice has no CI, no composer lock, no phpDocumentor
   config, and no installed php-cs-fixer package.

See also:

- `docs/architecture/payment-technical-readiness-review.md`
- `docs/architecture/payment-priority-next-tracks.md`

## Fixture/bootstrap proof update

The repository now owns grouped Payment fixtures, explicit Composer fixture entry points, and dataset/config smoke
tests. Full installed-runtime proof is still pending.

## Wave 34 update

The current slice now includes integrated proof for the webhook -> outbox -> consumer contour via controller-owned
provider endpoints and the owned outbox/consumer path. This reduces the largest behavioral gap from the earlier
readiness review, while still leaving installed-runtime proof as a release-hardening concern.

## Wave 35 update

Quality/documentation closure is now materially stronger than in earlier waves:

- repository-owned CI workflow exists
- phpDocumentor is declared and configured
- coding-standard tool dependency is declared
- a single `composer qa` entry point exists

The main remaining release-hardening gap is installed-runtime proof rather than repository configuration ownership.

## Wave 36 update

The repository now has a consolidated release-readiness portrait and a dedicated installed-runtime proof track.

This sharpens the post-wave-35 conclusion: the main remaining gap is no longer structural, behavioral, or documentation
ownership. It is reproducible installed-runtime proof.
