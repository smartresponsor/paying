# LIMITS

## Functional limits
- Provider orchestration is currently implemented only for `internal` and `stripe` via `ProviderRouter`.
- PayPal support is currently webhook ingest + gateway-specific internals, not full start/finalize provider routing.
- Adyen is verifier/mapper-level support in generic webhook path; no full provider-router implementation.

## Operational limits
- Unknown provider webhook validation depends on `PAYMENT_WEBHOOK_ALLOW_UNKNOWN`; enabling it lowers security strictness.
- Local/test runtime defaults can rely on SQLite; production user-data path is expected to be PostgreSQL.
- Demo command documents flow steps; it does not execute a full end-to-end transaction by itself.

## Scope limits
- This component is a payment domain boundary, not a full billing/subscription engine.
- Chargebacks/disputes and external accounting sync are outside canonical scope.
