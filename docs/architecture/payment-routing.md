# Payment routing architecture

## Purpose

The Payment component now owns an explicit routing layer in addition to lifecycle, retry, failover, alerting, and recovery contours.
This document explains how provider selection works and how the routing services relate to the rest of the Symfony-first tree.

## Routing layers

### Provider selection

`ProviderSelector` chooses the first provider whose circuit breaker is not open.
It is the simplest safe selector and is suitable for deterministic routing decisions when availability is the only concern.

### Provider scoring

`ProviderScorer` ranks providers using three signals:

- provider success rate from exported metrics;
- average provider latency from exported metrics;
- circuit breaker availability.

The current score model is intentionally simple:

- start from success rate;
- subtract a latency penalty;
- subtract an availability penalty when the provider circuit is open.

This gives an operationally useful ranking without introducing heavy policy or external dependencies.

### Adaptive routing

`AdaptiveRouting` converts provider scores into weights.
The highest-ranked provider remains the default chosen provider, while the normalized weights expose a traffic-splitting plan for future weighted execution.

## Related operational flows

Routing works together with the existing resilience flows:

- `ProviderGuard` wraps provider execution with retry, breaker, success/failure accounting, and duration metrics;
- `RetryExecutor` retries transient provider failures;
- `payment:failover:restart` retries failed payments on a fallback provider;
- `payment:lifecycle:run --action=restart-failed` performs batch recovery;
- `payment:lifecycle:run --action=alerts` exposes threshold-based alert checks.

## Console commands

### Choose first healthy provider

```bash
php bin/console payment:routing:choose --providers=stripe,adyen,internal
```

### Score providers

```bash
php bin/console payment:routing:score --providers=stripe,adyen,internal --operation=start
```

### Produce adaptive weighted plan

```bash
php bin/console payment:routing:adaptive --providers=stripe,adyen,internal --operation=start
```

### Fail over failed payments

```bash
php bin/console payment:failover:restart --provider=stripe --fallback-provider=internal --limit=50
```

## Tree ownership

The routing stack follows the canonical Symfony tree used by this repository:

- `src/Service` owns routing, scoring, retry, and guard orchestration;
- `src/Infrastructure/Console` owns operator commands;
- `src/ServiceInterface` owns routing contracts;
- `src/RepositoryInterface` owns persistence seams;
- `src/Entity` owns payment state.

No `domain/application` split is required for this component.
