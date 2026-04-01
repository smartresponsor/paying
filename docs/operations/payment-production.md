# Production deployment guide

## Overview

This document explains how to run the payment system in production using Docker, scheduler loops, and monitoring.

## Services

- app: Symfony application exposing HTTP endpoints and metrics
- scheduler: background worker executing recovery and alert checks

## Start

```bash
docker compose up -d --build
```

## Health

The container uses:

```bash
php bin/console payment:lifecycle:run --action=alerts
```

Exit code drives health state.

## Scheduler loop

Runs every minute:

- restart-failed
- alerts

Adjust interval via sleep duration.

## Metrics

Expose /metrics endpoint and connect to Prometheus.

Use provided alert rules in:

- monitoring/payment-alert-rules.yml

## Scaling

- scale scheduler horizontally for higher throughput;
- ensure idempotency in restart operations;
- use external DB (Postgres) instead of SQLite for production.

## Recommendations

- set DATABASE_URL to Postgres;
- configure circuit breaker storage if needed;
- tune retry limits and alert thresholds.
