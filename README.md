# Payment Platform (RC)

Production-ready payment core with:
- state machine
- idempotent flows
- retry logic
- observability

---

## Quick Start

composer install
symfony server:start

Open:
http://localhost:8000/payment/console

---

## API

POST /payment/finalize/{id}

Payload:
{
  "provider": "stripe",
  "providerRef": "abc",
  "providerTransactionId": "txn_123",
  "status": "completed"
}

---

## Metrics

GET /metrics/payment

---

## Monitoring

- Grafana: monitoring/grafana/payment-overview.json
- Alerts: monitoring/prometheus/payment-alerts.yml

---

## Testing

composer test
composer test:e2e
npm run test:playwright

---

## Architecture

See ARCHITECTURE.md

---

## Changelog

See CHANGELOG.md
