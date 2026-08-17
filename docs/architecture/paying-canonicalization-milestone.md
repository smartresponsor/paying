# Paying canonicalization milestone

## Release-candidate target

Paying should read as a Symfony-native payment component with a coherent `App\Paying` namespace, type-identifiable source layers, entity-first storage model, single payment execution path, explicit async/outbox/webhook boundaries, and clean deploy/ops/docs/tooling separation.

## Milestone

### M1 — Audit baseline and root hygiene

Status: started in this patch.

Deliverables:

- `tools/inspection/PayingCanonicalStructureAudit.php`
- `docs/architecture/paying-canonical-structure-audit.md`
- exact retirement of non-canonical root files:
  - `config-services.yaml`
  - `services.yaml`
  - `task.txt`
  - `touched.txt`

Acceptance:

- audit tool runs in report-only mode;
- no repository-wide overwrite;
- retired root files are backed up by the apply script before deletion;
- canonical Symfony config remains `config/services.yaml`.

### M2 — Entity-first map

Deliverables:

- `docs/architecture/paying-entity-first-map.md`
- README section linking entity model before controllers/services;
- documented DB/table-prefix/storage topology;
- explicit separation of data DB and infrastructure/internal DB.

Acceptance:

- reviewer can understand the component from entities outward;
- no service/controller-first narrative dominates the architecture docs.

### M3 — Provider/gateway boundary decision

Deliverables:

- one canonical payment orchestration boundary;
- provider/gateway responsibility table;
- updated DI aliases;
- tests adjusted to one authority path.

Acceptance:

- no two equal execution paths for the same payment action;
- gateway code is either internal adapter implementation or removed/merged.

### M4 — Class-form rename wave

Candidate decisions, pending M3:

- `PaymentApiJsonBodyDecoder` -> explicit service/decoder form.
- `PaymentCircuitBreaker` -> explicit service/guard form.
- `PaymentConsoleReadModel` -> explicit query/read-model service form.
- gateway classes -> adapter/provider-internal naming after M3.

Acceptance:

- file name, class name, namespace, interface, service alias, and tests are aligned;
- no compatibility duplicate is left unless explicitly documented as temporary.

### M5 — Projection and reconciliation consistency

Deliverables:

- projection schema/read model sync audit;
- reconciliation authority model;
- order ID ownership decision.

Acceptance:

- projection is not a random subset;
- reconciliation and webhook ingestion cannot silently mutate payment truth without audit trail.

### M6 — RC proof pack

Deliverables:

- lint/static/test/smoke proof;
- route inventory;
- OpenAPI/schema check;
- migration/mapping proof.

Acceptance:

- proof output is reproducible;
- runtime proof remains separate from architecture/business readiness score when requested.


## Wave 2 Completed: HTTP Boundary Name-Form

Completed class-form canonicalization for ambiguous top-level controllers and their mirrored controller interfaces:

- `StartController` -> `PaymentStartController`
- `FinalizeController` -> `PaymentFinalizeController`
- `StatusController` -> `PaymentStatusController`
- `WebhookController` -> `PaymentWebhookController`
- `MetricController` -> `PaymentMetricController`
- `DlqController` -> `PaymentDlqController`

This wave deliberately avoided provider/gateway service renames until the provider boundary is reviewed separately.

## Wave 3 — Payment core service name-form canonicalization

Status: prepared as a touched-files patch.

Scope:

- Canonicalizes the core payment execution service names by adding the `Payment` component prefix.
- Keeps the existing Symfony-oriented `src/Service` and `src/ServiceInterface` mirrored layout.
- Does not introduce ports/adapters or a `/src/Domain` layer.
- Retires only the touched legacy unprefixed files through the apply script after SHA-256 verification.

Canonicalized symbols:

- `CircuitBreaker` → `PaymentCircuitBreaker`
- `Metric` → `PaymentMetric`
- `ProviderGuard` → `PaymentProviderGuard`
- `ProviderRouter` → `PaymentProviderRouter`
- `RetryExecutor` → `PaymentRetryExecutor`
- matching `*Interface` symbols receive the same `Payment` prefix.

Verification:

- `composer report:service-core-name-form`
- `composer report:controller-name-form`
- `composer report:canon-structure`
## Wave 4 — API boundary name-form

Status: prepared.

The shared HTTP/API/security helpers have been promoted to explicit `Payment*` name-form:

