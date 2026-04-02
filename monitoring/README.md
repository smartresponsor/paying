# Payment Platform Monitoring

## Metrics Endpoint

Expose metrics:

```
GET /metrics/payment
```

Prometheus scrape config:

```
scrape_configs:
  - job_name: 'payment'
    static_configs:
      - targets: ['app:8000']
    metrics_path: /metrics/payment
```

---

## Key Metrics

### Core
- `payment_success_total`
- `payment_failure_total`
- `payment_duration_ms_avg`

### Retry
- `payment_retry_attempts_total`
- `payment_retry_exhausted_total`

### Provider
- `payment_provider_success_total`
- `payment_provider_failure_total`
- `payment_provider_duration_ms_avg`

---

## SLO Recommendations

| Metric | Target |
|------|-------|
| Success rate | > 98% |
| P95 latency | < 1000ms |
| Retry exhausted | 0 |

---

## Alerting

Import rules from:

```
monitoring/prometheus/payment-alerts.yml
```

---

## Grafana

Import dashboard:

```
monitoring/grafana/payment-overview.json
```

---

## Operational Playbook

### Failure spike
- Check provider dashboards
- Verify circuit breaker state
- Inspect retry metrics

### High latency
- Check provider response time
- Verify network / upstream

### Retry exhaustion
- Inspect DLQ
- Reconcile payments manually

---

## Maturity Level

This setup provides:

- Observability
- Alerting
- SLA tracking

System is now **production + SRE ready**.
