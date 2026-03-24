# Payment access matrix

## Roles and scopes

The current Payment component uses scope-based bearer verification via `RequireScope` and `ScopeGuardSubscriber`.
The intended minimum matrix is:

| Surface | Route / action | Scope |
|---|---|---|
| API | `POST /api/payments` | `payment:write` |
| API | `GET /api/payments/{id}` | `payment:read` |
| API | `POST /api/payments/{id}/refund` | `payment:write` |
| API | `POST /payment/start` | `payment:write` |
| API | `POST /payment/finalize/{id}` | `payment:write` |
| UI | `GET /payment/console` | `payment:read` |
| UI | `POST /payment/console/create` | `payment:write` |
| UI | `POST /payment/console/start` | `payment:write` |
| UI | `POST /payment/console/finalize` | `payment:write` |
| UI | `POST /payment/console/refund` | `payment:write` |
| Ops | `GET /payment/dlq` | `payment:read` |
| Ops | `POST /payment/dlq/replay/{id}` | `payment:write` |
| Ops | `GET /status` | `payment:read` |
| Ops | `GET /metrics` | `payment:read` |
| Docs | `/api/docs`, `/api/docs.json` | public |

## CLI operational ownership

| CLI command | Intended operator level |
|---|---|
| `payment:projection:sync` | payment:write / internal operator |
| `payment:projection:rebuild` | payment:write / internal operator |
| `payment:outbox:run` | payment:write / internal operator |
| `payment:dlq:replay` | payment:write / internal operator |
| `payment:reconcile:run` | payment:write / internal operator |
| `payment:sla:report` | payment:read / internal observer |
| `payment:gate:slo` | payment:read / CI gate |
| `payment:idem:purge` | payment:write / internal operator |

## Notes

- `payment:read` is for operational visibility and internal read-only navigation.
- `payment:write` is required for lifecycle-changing flows.
- Dedicated provider webhooks are validated through provider signatures rather than user scopes.
- The Twig console is intended as a fixture-backed smoke surface, not as a production customer UI.
- Legacy `src/Api/*` classes are now passive/deprecated tails and should not participate in the active service graph.


## CLI ownership

See `docs/architecture/payment-cli-ownership.md` for the owned operational command contour.


## CLI smoke status

Registration is covered for the full owned command set. Execution smoke currently covers projection, outbox, reconciliation, DLQ replay, idempotency purge, and SLA reporting. `payment:gate:slo` remains registration-covered and should receive dedicated execution proof in a later wave.
