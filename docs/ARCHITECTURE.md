# ARCHITECTURE

## Layer model

Canonical root: `src/` with namespace `App\Paying\`.

Main technical-role roots:

- `src/Controller` + `src/ControllerInterface`
- `src/Service` + `src/ServiceInterface`
- `src/Entity`
- `src/Repository` + `src/RepositoryInterface`
- `src/Builder`, `src/Factory`, `src/Handler`, `src/Normalizer`, `src/Policy`, `src/Provider`, `src/Verifier`
- `src/Event` and `src/EventSubscriber`

Pattern in use:

- Thin controllers and explicit Symfony technical-role roots.
- Services orchestrate payment lifecycle behavior but do not own Doctrine managers.
- Repositories own direct Doctrine manager access for business and operational persistence.
- Entity model covers payment aggregates, webhook/outbox state, and operational records.
- Event subscribers own kernel/event integration; provider and verifier roots own gateway/security boundaries.

## Storage topology

- Doctrine DBAL default connection: `data` (PostgreSQL expected for user data).
- Secondary connection: `infrastructure` (SQLite expected for operational/internal data).

## Eventing/runtime

- Webhook ingest -> normalize/validate -> `payment_webhook_log` dedupe -> outbox message.
- Outbox worker/command publishes to Messenger transport.
- DLQ endpoints allow list/replay behavior.

## Docs position

This file is the canonical architecture narrative. Legacy wave-by-wave narratives are non-canonical historical records.
