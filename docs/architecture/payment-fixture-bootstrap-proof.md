# Payment fixture/bootstrap proof

// Marketing America Corp. Oleksandr Tishchenko

## Scope of this proof

This proof closes the repository-owned part of the fixture/bootstrap track for the current Payment slice.

It does **not** claim that a fully installed runtime already proves the complete path
`doctrine:migrations:migrate -> doctrine:fixtures:load -> operate on loaded state`.

What this proof does establish:

- Payment fixtures are explicitly grouped under the `payment` fixture group.
- The repository owns dedicated Composer entry points for loading Payment fixtures.
- The fixture dataset shape is stable and smoke-tested.
- The PHPUnit bootstrap and README now expose the fixture/bootstrap contour explicitly.

## Owned fixture group

The following fixture classes belong to the `payment` group:

- `App\Infrastructure\Fixture\PaymentFixture`
- `App\Infrastructure\Fixture\PaymentGatewayFixture`
- `App\Infrastructure\Fixture\PaymentMethodFixture`
- `App\Infrastructure\Fixture\PaymentWebhookLogFixture`

## Owned Composer entry points

- `composer fixtures:payment:load`
- `composer fixtures:payment:append`

## Dataset expectations

The smoke dataset shape for the current slice is:

- payments: 5
- gateways: 3
- methods: 3
- webhook logs: 2

## Remaining gap

The highest-value next step after this proof remains the integrated vertical:

- `webhook -> outbox -> consumer`

The installed-runtime fixture execution proof is still a follow-up item because the current slice still lacks lock/CI
closure.
