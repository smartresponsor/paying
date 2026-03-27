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

- OpenAPI source: `docs/api/openapi.yaml`.
- Nelmio UI and JSON routes above are the runtime contract publication points.
