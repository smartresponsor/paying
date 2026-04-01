# Payment technical portrait

## Current state

The `Paying/Payment` component is now structurally canonical enough to be treated as a single Symfony-oriented
application under `App\ -> src/`. The repository already contains live contours for:

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

- `src/Entity/Payment/Payment.php`
- `src/ValueObject/Payment/PaymentStatus.php`
- `src/Controller/Payment/StartController.php`
- `src/Controller/Payment/FinalizeController.php`
- `src/Service/Payment/RefundService.php`
- `src/Message/Handler/Payment/PaymentCreateHandler.php`
- `src/Message/Handler/Payment/PaymentRefundHandler.php`

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

### 1. Dependency graph does not match the code

There is a factual mismatch between what the code imports and what `composer.json` currently requires.

Observed examples from the current slice:

- `ApiPlatform\Metadata\ApiResource` and `ApiPlatform\State\ProcessorInterface` are used under `src/Api/*`
- `Symfony\Component\Messenger\*` is used by message handlers/processors/transport code
- `Symfony\Bundle\FrameworkBundle\Test\WebTestCase` is used by functional/e2e tests
- `Symfony\Component\Validator\Constraints` is used by DTOs
- `Symfony\Component\Serializer\Annotation\Groups` is used by API resource classes
- `Psr\Log\LoggerInterface` is wired from Monolog-style service definitions

But `composer.json` does not currently declare the full matching package contour.

### 2. API Platform transitional tail still exists

The repository still contains API Platform resources and processors under `src/Api/*`. This is transitional and
conflicts with the target direction of explicit Symfony controllers plus Nelmio-based OpenAPI documentation.

### 3. Test contour is present, but not yet fully operationalised

The current slice contains real tests under `tests/*`, but the repository still lacks a canonical `phpunit.xml.dist` and
a fully explicit test bootstrap / suite layout.

### 4. Quality gates exist as files, but not as a complete pipeline

The repository contains:

- `.php-cs-fixer.dist.php`
- `phpstan.neon`
- `rector.php`
- `.yamllint.yml`
- smoke scripts under `tools/smoke`

However, this is not yet a complete operational QA contour because the repository still lacks a strong script matrix and
a documented green pipeline.

### 5. Vertical proof is incomplete

There are already tests and working seams, but the main business verticals are not yet fully proven end-to-end with a
clearly documented matrix.

## Immediate conclusion

The next phase should not be more archive cleanup. It should be:

1. dependency normalization
2. API Platform evacuation
3. OpenAPI + Nelmio endpoint documentation
4. fixtures and security/access contour
5. minimal Twig/Bootstrap operational UI
6. CLI proof
7. vertical business-flow proof
8. documentation refresh

## Current runtime note

The legacy `src/Api/*` tail has already been evacuated from the current slice. The active HTTP contour is now
controller-owned and Symfony-oriented.

## Current operational proof level

CLI execution smoke now covers projection, outbox, reconciliation, DLQ replay, idempotency purge, and SLA reporting.
This means the owned operational contour is no longer proved only by registration; it now has command-level execution
proof across the main operator surfaces.
