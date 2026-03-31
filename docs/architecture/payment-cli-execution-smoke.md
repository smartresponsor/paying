# Payment CLI execution smoke

## Purpose

The Payment component owns an operational CLI contour that must remain executable even when HTTP/UI flows evolve.
This smoke layer is intentionally lightweight: it proves command execution contracts, operator output, and command-level
ownership without requiring full external infrastructure.

## Covered commands

The current smoke coverage now includes:

- `payment:projection:sync`
- `payment:projection:rebuild`
- `payment:outbox:run`
- `payment:reconcile:run`
- `payment:dlq:replay`
- `payment:idem:purge`
- `payment:lifecycle:run`
- `payment:sla:report`

## What is proved

### Registration proof

`tests/Functional/Cli/PaymentCommandRegistrationTest.php` proves that the owned Payment commands are registered in the
Symfony console application.

### Execution proof

`tests/Functional/Cli/PaymentCommandExecutionSmokeTest.php` covers command execution for projection, outbox, and
reconciliation flows.

`tests/Functional/Cli/PaymentOperationalCommandExecutionSmokeTest.php` covers command execution for:

- DLQ replay
- idempotency purge
- SLA report generation

`tests/Functional/Cli/PaymentLifecycleCommandExecutionSmokeTest.php` covers command execution for:

- business create flow
- business start flow
- business finalize flow
- business refund flow

These tests prove:

- the command can execute successfully;
- the command delegates to its owned seam or service;
- the command prints operator-facing output in the expected shape.

## Ownership note

The CLI contour is App-owned and is separate from:

- the HTTP/OpenAPI contour;
- the Twig console smoke surface;
- the removed legacy `src/Api/*` API Platform tail.
