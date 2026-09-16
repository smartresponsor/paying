# Paying canonical structure audit — current slice 2026-04-30

## Executive result

The repository already contains a broad payment component surface: entities, controllers, DTOs, services, webhooks, outbox, reconciliation, projections, CLI, tests, docs, Docker/deploy assets, and operations material.

The release-candidate problem is structural rather than lack of code:

- namespace surface is coherent: `App\Paying\...`;
- class-form taxonomy is only partially coherent;
- root layout contains loose/generated artifacts that should not remain at repository root;
- execution model shows competing provider/gateway boundaries;
- the component is not yet presented in a strong Entity-first reading order;
- operational assets exist, but root/deploy/docs/ops boundaries need cleanup.

## Observed strengths

- Composer PSR-4 maps `App\Paying\` to `src/`.
- Main Symfony layers exist: `Entity`, `Repository`, `Controller`, `Form`, `Service`, `ServiceInterface`, `Message`, `Event`, `Command`.
- Payment business scope is visible: start/create/finalize/refund/webhook/reconciliation/outbox/projection.
- Docker assets are already under `deploy/docker`; remaining cleanup is around root references and loose files.

## Main risks before RC

### P0 — execution-path ambiguity

There are two visible abstractions:

- `PaymentProviderInterface` / provider services;
- `Gateway\PaymentGatewayInterface` / gateway services.

Before RC, only one model should be authoritative. Recommended direction: keep application/business orchestration in the provider/service layer and treat gateway classes as provider-adapter internals.

### P0 — Entity-first reading order is not formalized

The entity set exists, but the repository does not yet explain itself from storage outward. A reviewer should be able to start from:

- `PaymentEntity`
- `PaymentTransactionEntity`
- `PaymentRefundEntity`
- `PaymentGatewayEntity`
- `PaymentMethodEntity`
- `PaymentOutboxMessageEntity`
- `PaymentWebhookLogEntity`
- `PaymentDlqEntity`

and then understand repositories, services, controllers, messages, projections, and tests.

### P1 — class-form drift

Most files already have useful payment prefixes or suffixes. Remaining drift is concentrated around ambiguous service/boundary terms:

- `PaymentApiJsonBodyDecoder`
- `PaymentCircuitBreaker`
- `PaymentConsoleReadModel`
- `PayPalGateway`
- `StripeGateway`
- `PaymentFixtureFaker`

These should be fixed after the provider/gateway boundary decision, not before it.

### P1 — loose root artifacts

The following root files are not canonical repository-entry files and should be retired by explicit touched-file patching:

- `config-services.yaml`
- `services.yaml`
- `task.txt`
- `touched.txt`

The canonical Symfony service configuration is `config/services.yaml`.

### P2 — generated/static documentation duplication

`docs/site/*.html` appears to be generated output beside source docs. RC should decide whether generated site output belongs in the repository or in build artifacts.

## Proposed canonical target

### Namespace

Keep:

```text
App\Paying\...
```

Do not collapse this component to plain `App\...`.

### Root

Keep only stable entry/config files at root. Runtime, deployment, reports, temporary touched lists, and generated work files must move under typed locations:

- `deploy/` for Docker/deployment assets;
- `ops/` for operations dashboards/alerts/release checklists;
- `tools/` for scripts and inspectors;
- `docs/` for authored documentation;
- `var/` or external CI artifacts for generated runtime output.

### Source tree

Keep type-identifiable top-level layers under `src/`:

```text
src/Attribute
src/Command
src/Controller
src/ControllerInterface
src/DependencyInjection
src/Entity
src/Event
src/Exception
src/Form
src/Infrastructure
src/InfrastructureInterface
src/Message
src/Repository
src/RepositoryInterface
src/Service
src/ServiceInterface
src/ValueObject
```

No custom `src/Domain`, no ports/adapters split, no hexagonal drift.

## Recommended milestone order

1. **Wave 1 — Audit and guardrails**
   - Add report-only structure audit tool.
   - Document canonical target and milestone.
   - Retire exact loose root artifacts with backup.
   - No business class renames yet.

2. **Wave 2 — Root and deploy cleanup**
   - Validate whether `compose.yml` remains root entrypoint or moves to `deploy/docker/compose.yml` with root wrapper.
   - Normalize deploy script references.
   - Remove stale references to retired root service YAML files.

3. **Wave 3 — Entity-first documentation**
   - Add entity map and storage topology document.
   - Align README reading order with entities first.
   - Add table-prefix and DB topology rules.

4. **Wave 4 — Provider/gateway boundary**
   - Choose canonical orchestration path.
   - Make gateway classes internal provider implementation details or merge them into providers.
   - Update DI and tests in one atomic wave.

5. **Wave 5 — Class-form rename wave**
   - Rename ambiguous service classes after provider/gateway decision.
   - Update interfaces, aliases, tests, docs, and references.
   - Avoid partial rename waves that leave duplicate names.

6. **Wave 6 — Projection/reconciliation hardening**
   - Align projection schema, sync service, repository, tests, status/metric consumers.
   - Decide whether order ID is aggregate-owned or an external correlation key.

7. **Wave 7 — RC proof**
   - Run lint/static/unit/functional/smoke proof.
   - Keep runtime proof separate from architecture/business readiness if needed.

## Current patch intent

This wave is intentionally conservative: adds a report-only structure auditor, records the canonical milestone, adds a controlled PowerShell application path, and retires only exact loose root files with backup/hash guard.
