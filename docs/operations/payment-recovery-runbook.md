# Payment recovery runbook

## Purpose

This runbook describes how to operate the payment lifecycle when failures occur.
It assumes retry, metrics, routing, and failover features are enabled.

## Key states

- started
- finalized
- failed

Failures are expected and handled through retry, restart, and failover flows.

## Step 1 — Inspect metrics

Use the metrics endpoint or logs to determine:

- failure rate per provider;
- retry attempts and exhaustion;
- average latency.

## Step 2 — Restart failed payments

```bash
php bin/console payment:lifecycle:run --action=restart-failed --provider=stripe --limit=50
```

This uses retry and metrics automatically.

## Step 3 — Apply failover if needed

If a provider is degraded:

```bash
php bin/console payment:failover:restart \
  --provider=stripe \
  --fallback-provider=internal \
  --limit=50
```

## Step 4 — Verify alert status

```bash
php bin/console payment:lifecycle:run --action=alerts
```

If exit code is non-zero, investigate thresholds and provider health.

## Step 5 — Inspect routing decision

```bash
php bin/console payment:routing:score --providers=stripe,internal
php bin/console payment:routing:adaptive --providers=stripe,internal
```

Use this to confirm that degraded providers are not selected.

## Expected behavior

- retry should absorb transient failures;
- batch restart should recover most failed payments;
- failover should recover remaining failures;
- routing should avoid degraded providers;
- alerts should reflect only persistent issues.

## Escalation

If failures persist after failover:

- disable the failing provider at configuration level;
- increase thresholds temporarily;
- inspect provider integration or external API.