- `PaymentApiErrorResponseFactory`
- `PaymentApiJsonBodyDecoder`
- `PaymentApiRequestValidator`
- `PaymentValidationErrorMapper`
- `PaymentOidcJwksCache`
- `PaymentTokenVerifier`

Matching interfaces and unit tests follow the same prefix rule. This removes another block of generic utility-looking classes from the component surface while preserving the existing Symfony layer layout.

## Wave 5 — Console Command Name-Form

Status: prepared as touched-files patch.

Scope:

- Canonicalize infrastructure console command classes under `src/Infrastructure/Console` with the `Payment*Command` prefix.
- Keep Symfony command names unchanged, for example `payment:outbox:run`, to avoid operator/runtime contract drift.
- Update functional CLI smoke tests to instantiate the prefixed command classes.
- Add report-only guard `composer report:console-command-name-form`.

Non-scope:

- No repository overwrite.
- No command behavior change.
- No service/provider boundary redesign.



## Wave 6 — Infrastructure Name-Form

Status: prepared as touched-files patch.

Purpose: bring infrastructure support classes and contracts to the canonical `Payment*` name-form without changing command names, route names, payloads, or database table names.

Validation: `composer report:infrastructure-name-form`.

## Wave 7 — business service name-form canonicalization

Status: prepared.

Wave 7 moves the remaining root business services and mirrored interfaces to canonical `Payment*` names while preserving external operator/runtime contracts. Provider and gateway naming remains intentionally separate for a later provider-boundary decision wave.

Validation command: `composer report:business-service-name-form`.

## Wave 8 — Service adapter name-form canonicalization

Wave 8 closes the next naming gap in service adapter/helper classes. Gateway, mapper, order-sync, and webhook helper classes now use the `Payment*` file/class prefix. Residual duplicate root services/interfaces superseded by Wave 7 are retired by hash-checked touched-file deletion only.

## Wave 10 — Value-object and exception name-form canonicalization

Status: prepared as touched-files patch.

Wave 10 promotes the remaining generic value-object and outbox exception names to the component-explicit `Payment*` name-form:

- `PaymentMoney`
- `PaymentGatewayCode`
- `PaymentTransactionId`
- `PaymentOutboxOperationException`
- `PaymentMoneyTest`

The wave preserves the existing Symfony layer layout and external contracts. Legacy files are retired only by the apply script after backup and SHA-256 verification.

Validation command: `composer report:value-object-exception-name-form`.

## Wave 11 — Entity-first persistence map and guard

Status: prepared as touched-files patch.

Wave 11 formalizes the persistence-first reading order and adds a report-only guard for the Paying Doctrine surface.

Added:

- `docs/architecture/paying-entity-first-map.md`
- `tools/inspection/PayingEntityFirstPersistenceReport.php`
- `composer report:entity-first-persistence`

The report verifies that entity files keep `Payment*Entity` form, use explicit Doctrine table names, and that mapped/migrated table names follow the `payment` / `payment_*` prefix canon.

## Wave 13 — Webhook controller name-form canonicalization

Status: prepared.

Provider-specific webhook controllers now have canonical `Payment*Controller` names while preserving public webhook route contracts. This closes the remaining controller-level provider callback drift separately from service adapter naming and Entity-first work.

Guard: `composer report:webhook-controller-name-form`.



## Wave 14 — Provider service name-form canonicalization

Status: prepared.

Scope:

- Canonicalize first-party provider services to `PaymentInternalProvider`, `PaymentStripeProvider`, and `PaymentPayPalProvider`.
- Preserve provider keys: `internal`, `stripe`, and `paypal`.
- Add `composer report:provider-service-name-form` as a report-only guard.

Out of scope:

- Provider contract redesign.
- Gateway adapter redesign.
- Public route or command changes.

## Wave 16 — Attribute name-form canonicalization

Status: prepared.

- Canonicalized `RequireScope` to `PaymentRequireScopeAttribute`.
- Preserved existing payment scope strings and authorization behavior.
- Added `composer report:attribute-name-form` as a report-only guard.

## Wave 17 — Subscriber Layer / Name-Form

Moved payment event/kernel subscribers to `src/Subscriber` and added `composer report:subscriber-layer-name-form`.

## Wave 18 — Post-subscriber residual retirement

