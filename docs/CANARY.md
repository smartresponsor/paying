# Canary Strategy

## Purpose

This document defines how the payment component should use synthetic users, cohort-based validation, and canary traffic before horizontal scaling and broad rollout.

Goals:
- detect regressions before full rollout
- separate signals for new vs existing user behavior
- validate release safety under production-like conditions
- produce high-signal canary data for decision making

---

## 1. Rollout sequence

Recommended order:
1. architecture stabilization
2. synthetic user validation
3. cohort-based canary validation
4. limited real canary traffic
5. broader rollout
6. horizontal scaling after signal quality is proven

Canary is not a replacement for architecture readiness.
Canary is the validation layer between stable architecture and real scale.

---

## 2. Cohorts

### 2.1 New users
Definition:
- no prior payment history in the tested scenario
- cold-path execution
- new order / new payment creation

Primary signals:
- start success rate
- start latency
- first finalize success
- auth / scope correctness
- provider routing correctness

### 2.2 Existing users
Definition:
- existing payment/order context
- repeated or stateful flows
- duplicate/retry/reconcile/refund-sensitive behavior

Primary signals:
- duplicate finalize safety
- replay behavior
- restart/refund consistency
- projection consistency
- state transition correctness

### 2.3 Why both cohorts matter
New users validate cold-path behavior.
Existing users validate stateful correctness and idempotency.
A release that is safe for one cohort may still be unsafe for the other.

---

## 3. Synthetic users

Synthetic users should be introduced before real canary traffic.

### Required properties
- clearly identifiable synthetic IDs
- isolated prefixes for user/order/payment references
- excluded from business analytics where needed
- safe cleanup policy
- traceable in logs, metrics, and traces

### Recommended identity prefixes
- user: `synthetic-user-*`
- order: `synthetic-order-*`
- payment: `synthetic-payment-*`
- cohort labels: `new`, `existing`

### Synthetic scenarios
#### New cohort
- start payment
- successful finalize
- provider error path
- slow provider path

#### Existing cohort
- duplicate finalize
- reconcile after finalize
- refund after completed
- replay after partial failure

---

## 4. Canary traffic model

### Stage 0 — synthetic only
No real user traffic.
Only synthetic cohorts run against the candidate release.

### Stage 1 — limited real canary
- 1% traffic or equivalent small segment
- real traffic plus synthetic monitoring
- canary-only dashboards and alerts

### Stage 2 — widened canary
- 5%
- then 10%
- then larger share if all signals remain healthy

### Stage 3 — general rollout
Full traffic only after canary success criteria are met.

---

## 5. Canary segmentation strategy

Preferred order:
1. synthetic new + existing cohorts
2. real new-user canary
3. real existing-user canary
4. mixed traffic expansion

Reason:
new-user traffic is usually safer to canary first because it has fewer legacy state interactions.
existing-user traffic should follow after confidence is established.

---

## 6. Required telemetry labels

Canary is only useful if its signals are separable.

All key logs/metrics/traces should include where possible:
- release
- deployment version
- cohort
- synthetic vs real
- provider
- operation
- environment
- canary flag

Without these labels, canary data becomes low-signal and unsafe for decisions.

---

## 7. Canary dashboards

Separate dashboards should exist for canary traffic.

### Required views
- canary success rate
- canary failure rate
- canary latency by provider
- canary retry exhausted count
- canary duplicate/idempotency events
- canary state transition failures
- cohort split: new vs existing
- synthetic vs real comparison

---

## 8. Canary alerts

Canary should have stricter alerting than general traffic.

### Recommended alert classes
- canary failure rate increase
- canary latency regression
- canary retry exhausted > 0
- canary provider failure spike
- canary duplicate handling anomaly
- canary state transition violation

---

## 9. Promotion criteria

A canary may be promoted only if:
- success rate stays within threshold
- latency remains within SLO budget
- retry exhausted remains acceptable
- no duplicate side effects are observed
- no state transition anomalies are observed
- provider-specific regressions are absent
- synthetic cohorts continue to pass during rollout

---

## 10. Rollback criteria

Immediate rollback or halt should occur if:
- canary failure rate exceeds threshold
- retry exhausted appears unexpectedly
- provider-specific errors spike materially
- duplicate/refund/finalize anomalies are detected
- synthetic cohort starts failing after rollout starts
- state machine violations appear

Canary rollout must be reversible faster than full-release damage can accumulate.

---

## 11. Relationship to Kubernetes

Kubernetes is not the first step.

Correct order:
- define canary model
- define cohort signals
- define synthetic users
- validate rollout criteria
- then encode this into Kubernetes or rollout tooling

Kubernetes should carry the canary strategy, not invent it.

---

## 12. Minimum implementation plan

### Phase A
- add Canary.md
- define cohort labels
- define synthetic identities
- define promotion/rollback criteria

### Phase B
- add telemetry labels: release, cohort, canary, synthetic
- build canary dashboard views
- add canary alerts

### Phase C
- implement synthetic new/existing cohort flows
- run synthetic validation in pre-rollout and during canary

### Phase D
- enable small real-user canary
- measure and compare against stable baseline

### Phase E
- only after stable canary, proceed to horizontal scaling work

---

## 13. Current assessment

Already present:
- monitoring foundation
- tracing foundation
- metrics and alerts
- strong finalize behavior
- documentation and deployment model

Partially present:
- testing layers that can back synthetic flows
- release-quality observability
- scale-readiness documentation

Missing:
- explicit canary contract
- cohort labels in telemetry
- synthetic user framework
- canary-specific dashboard/alert partitioning
- rollout promotion gates based on canary data

---

## 14. Final rule

Canary is not just partial traffic.

For a payment system, canary must be:
- cohort-aware
- observable
- reversible
- backed by synthetic validation
- strong enough to decide whether scale is safe
