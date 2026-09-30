# Paying Wave 7 — Business Service Name-Form Canonicalization

Wave 7 canonicalizes the remaining root business service classes that were still using generic or unprefixed names.

## Scope

Renamed root payment service boundaries and their mirrored interfaces to the `Payment*` form:

- dead-letter queue service
- idempotency service and store factory
- projection lag and projection sync services
- reconciliation service
- refund service
- SLA reporter service
- webhook ingest and webhook verifier services

This wave intentionally does not change Symfony console command names, route names, database table names, or provider/gateway class names.

## Guard

`composer report:business-service-name-form` verifies that legacy unprefixed service files are gone, canonical files exist, and no `PaymentPayment*` or `*ServiceService*` drift was introduced.
