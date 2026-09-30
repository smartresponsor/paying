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
