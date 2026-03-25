# Payment dependency audit

// Marketing America Corp. Oleksandr Tishchenko

## Purpose

This document records the factual dependency mismatch of the current `Paying-20-Payment` slice and defines the target dependency graph for the next hardening phase.

## Declared runtime packages today

From `composer.json`:

- PHP 8.4
- Symfony runtime / framework / console / http-foundation / routing / uid / workflow / dependency-injection / config / yaml / rate-limiter
- Doctrine ORM / DoctrineBundle / MigrationsBundle

## Declared dev packages today

From `composer.json`:

- PHPUnit
- Rector
- PHPStan

## Observed package usage not fully represented in composer.json

### Messenger

Observed imports:

- `Symfony\Component\Messenger\Attribute\AsMessageHandler`
- `Symfony\Component\Messenger\MessageBusInterface`
- `Symfony\Component\Messenger\Transport\TransportInterface`

Observed in:

- `src/Message/Handler/Payment/*`
- `src/Api/Processor/*`
- `src/Service/Payment/Outbox/PaymentOutboxProcessor.php`
- `tests/E2E/PaymentWebhookToOrderFlowTest.php`

**Target action:** add `symfony/messenger`.

### Validator

Observed imports:

- `Symfony\Component\Validator\Constraints as Assert`

Observed in:

- `src/Api/Dto/PaymentCreateInput.php`
- `src/Api/Dto/PaymentRefundInput.php`

**Target action:** add `symfony/validator`.

### Serializer

Observed imports:

- `Symfony\Component\Serializer\Annotation\Groups`

Observed in:

- `src/Api/Resource/PaymentResource.php`
- `src/Api/Resource/RefundResource.php`
- `src/Api/Resource/PaymentCreateRequest.php`
- `src/Api/Resource/PaymentRefundRequest.php`

**Target action:** if resource DTO serialization remains in use after API Platform evacuation, add `symfony/serializer`.

### Framework test stack

Observed imports:

- `Symfony\Bundle\FrameworkBundle\Test\WebTestCase`

Observed in:

- `tests/Functional/Api/PaymentCreateEndpointTest.php`
- `tests/E2E/Kernel/StripeWebhookKernelFlowTest.php`

**Target action:** add the missing framework testing stack as needed, commonly `symfony/browser-kit` and `symfony/css-selector` for functional tests.

### Monolog / PSR logging runtime

Observed service wiring:

- `payment_audit.logger` uses `@monolog.logger`

Observed imports:

- `Psr\Log\LoggerInterface`

**Target action:** add `psr/log` explicitly if not already pulled transitively, and add `symfony/monolog-bundle` if Monolog service wiring remains the canonical logger source.

### API Platform transitional usage

Observed imports:

- `ApiPlatform\Metadata\ApiResource`
- `ApiPlatform\Metadata\Post`
- `ApiPlatform\State\ProcessorInterface`

Observed in:

- `src/Api/Resource/*`
- `src/Api/Processor/*`

**Target action:** do **not** reinforce this dependency. Evacuate API Platform code instead and replace it with explicit Symfony controllers plus Nelmio documentation.

### Nelmio / OpenAPI documentation contour

Documentation requirement exists, but bundle/runtime support is not yet visible in the current slice.

**Target action:** add `nelmio/api-doc-bundle` and wire a canonical documentation endpoint.

### Fixtures

Fixtures are required by the next phase, but no fixture bundle/tooling is yet explicit in the current dependency graph.

**Target action:** add a fixture strategy, typically `doctrine/doctrine-fixtures-bundle`.

## Target dependency matrix for the next phase

### Runtime candidates

- `symfony/messenger`
- `symfony/validator`
- `symfony/serializer`
- `symfony/twig-bundle`
- `symfony/form`
- `symfony/security-bundle`
- `symfony/monolog-bundle`
- `nelmio/api-doc-bundle`
- `psr/log`

### Dev candidates

- `symfony/browser-kit`
- `symfony/css-selector`
- `doctrine/doctrine-fixtures-bundle`
- `phpdocumentor/phpdocumentor`

## Immediate rule

The next wave should first audit each observed import and decide one of two outcomes:

1. **keep and declare the package**, or
2. **evacuate the code path and remove the import**.

No silent dependency drift should remain.
