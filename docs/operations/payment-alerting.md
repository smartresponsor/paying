# Payment alerting guide

## Purpose

Define alert thresholds and how to interpret them.

## Checks

### Failed payments

Metric source:

- repository `listIdsByStatuses(['failed'])`

Threshold example:

- warning: > 1
- critical: > 10

### Projection lag

Metric source:

- `ProjectionLagServiceInterface::snapshot()`

Threshold example:

- warning: > 10s
- critical: > 60s

## Command

```bash
php bin/console payment:lifecycle:run --action=alerts \
  --failed-threshold=1 \
  --lag-threshold-ms=60000
```

## Interpretation

- status = ok → system healthy
- status = alert → investigate checks

## Integration

### Cron

```bash
*/2 * * * * php bin/console payment:lifecycle:run --action=alerts || echo "ALERT"
```

### Container health

Use exit code for readiness checks.

## Recommended actions

- high failed payments → run restart-failed and failover;
- high projection lag → investigate read model or queue;
- repeated alerts → disable provider or adjust routing.
