# Payment app: external critique checklist and resolution plan

## What criticism you can expect

### 1) Responsibility boundaries

- Controllers are not consistently thin: `PaymentConsoleController` handles form lifecycle, orchestration, persistence interactions, and UI composition in one class.
- Exception and error handling policy is mixed in transport layer (`flash`, redirects, and direct runtime logging in controller).
- Domain-level money semantics are partially represented as formatted decimal strings.

### 2) Interoperability with other applications

- OpenAPI description is currently minimal and does not fully describe error envelopes, auth scopes, idempotency semantics, and webhook security contracts.
- Provider integration is selected by runtime string key; integrators may ask for explicit provider capability metadata and versioning commitments.
- Operational integration primitives are present (DLQ/outbox/metrics), but business contract rigor should be raised to match them.

### 3) Code quality and architecture

- Layer model is clearly documented, but architecture rules are mostly convention-based.
- Naming and responsibility overlap between `Service` and `Infrastructure` components can slow onboarding.
- Deterministic behavior and public contract stability are good candidates for stronger automated checks.

## Concrete proposals to resolve each critical area

## A. Responsibility boundaries (thin controllers + explicit use-cases)

### Proposal A1: Move each controller action orchestration into dedicated application handlers

- Create `App\Service\UseCase\PaymentConsole\CreatePaymentHandler`.
- Create `StartPaymentHandler`, `FinalizePaymentHandler`, `RefundPaymentHandler`.
- Keep controllers as: request DTO binding + validation + one handler call + response mapping.

**Acceptance criteria**

- Controller methods are <= 25 lines each.
- No repository writes directly from controller.
- All business exceptions mapped via one error mapper to consistent UI/API errors.

### Proposal A2: Unify transport error strategy

- Replace direct `error_log` in controllers with PSR logger service.
- Introduce common exception-to-message mapper (UI) and exception-to-problem-json mapper (API).

**Acceptance criteria**

- No `error_log(` usage under `src/Controller`.
- One policy table in docs for domain/application/infrastructure exception mapping.

## B. Interoperability (contract-first + versioning)

### Proposal B1: Expand OpenAPI to stable external contract

Add to `docs/api/openapi.yaml`:

- Reusable schemas: `ErrorResponse`, `ValidationError`, `IdempotencyError`, `PaymentState`.
- Explicit non-2xx responses for each route (`400`, `401`, `403`, `404`, `409`, `422`, `429`, `500`).
- Security schemes and required scopes per endpoint.
- Header contracts (`Idempotency-Key`, webhook signature headers).

**Acceptance criteria**

- 100% listed payment routes include request/response schemas + error responses.
- OpenAPI validated in CI.
- Nelmio docs render without schema warnings.

### Proposal B2: Provider capability contract

- Add `PaymentProviderCapabilities` DTO (capture/refund/webhook support flags).
- Extend provider interface with `capabilities(): PaymentProviderCapabilities`.
- Expose capability matrix in docs for integrators.

**Acceptance criteria**

- Unknown provider returns deterministic typed error.
- Capability mismatch returns explicit domain/application error code.

## C. Code quality + architecture enforcement

### Proposal C1: Money semantics hardening

- Introduce a money value object for canonical minor units in domain flow.
- Convert to decimal string only at persistence boundary.
- Add invariant checks (currency code format, non-negative minor amount).

**Acceptance criteria**

- No `number_format(.../100)` in orchestration services.
- Unit tests for money invariants and conversion edge cases.

### Proposal C2: Architecture fitness checks

- Add static rule checks that enforce allowed dependencies:
  - Controller -> ServiceInterface/DTO only.
  - Service -> RepositoryInterface/Entity/ValueObject only.
  - Infrastructure must not be depended on by Controller directly.

**Acceptance criteria**

- CI fails on layer violation.
- Rule set documented in `docs/ARCHITECTURE.md`.

### Proposal C3: Deterministic automated quality gates

- Keep and extend vertical, functional, and e2e smoke tests for payment critical path.
- Add deterministic fixture reset for PostgreSQL/SQLite dual-store runs.
- Add coverage gate for changed files.

**Acceptance criteria**

- Green CI for unit + functional + e2e smoke.
- Re-run stability: three consecutive local runs without flaky failures.

## Recommended execution order (small reviewable commits)

1. Error handling unification in controllers (small diff, fast impact).
2. OpenAPI error/security/idempotency enrichment.
3. Payment use-case handlers extraction from controller actions.
4. Money value object and invariants.
5. Architecture dependency checks in CI.

## Done-definition for this improvement track

- Public API contracts are explicit and versioned.
- Controllers are thin and transport-only.
- Domain invariants are encoded in value objects.
- Layer boundaries are machine-enforced in CI.
- Operational docs and runbook include failure modes and recovery steps.

## Scope closure status (current)

- ✅ C1 partially closed: Money value object introduced in payment creation path with invariant tests.
- ✅ A2 partially closed: direct runtime controller logging replaced with Symfony `LoggerInterface` usage in console refund flow.
- ⏳ A1 in progress: controller still orchestrates multiple use-cases and should be split into dedicated handlers.
- ⏳ B1 in progress: OpenAPI contract still requires full error/security/idempotency expansion.
- ⏳ C2/C3 in progress: architecture dependency rules and deterministic CI gates are not yet fully enforced.

## What is still not closed in substance (evidence-based)

1. **Controllers are still not thin enough**
   - `PaymentConsoleController` still owns full use-case orchestration for create/start/finalize/refund, including DTO flow + repository usage + provider guard calls.
   - Target state: one action -> one application handler call -> response mapping.

2. **OpenAPI/business interoperability remains under-specified**
   - Public schema still needs explicit reusable error contracts, scope mapping, idempotency behavior, and webhook signature contracts across all routes.
   - Target state: every public route has typed request/response + non-2xx problem schema + security scope declaration.

3. **Architecture boundaries are not machine-enforced yet**
   - Layer model is documented but CI fitness rules for forbidden dependencies are not yet part of build gates.
   - Target state: architecture rule violations fail CI deterministically.

4. **Deterministic test gates are incomplete**
   - Unit coverage for Money exists, but integration coverage for failure semantics and contract errors per endpoint should be expanded.
   - Target state: stable unit + functional + e2e smoke checks with deterministic fixtures and flaky protection.

5. **Uniform error contract is still partial**
   - Logging style was improved in console refund flow, but cross-controller error contract normalization is still pending.
   - Target state: shared exception mapper for API + UI with consistent machine-readable codes.

## Remaining steps to full closure (current estimate)

Based on the implemented fixes, the remaining work can be closed in **5 focused engineering steps**:

1. **Complete thin-controller extraction for remaining mixed actions**
   - Move any residual orchestration from API controllers into dedicated handlers.
2. **Finish API contract parity in OpenAPI for all active routes**
   - Ensure every public route has full request/response/error envelopes and examples aligned with runtime behavior.
3. **Promote architecture rules from smoke checks to mandatory CI gates**
   - Run boundary tests (controller/service + service/controller isolation) in CI as required checks.
4. **Add integration regression pack for critical payment paths**
   - Verify create/start/finalize/refund contract semantics across HTTP + persistence layers with deterministic fixtures.
5. **Operational closure**
   - Finalize runbook/README updates for error taxonomy, idempotency behavior, and recovery procedures.

If executed as small reviewable commits, this is roughly **1-2 sprints** depending on CI/setup depth.
