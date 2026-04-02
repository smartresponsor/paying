# Idempotency Model

## Purpose

This document defines idempotency rules for the payment component.

Goals:
- prevent duplicate processing
- ensure safe retries and replays
- define clear duplicate handling policies

---

## General principles

All write-like operations MUST be idempotent:
- start
- finalize
- refund
- reconcile
- restart
- webhook ingest

Authoritative source of truth:
- write-side SQL payment state

Terminal states:
- completed
- failed
- canceled
- refunded

Terminal state operations MUST be no-op or rejected.

---

## Operation rules

### Start
- must use idempotency key
- duplicates must return existing payment

### Finalize
- idempotent by payment id
- terminal state → no-op

### Refund
- must prevent double refund
- use refund identity key

### Reconcile
- safe to repeat
- must not create side effects

### Restart
- allowed only from failed
- duplicates → no-op or error

### Webhook
- dedupe by provider event id
- duplicates ignored

---

## Storage model

Idempotency must use shared storage:
- SQL or Redis

Not allowed:
- in-memory only

---

## Duplicate handling

- finalize: no-op
- start: return existing
- refund: return existing or block
- webhook: ignore

---

## Observability

Log:
- operation
- key
- duplicate_detected
- traceId

Metrics:
- idempotency_duplicate_total
- webhook_duplicate_total

---

## Current state

Implemented:
- finalize idempotency
- terminal state logic

Partial:
- retry integration
- tracing

Missing:
- start idempotency
- refund idempotency
- webhook dedupe registry

---

## Pre-scale checklist

- idempotency keys defined
- shared storage confirmed
- duplicate tests exist

---

## Final rule

Idempotency is correctness, not optimization.
