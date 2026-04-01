# Payment dashboard guide

## Dashboard file

Import the Grafana dashboard from:

- `monitoring/grafana/payment-operations-dashboard.json`

## Panels

The dashboard includes:

- failed payments;
- retry exhausted;
- projection lag;
- provider failure rate;
- provider success rate;
- provider latency;
- retry attempt rate.

## Prometheus queries

### Failed payments

`payment_failure_total`

### Retry exhausted

`payment_retry_exhausted_total`

### Projection lag

`payment_projection_lag_ms`

### Provider failure rate

`sum by (provider, operation) (rate(payment_provider_failure_total[5m]))`

### Provider success rate

`sum by (provider, operation) (rate(payment_provider_success_total[5m]))`

### Provider latency

`payment_provider_duration_ms_avg`

### Retry pressure

`rate(payment_retry_attempts_total[5m])`

## Recommended dashboard usage

- top row for fast health view;
- middle row for provider behavior;
- bottom row for retry pressure and latency trends.

## On-call interpretation

- rising retry exhaustion means retry is not enough;
- rising provider failure rate with stable latency usually means provider-side errors;
- rising latency with later failures usually means degradation before outage;
- rising projection lag means read side or async processing is behind.
