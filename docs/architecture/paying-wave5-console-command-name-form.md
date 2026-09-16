# Paying Wave 5 — Console Command Name-Form

This wave canonicalizes the infrastructure CLI command surface without changing Symfony command names or runtime behavior.

## Renames

| Legacy class | Canonical class | Symfony command name |
| --- | --- | --- |
| `DlqReplayCommand` | `PaymentDlqReplayCommand` | `payment:dlq:replay` |
| `GateSloCommand` | `PaymentGateSloCommand` | `payment:gate:slo` |
| `IdemPurgeCommand` | `PaymentIdemPurgeCommand` | `payment:idem:purge` |
| `OutboxRunCommand` | `PaymentOutboxRunCommand` | `payment:outbox:run` |
| `ProjectionRebuildCommand` | `PaymentProjectionRebuildCommand` | `payment:projection:rebuild` |
| `ProjectionSyncCommand` | `PaymentProjectionSyncCommand` | `payment:projection:sync` |
| `ReconcileRunCommand` | `PaymentReconcileRunCommand` | `payment:reconcile:run` |
| `SlaReportCommand` | `PaymentSlaReportCommand` | `payment:sla:report` |

## Guard

Run:

```bash
composer report:console-command-name-form
```

The guard is report-only and checks `src/Infrastructure/Console` for non-prefixed command files/classes.