Status: prepared. This cleanup-only wave adds a report-only guard and retires residual legacy duplicate files left after the subscriber layer move. It preserves routes, provider keys, scope strings, Doctrine tables, and public command names.

## Wave 19 — Inspection script registry repair

Status: prepared.

Wave 19 repairs the Composer-facing verification surface after the accumulated name-form, layer-form, duplicate-retirement, and Entity-first report waves. It adds a dedicated registry guard and exposes the missing report scripts through `composer.json`.

No runtime class rename, route change, provider-key change, or Doctrine table change is part of this wave.

## Wave 20 — Canonical structure closure guard

Wave 20 adds `PayingCanonicalStructureClosureReport` and Composer script `report:canonical-structure-closure` as a report-only checkpoint. It consolidates the report registry, high-risk legacy retirement expectations, canonical replacement path checks, and double-prefix drift detection.

No runtime contracts are changed in this wave.

## Wave 21 — Application surface name-form guard

Status: prepared. This report-only wave adds `composer report:application-surface-name-form` to keep the Symfony/application surface aligned after the controller, service, infrastructure, provider, subscriber, attribute, and entity-first waves. It checks forms, repositories, repository contracts, and messenger messages/handlers for the `Payment*` component prefix and type-identifying suffixes.

### Wave 22 — Source residual name-form closure guard

Status: report-only closure guard.

Deliverables:

- `tools/inspection/PayingSourceResidualNameFormReport.php`
- `composer report:source-residual-name-form`
- `docs/architecture/paying-wave22-source-residual-name-form.md`

Acceptance:

- previously covered unprefixed source duplicates no longer coexist with canonical `Payment*` replacements;
- `PaymentPayment*` double-prefix drift is absent;
- no route, provider key, scope string, or Doctrine table contract is changed.

## Wave 23 — Test residual name-form guard

- Added `composer report:test-residual-name-form`.
- Added a report-only guard for legacy duplicate test files and `PaymentPayment*` test drift.
- No runtime, route, provider, or database contract changes.


## Wave 24 — Test legacy duplicate retirement

Status: prepared as touched-files patch.

Wave 24 retires mapped legacy unit-test duplicates after their canonical `Payment*Test` replacements were established and guarded by Wave 23. The apply script backs up each touched legacy test and deletes it only when the expected SHA-256 hash matches.

No runtime classes, route contracts, provider keys, scope strings, Doctrine tables, or command names are changed. Remaining unmapped legacy tests are intentionally left for a later canonical mapping wave.

Validation command: `composer report:test-legacy-duplicate-retirement`.

## Wave 25 — Test unmapped residual name-form guard

Status: prepared.

Wave 25 adds a report-only guard for unmapped legacy test residuals after the mapped duplicate retirement step. It creates a safe backlog for the next test mapping/rename/retirement wave without deleting files prematurely.

## Wave 26 — Test unmapped canonicalization

Status: prepared.

Wave 26 canonicalizes the four remaining unmapped unit-test residuals that were left as explicit backlog by Waves 24 and 25. It moves them to `Payment*Test` class/file names and adds `composer report:test-unmapped-canonicalization` as a focused guard.

No runtime class, route, provider key, scope string, Doctrine table, command name, or message contract is changed.


## Wave 27 — test canonical closure guard

Status: prepared. Wave 27 adds `composer report:test-canonical-closure` as the closure guard for the test name-form contour. It checks the Waves 23-26 test reports/scripts, known legacy-to-canonical test pairs, and `PaymentPayment*` drift under `tests/`.

## Wave 28 Completed: Release-Candidate Structure Guard

Wave 28 added a report-only closure guard for the canonicalization track.

Deliverables:

- `tools/inspection/PayingReleaseCandidateStructureReport.php`
- `composer report:release-candidate-structure`
- `docs/architecture/paying-wave28-release-candidate-structure.md`
- `docs/architecture/paying-wave28-patch-manifest.json`

Acceptance:

- no runtime class rename;
- no public route/provider key/database table change;
- no file retirement;
- high-level canonicalization reports and docs are discoverable;
- `PaymentPayment*` drift remains guarded before RC proof work.

## Wave 29 — Packaging/root surface guard

Wave 29 adds a report-only guard for the repository packaging/root surface. It verifies that deployment, Docker, documentation, tooling, and Symfony anchors remain organized under canonical directories and that historical loose root artifacts do not reappear at the repository root.
