#!/usr/bin/env bash
BASE=http://localhost:8000
curl -s -X POST "$BASE/payment/start" -H 'Content-Type: application/json' -H 'Idempotency-Key: e2e-1' -d '{"amount":"1.23","currency":"USD","provider":"internal"}'
