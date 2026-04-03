# Telemetry Labels

## Purpose

This document defines the telemetry enrichment required before synthetic cohorts, canary validation, and horizontal scaling.

Goals:
- make canary traffic separable from stable traffic
- distinguish new vs existing user cohorts
- distinguish synthetic vs real traffic
- preserve provider/operation visibility across logs, metrics, and traces

---

## Required labels

All key telemetry should include where technically possible:

- `release`
- `deployment_version`
- `environment`
- `cohort`
- `synthetic`
- `canary`
- `provider`
- `operation`
- `trace_id`

### Label semantics

#### release
Human-readable release identifier.
Example:
- `rc-1`
- `rc-1-canary`

#### deployment_version
Immutable build or artifact version.
Example:
- git SHA
- image tag

#### environment
Example:
- `test`
- `staging`
- `prod`

#### cohort
Allowed values:
- `new`
- `existing`
- `unknown`

#### synthetic
Allowed values:
- `true`
- `false`

#### canary
Allowed values:
- `true`
- `false`

#### provider
Example:
- `internal`
- `stripe`
- `paypal`

#### operation
Example:
- `start`
- `finalize`
- `refund`
- `reconcile`
- `restart`
- `webhook`

#### trace_id
Correlation identifier shared across logs/traces and ideally attached to metrics context when possible.

---

## Where labels must appear

### Logs
Required fields:
- release
- cohort
- synthetic
- canary
- provider
- operation
- traceId

### Metrics
At minimum, enrich provider/operation-sensitive metrics with:
- release
- cohort
- synthetic
- canary
- provider
- operation

### Traces
Required attributes per request/span:
- release
- cohort
- synthetic
- canary
- provider
- operation
- environment

---

## Minimum implementation order

### Step 1
Define a request-scoped telemetry context object or equivalent runtime source.

### Step 2
Populate context from:
- release metadata
- request headers/flags
- synthetic identity detection
- cohort resolution
- provider/operation resolution

### Step 3
Inject context into:
- structured logger
- metrics service
- tracer/span attributes

### Step 4
Use labels in:
- canary dashboards
- canary alerts
- synthetic user reports

---

## Cohort resolution rules

### new
Use for traffic where the tested scenario starts without prior payment history.

### existing
Use for traffic where the tested scenario operates on an existing payment/order context.

### unknown
Fallback only when no safe classification exists.

Unknown should be minimized because it weakens canary signal quality.

---

## Synthetic traffic rules

Synthetic traffic must be identifiable by deterministic patterns such as:
- synthetic user ID prefix
- synthetic order ID prefix
- synthetic payment prefix
- explicit synthetic header or flag

Synthetic traffic must not rely only on probabilistic detection.

---

## Canary traffic rules

Canary should be identifiable independently from synthetic.

Examples:
- canary header
- canary routing flag
- canary deployment marker

A request may be:
- synthetic + canary
- real + canary
- real + stable

These cases must remain distinguishable.

---

## Suggested metrics views

Build dashboards that can filter by:
- release
- canary
- cohort
- synthetic
- provider
- operation

Minimum comparisons:
- canary vs stable success rate
- canary vs stable latency
- new vs existing cohorts
- synthetic vs real traffic behavior
- provider-specific degradation inside canary

---

## Risks if labels are missing

- canary data mixes with stable baseline
- synthetic traffic pollutes product interpretation
- new/existing cohort regressions stay hidden
- rollback decisions become subjective
- provider regressions are harder to localize

---

## Current state assessment

Already present:
- tracing foundation
- metrics foundation
- structured observability direction

Partially present:
- provider/operation telemetry
- request correlation

Missing:
- release label contract
- cohort label contract
- synthetic label contract
- canary label contract
- standardized propagation across logs/metrics/traces

---

## Final rule

Before synthetic cohorts and canary traffic are trusted, telemetry labels must be treated as part of the runtime contract, not as optional metadata.
