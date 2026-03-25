# Payment (Smartresponsor)

Canonical documentation package:
- [INSTALL](docs/INSTALL.md)
- [ARCHITECTURE](docs/ARCHITECTURE.md)
- [OPERATIONS](docs/OPERATIONS.md)
- [API](docs/API.md)
- [LIMITS](docs/LIMITS.md)

## Honest portrait

### What this component does
- Provides payment lifecycle orchestration for create/start/finalize/refund/read flows.
- Exposes three canonical surfaces:
  - API (JSON HTTP)
  - UI (Symfony Forms + Twig + Bootstrap operator console)
  - CLI (operational commands for outbox and demo flow)
- Stores user/business payment data in PostgreSQL and infrastructure/operational data in SQLite.
- Supports webhook ingest, idempotency, outbox publishing, and health/metrics endpoints.

### What this component does **not** do
- Does not provide real checkout UI pages for end customers.
- Does not guarantee settlement/accounting/reconciliation with external ERP systems out of the box.
- Does not implement every provider in every flow (provider support is intentionally asymmetric by surface).
- Does not replace gateway-native dispute/chargeback tooling.

### Real provider support (current truth)
- Payment provider router (start/finalize/refund/reconcile): `internal`, `stripe`.
- Dedicated webhook ingest endpoints: `stripe`, `paypal`.
- Generic webhook verifier supports signatures for `stripe`, `adyen`; unknown providers can be allowed only via env flag.

## Canonical surface
- **Canonical UI**: `/payment/console` (payment list + card, create/start/finalize/refund actions, webhook visibility, filter/search, links to OpenAPI/status/metrics).
- **Canonical API**: `/api/payments`, `/api/payments/{id}`, `/api/payments/{id}/refund`, `/payment/start`, `/payment/finalize/{id}`, `/payment/webhook/{provider}`, `/webhook/stripe`, `/webhook/paypal`, `/status`, `/metrics`, `/payment/dlq`.
- **Canonical CLI**: `payment:outbox:process`, `payment:e2e:demo`.

## Runtime story (short)
- Install: see `docs/INSTALL.md`.
- Env + DB topology: see `docs/INSTALL.md`.
- Bootstrap/reset/tests/pipeline: see `docs/OPERATIONS.md`.
- Demo flow: see `docs/OPERATIONS.md`.
