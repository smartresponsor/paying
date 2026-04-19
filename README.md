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

Documentation surfaces are intentionally split:

- Antora producer pages: `docs/antora.yml` + `docs/modules/ROOT/pages/`
- Nelmio/OpenAPI HTTP contract generation: runtime publication via `/api/docs`, `/api/docs.json`, `/api/docs.yaml`
- Swagger UI: browser-facing viewer for the Nelmio/OpenAPI contract, not a separate documentation system
- Exported OpenAPI artifact: `docs/api/openapi.yaml`
- Doctum generated code reference: `doctum.php` -> `docs/generated/doctum/`

Canonical narrative entry set:

- [INSTALL](docs/INSTALL.md)
- [ARCHITECTURE](docs/ARCHITECTURE.md)
- [OPERATIONS](docs/OPERATIONS.md)
- [API](docs/API.md)
- [LIMITS](docs/LIMITS.md)
- [PROOF_PACK](docs/PROOF_PACK.md)

Antora producer entry pages mirror that split and stay intentionally thin:

- `docs/modules/ROOT/pages/index.adoc`
- `docs/modules/ROOT/pages/architecture.adoc`
- `docs/modules/ROOT/pages/install.adoc`
- `docs/modules/ROOT/pages/operations.adoc`
- `docs/modules/ROOT/pages/limits.adoc`
- `docs/modules/ROOT/pages/proof-pack.adoc`
- `docs/modules/ROOT/pages/api.adoc`
- `docs/modules/ROOT/pages/code-reference.adoc`

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


## Dependency-oriented installability

This component now exposes a canonical Symfony bundle/export scaffold:

- `App\PayingBundle`
- `App\DependencyInjection\Configuration`
- `App\DependencyInjection\PayingExtension`

Early compile-phase parameters intentionally remain in `config/services.yaml`
because `framework`, `doctrine`, and `messenger` package config need them before
late extension-populated parameters would exist:

- `paying.messenger.dsn`
- `paying.storage.data_server_version`
- `paying.storage.infra_server_version`


Simple interface aliases are imported from `config/services/payment_aliases.yaml`
to keep the root `config/services.yaml` focused on early parameters and explicit
runtime wiring.
