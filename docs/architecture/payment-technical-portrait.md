# Payment technical portrait

## Current state

The component is now structurally canonical enough to be treated as a single Symfony-oriented
application under `App\Paying\ -> src/`. The repository already contains live contours for:

- payment lifecycle (`Payment`, `PaymentStatus`, start/finalize/refund flows)
- webhook intake and normalization
- outbox / retry / DLQ / replay handling
- projection and reconciliation services
- migrations for user-data and infrastructure-data storage
- early test coverage across unit, functional, and e2e seams

The component is no longer in a demolition/salvage phase. It has moved into a **technical hardening and proof phase**.

## Responsibility portrait

This repository is not a pure CRUD payment store and not a provider SDK wrapper. Its factual responsibility is closer
to:

**payment execution and integration orchestration for Smartresponsor**

The component currently acts as a coordinator for:

- starting and finalizing payments
- receiving and validating provider webhooks
- normalizing inbound provider payloads into app-owned contracts
- persisting operational logs and outbox messages
- dispatching asynchronous payment events
- reconciling payment state and synchronizing read-models
- exposing operational endpoints and commands around payment flow durability

## What is already alive

### Lifecycle contour

The following seams show an already recognisable payment process:

- `src/Entity/Payment.php`
- payment lifecycle state/value handling in the current `src/Service` + entity contour
- `src/Controller/PaymentStartController.php`
- `src/Controller/PaymentFinalizeController.php`
- `src/Service/PaymentRefundService.php`
- `src/Service/PaymentService.php`
- `src/Service/PaymentApiStartHandler.php`

### Webhook contour

The webhook pipeline is already explicit and app-owned:

- dedicated Stripe/PayPal webhook controllers
- generic payment webhook controller
- signature validators
- payload normalizers
- webhook log entity
- outbox enqueue path
- consumer-side reconciliation

### Operational durability contour

The repository also contains explicit durability seams:

- `payment_outbox_message`
- DLQ handling and replay
- outbox worker retry logic
- projection sync / rebuild
- reconciliation services

## Technical risks that remain

### 1. Installed-runtime proof remains stronger risk than package ownership

The current slice already declares the main package contour in `composer.json` and `composer.lock`.

The higher-value remaining risk is not missing package declarations, but the absence of attached proof that the locked
graph was actually installed and executed end-to-end for the current slice.

### 2. Test contour is present, but installed execution proof is still incomplete

The current slice contains a canonical `phpunit.xml.dist`, owned Composer test scripts, and explicit bootstrap helpers.

The remaining gap is attached proof from an installed runtime rather than missing test ownership.

### 3. Quality gates exist as owned repository contour, but not yet as attached installed proof

The repository contains:

- `.php-cs-fixer.dist.php`
- `phpstan.neon`
- `rector.php`
- `.yamllint.yml`
- smoke scripts under `tools/smoke`

However, the main remaining gap is executed and attached proof from that owned matrix, not file ownership.

### 4. Vertical proof is incomplete

There are already tests and working seams, but the strongest remaining closure item is executed installed-runtime proof
that ties those seams together under the locked dependency graph.

## Immediate conclusion

The next phase should not be more archive cleanup. It should be:

1. installed-runtime proof
2. fixtures and operational UI proof
3. vertical business-flow proof
4. documentation consolidation around executed proof artifacts

## Current runtime note

The removed `src/Api/*` API Platform tail is no longer part of the current slice. The active HTTP contour is now
controller-owned and Symfony-oriented.

## Current operational proof level

CLI execution smoke now covers projection, outbox, reconciliation, DLQ replay, idempotency purge, and SLA reporting.
This means the owned operational contour is no longer proved only by registration; it now has command-level execution
proof across the main operator surfaces.
