# Architecture Overview

## Core Flow

Client (API / UI / CLI)
    ↓
FinalizeController
    ↓
ProviderGuard
    ↓
Provider (Stripe / PayPal / Internal)
    ↓
RetryExecutor (optional)
    ↓
Payment Entity (state machine)
    ↓
Repository (DB)

---

## Key Concepts

### State Machine
All transitions enforced via PaymentStatusTransitionPolicy.

### Idempotency
Terminal states:
- completed
- failed
- canceled
- refunded

Repeated finalize calls → no-op.

### Observability
- Metrics (/metrics/payment)
- Tracing (traceparent)
- Structured logs

### Retry
RetryExecutor handles retries with metrics.

### Testing
- PHPUnit
- Symfony functional
- Panther E2E
- Playwright UI

---

## Principles

- Single execution path
- Explicit errors
- Idempotent flows
- Observable system
