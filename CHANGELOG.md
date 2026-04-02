# Changelog

All notable changes to this project will be documented in this file.

## [RC-1] - 2026-04

### Added
- Payment state machine with enforced transitions
- Idempotent finalize flow (API, CLI, handlers)
- Distributed tracing (traceId, spanId, traceparent propagation)
- Prometheus-compatible metrics endpoint (`/metrics/payment`)
- Grafana dashboard and alerting rules
- Retry instrumentation and provider-level metrics

### Changed
- Unified finalize contract:
  - `gatewayTransactionId` → `providerTransactionId`
- Consolidated provider/gateway execution path

### Fixed
- Removed silent failures in infrastructure
- Ensured consistent status transitions via domain policy
- Synchronized UI (Playwright) with backend contract

### Security
- Improved edge validation for request payloads
- Enforced strict input validation for finalize endpoint

---

## Notes
This release candidate focuses on:
- Stability under retries and duplicate events
- Observability and production readiness
