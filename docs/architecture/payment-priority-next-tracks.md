# Payment priority next tracks

// Marketing America Corp. Oleksandr Tishchenko

## Current recommendation order

### Track 1 — installed-runtime proof

This is now the immediate next track.

Target deliverables:

- committed `composer.lock`;
- `composer install` proof from the owned dependency graph;
- migration + fixture-load proof in a booted environment;
- installed execution proof for API, console, CLI, and webhook flows.

### Track 2 — final documentation consolidation

Once installed-runtime proof exists, the next high-value step is to collapse the accumulated engineering docs into a tighter operator/developer package.

Target deliverables:

- README refreshed around the actually proven install flow;
- release checklist aligned with proven paths;
- architecture docs consolidated around current, not historical, ownership.

### Track 3 — remaining hardening polish

This is the final track after installed proof and docs consolidation.

Target deliverables:

- wider installed-runtime smoke around operator pages and diagnostics;
- selective phpDocumentor/DocBlock enrichment where ownership is still thin;
- final QA gate tuning.

## Why this order

The current slice already has broad structural and behavioral proof.

The remaining uncertainty is concentrated in:

- reproducible installation;
- installed migration + fixture loading;
- installed execution against real stores;
- final document consolidation.

That means installed-runtime proof now reduces more real risk than any further cleanup wave.

## Current references

- `docs/architecture/payment-release-readiness-portrait.md`
- `docs/architecture/payment-installed-runtime-proof-track.md`
- `docs/architecture/payment-release-readiness-snapshot.md`


- `docs/architecture/payment-test-runtime-bootstrap.md` defines the deterministic test-runtime bootstrap contour.


## Installed-runtime preflight

Before lock-backed install proof, run the owned preflight contour and close any missing repository/runtime files.
