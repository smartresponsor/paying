# Payment API Cookbook

## Start
Request:
POST /payment/start
Headers: Content-Type: application/json, Idempotency-Key: <key>
Body: {"amount":"12.34","currency":"USD","provider":"internal"}

## Finalize
POST /payment/finalize/{id}?provider=internal

## Webhook
POST /payment/webhook/stripe
Header: Stripe-Signature: t=<ts>,v1=<hmac>
Body: stripe event JSON

## DLQ
GET /payment/dlq
POST /payment/dlq/replay/{id}
