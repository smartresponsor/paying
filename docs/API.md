# API

## Canonical HTTP endpoints

### Core API

- `POST /api/payments` — create payment
- `GET /api/payments/{id}` — read payment
- `POST /api/payments/{id}/refund` — refund payment

### Lifecycle endpoints

- `POST /payment/start` — start provider flow
- `POST /payment/finalize/{id}` — finalize payment

### Webhooks

- `POST /payment/webhook/{provider}` — generic webhook endpoint
- `POST /webhook/stripe` — Stripe-specific webhook ingest
- `POST /webhook/paypal` — PayPal-specific webhook ingest

### Ops endpoints

- `GET /status`
- `GET /metrics`
- `GET /payment/dlq`
- `POST /payment/dlq/replay/{id}`

### API docs

- `GET /api/docs`
- `GET /api/docs.json`

## Canonical API contract source

- NelmioApiDocBundle is the canonical HTTP contract generator for this repository.
- Swagger UI is the browser-facing viewer for that contract and must remain only a presentation layer, not a separate documentation system.
- Exported OpenAPI source of truth: `docs/api/openapi.yaml`.
- Runtime publication points: `/api/docs`, `/api/docs.json`.
- Antora is the owner/manual documentation entry surface for this repository.
- Doctum is the separate generated code-reference surface for `src/`; producer configuration is owned in-repo via `doctum.php`, while checked-in generated output is not part of the current slice.

## Operational visibility

Operational endpoints are also published in the OpenAPI/Nelmio surface so the documented contract stays aligned with runtime security expectations for `/status`, `/metrics`, and `/payment/dlq*`.
