# Payment CLI execution smoke

## Purpose

The Payment component owns an operational CLI contour that must remain executable even when HTTP/UI flows evolve.
This smoke layer is intentionally lightweight: it proves command execution contracts, operator output, and command-level ownership without requiring full external infrastructure.

## Covered commands

The current smoke coverage now includes:

- `payment:projection:sync`
- `payment:projection:rebuild`
- `payment:outbox:run`
- `payment:reconcile:run`
- `payment:dlq:replay`
- `payment:idem:purge`
- `payment:sla:report`

## What is proved

### Registration proof

`tests/Payment/Functional/Cli/PaymentCommandRegistrationTest.php` proves that the owned Payment commands are registered in the Symfony console application.

### Execution proof

`tests/Payment/Functional/Cli/PaymentCommandExecutionSmokeTest.php` covers command execution for projection, outbox, and reconciliation flows.

`tests/Payment/Functional/Cli/PaymentOperationalCommandExecutionSmokeTest.php` covers command execution for:

- DLQ replay
- idempotency purge
- SLA report generation

These tests prove:

- the command can execute successfully;
- the command delegates to its owned seam or service;
- the command prints operator-facing output in the expected shape.

## Not yet covered

The following command still benefits from dedicated execution smoke or deeper behavioral proof:

- `payment:gate:slo`

It is registered and owned, but its failure/success threshold behavior should eventually be covered by a dedicated CLI test.

## Ownership note

The CLI contour is App-owned and is separate from:

- the HTTP/OpenAPI contour;
- the Twig console smoke surface;
- the removed legacy `src/Api/*` API Platform tail.
