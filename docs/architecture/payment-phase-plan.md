# Payment hardening phase plan

// Marketing America Corp. Oleksandr Tishchenko

## Phase 01 — dependency and bootstrap normalization

- audit every external import against `composer.json`
- add required runtime/dev packages or remove transitional code paths
- add canonical `phpunit.xml.dist`
- add explicit Composer QA scripts
- add kernel/container smoke commands

## Phase 02 — API Platform evacuation and Nelmio OpenAPI contour

- remove API Platform resources/processors under `src/Api/*`
- replace them with explicit Symfony controllers
- introduce request/response DTOs for documented API contracts
- wire Nelmio ApiDocBundle
- expose canonical documentation endpoint(s)

## Phase 03 — security/access matrix

- define payment roles and operational roles
- map permissions to create/start/finalize/refund/webhook/DLQ/projection actions
- implement voters/guards/policies consistently
- add denial/authorization tests

## Phase 04 — fixtures and minimal UI

- add deterministic fixtures for payment lifecycle states
- add security/demo users if security contour exists
- add Symfony Forms for core payment operations
- add Twig + Bootstrap pages for minimal operational usability

## Phase 05 — CLI and operational contour

- confirm and document CLI ownership for projection/outbox/DLQ/fixtures
- add missing operational commands if needed
- cover commands with functional tests and smoke

## Phase 06 — vertical proof

- prove create/start/finalize/refund/webhook/outbox/projection verticals
- keep the proof layered: unit + functional + integration/e2e
- require documentation coverage for every proven vertical

## Phase 07 — documentation refresh

- update README
- update API documentation
- update internal architecture docs
- update phpDocumentor/DocBlock coverage
- document runtime, fixtures, security, UI, CLI, and test flows
