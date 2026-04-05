# Payment (Smartresponsor)

[![Quality CI](https://github.com/smartresponsor/paying/actions/workflows/payment-quality.yaml/badge.svg)](https://github.com/smartresponsor/paying/actions/workflows/payment-quality.yaml)
[![Pages](https://github.com/smartresponsor/paying/actions/workflows/payment-pages.yaml/badge.svg)](https://github.com/smartresponsor/paying/actions/workflows/payment-pages.yaml)
[![Release](https://github.com/smartresponsor/paying/actions/workflows/payment-release.yaml/badge.svg)](https://github.com/smartresponsor/paying/actions/workflows/payment-release.yaml)
[![Latest Release](https://img.shields.io/github/v/release/smartresponsor/paying?display_name=tag)](https://github.com/smartresponsor/paying/releases)

Owner-facing GitHub entrypoints:

- Actions: <https://github.com/smartresponsor/paying/actions>
- Releases: <https://github.com/smartresponsor/paying/releases>
- Tags: <https://github.com/smartresponsor/paying/tags>
- Pages: <https://smartresponsor.github.io/paying/>

Canonical documentation package:

- [INSTALL](docs/INSTALL.md)
- [ARCHITECTURE](docs/ARCHITECTURE.md)
- [OPERATIONS](docs/OPERATIONS.md)
- [API](docs/API.md)
- [LIMITS](docs/LIMITS.md)
- [PROOF_PACK](docs/PROOF_PACK.md)

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

- Payment provider router (start/finalize/refund/reconcile): `internal`, `stripe`, `paypal`.
- Dedicated webhook ingest endpoints: `stripe`, `paypal`.
- Generic webhook verifier supports signatures for `stripe`, `adyen`; unknown providers can be allowed only via env
  flag.

## Canonical surface

- **Canonical UI**: `/payment/console` (payment list + card, create/start/finalize/refund actions, webhook visibility,
  filter/search, links to OpenAPI/status/metrics).
- **Canonical API
  **: `/api/payments`, `/api/payments/{id}`, `/api/payments/{id}/refund`, `/payment/start`, `/payment/finalize/{id}`, `/payment/webhook/{provider}`, `/webhook/stripe`, `/webhook/paypal`, `/status`, `/metrics`, `/payment/dlq`.
- **Canonical CLI**: `payment:outbox:process`, `payment:e2e:demo`.

## Runtime story (short)

- Install: see `docs/INSTALL.md`.
- Env + DB topology: see `docs/INSTALL.md`.
- Bootstrap/reset/tests/pipeline (SQLite defaults + PostgreSQL override for local/Docker): see `docs/OPERATIONS.md`.
- Demo flow: see `docs/OPERATIONS.md`.

## UI e2e coverage strategy

- Symfony Panther remains the canonical PHP-first browser e2e harness for the operator console.
- Playwright (Chromium) adds complementary browser-engine coverage for flows that are not yet covered via Panther.
- Playwright specs and config live under `tests/Playwright/`.
