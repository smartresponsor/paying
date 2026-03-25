# ARCHITECTURE

## Layer model
Canonical root: `src/` with namespace `App\`.

Main layers:
- `src/Controller` + `src/ControllerInterface`
- `src/Service` + `src/ServiceInterface`
- `src/Entity`
- `src/Infrastructure` + `src/InfrastructureInterface`
- `src/Repository` + `src/RepositoryInterface`

Pattern in use:
- Thin controllers.
- Service layer for orchestration and provider guards.
- Entity model for payment aggregates and event logs.
- Infrastructure for idempotency, outbox, projection, observability subscribers.

## Storage topology
- Doctrine DBAL default connection: `data` (PostgreSQL expected for user data).
- Secondary connection: `infra` (SQLite expected for app/infrastructure data).

## Eventing/runtime
- Webhook ingest -> normalize/validate -> `payment_webhook_log` dedupe -> outbox message.
- Outbox worker/command publishes to Messenger transport.
- DLQ endpoints allow list/replay behavior.

## Docs position
This file is the canonical architecture narrative. Legacy wave-by-wave narratives are non-canonical historical records.
