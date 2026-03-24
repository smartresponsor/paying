# Payment CLI ownership

## Owned operational commands

The Payment component owns the following operational CLI surfaces:

- `payment:dlq:replay`
- `payment:gate:slo`
- `payment:idem:purge`
- `payment:outbox:run`
- `payment:projection:rebuild`
- `payment:projection:sync`
- `payment:reconcile:run`
- `payment:sla:report`

## Ownership rule

These commands belong to the Payment application layer and are part of the supported operational contour.
They are expected to remain wired through Symfony console registration and should be covered by smoke-level registration tests.

## Non-owned contour

Legacy `src/Api/*` classes are no longer part of the active runtime/service graph and are not part of the owned CLI/API contour.


## Execution smoke

See `docs/architecture/payment-cli-execution-smoke.md` for the current command execution proof contour.
