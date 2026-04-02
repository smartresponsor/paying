# Resilience Model

## Purpose

This document defines resilience rules for the payment component.

Goals:
- survive upstream instability
- avoid retry storms
- make failures observable
- keep payment state correct under degraded conditions

---

## Core resilience mechanisms

Current resilience stack includes:
- retry logic
- circuit breaker
- rate limiting
- metrics
- alerting
- tracing

Resilience is correctness plus operational containment.

---

## Failure domains

Primary failure domains:
- provider API failure
- provider latency spike
- database connectivity failure
- queue / async transport failure
- auth / token verification failure
- partial internal runtime failure

---

## Retry policy

Retry is allowed only for retryable failures.

### Rules
- retries must be bounded
- retries must be observable
- retries must not replace idempotency
- retries must use backoff
- exhausted retries must emit alertable signal

### Current direction
- RetryExecutor is present
- retry metrics are present

### Required next hardening
- per-operation retry matrix
- retryable vs non-retryable exception classes
- retry budget by operation

---

## Timeout policy

Timeouts must exist for every provider operation.

### Required
- start timeout
- finalize timeout
- refund timeout
- reconcile timeout

### Rules
- timeout must be smaller than user-visible SLA budget
- timeout must bound retry amplification
- timeout must feed circuit breaker and metrics

### Current state
- partial / underdefined

---

## Circuit breaker policy

Circuit breaker protects provider-facing operations.

### Rules
- repeated upstream failure opens breaker
- open breaker short-circuits traffic
- success closes or heals breaker gradually
- breaker state must be observable

### Risks if weak
- provider outage cascades into app-wide instability
- retry storms amplify external outage

---

## Duplicate and replay resilience

Idempotency is part of resilience.

Required protections:
- duplicate request safety
- duplicate webhook safety
- replay safety after partial failure
- terminal-state no-op behavior

---

## Async resilience

For outbox / projection / async processing:
- failed items must not disappear silently
- retries must be bounded
- DLQ or equivalent terminal failure path must exist
- lag must be measurable

---

## Backpressure and overload

System should not fail by unlimited acceptance.

Recommended protections:
- rate limiting
- queue depth monitoring
- retry budget
- selective load shedding for noncritical paths

Current state:
- rate limiting present
- explicit backpressure/load shedding partial or missing

---

## Observability requirements

Every resilience decision should be visible.

### Logs
- operation
- provider
- error class
- retry attempt
- breaker state
- traceId

### Metrics
- retry_attempts_total
- retry_exhausted_total
- provider_failure_total
- provider_duration_ms_avg
- payment_failure_total

### Alerts
- retry exhausted
- provider failure spike
- provider latency spike
- failure rate spike

---

## Operational rules

### Must be implemented
- explicit failure handling
- no silent catch-and-drop
- no unbounded retry
- no retry without idempotency
- no hidden terminal failure

### Must be documented
- retry matrix
- timeout matrix
- breaker thresholds
- DLQ policy
- manual recovery path

---

## Current state assessment

Implemented:
- retry executor
- provider guard with breaker
- metrics and alerts
- tracing
- improved explicit failure handling

Partial:
- timeout contracts
- backpressure
- load shedding
- worker/DLQ governance

Missing:
- formal per-operation resilience matrix
- explicit bulkhead model
- synthetic resilience drills

---

## Pre-scale checklist

- timeout policy defined
- retry matrix approved
- breaker thresholds approved
- async failure path documented
- duplicate/replay scenarios tested
- provider outage scenario tested
- rollback and manual recovery documented

---

## Final rule

Resilience is not only staying up.

A payment system is resilient only if it stays correct under failure.
