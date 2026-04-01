# Payment — RC bundle (E1–E17)

- Canon layers & mirrors enforced; strict_types; namespaces normalized.
- Entity (ULID), Repository, Projection, CLI.
- Provider router & adapters (internal/stripe/adyen), webhook verifier + mappers.
- Idempotency (Data), Outbox + DLQ + worker + UI.
- Workflow (state machine), Reconcile/Refund.
- Observability: /metrics + dashboard/alerts; SLO gate command.
- Security: CSP headers, Audit log, OIDC JWKS cache, Rate limiter.
- Symfony 7/8 ready; PHP platform 8.5.
