# Paying Wave 24 — Test Legacy Duplicate Retirement

Status: prepared as touched-files patch.

Wave 24 retires the mapped legacy unit-test duplicates that were identified by Wave 23 after their canonical `Payment*Test` replacements already existed.

## Scope

Retired by the apply script only after backup and SHA-256 verification:

- `tests/Unit/ApiErrorResponseFactoryTest.php`
- `tests/Unit/ApiJsonBodyDecoderTest.php`
- `tests/Unit/ApiRequestValidatorTest.php`
- `tests/Unit/FinalizeControllerTest.php`
- `tests/Unit/ProjectionLagServiceTest.php`
- `tests/Unit/ProviderGuardTest.php`
- `tests/Unit/RefundServiceTest.php`
- `tests/Unit/ResponseHeaderSubscriberTest.php`
- `tests/Unit/RetryExecutorTest.php`
- `tests/Unit/ScopeGuardSubscriberTest.php`
- `tests/Unit/TokenVerifierTest.php`
- `tests/Unit/ValidationErrorMapperTest.php`
- `tests/Unit/ValueObject/MoneyTest.php`

## Non-scope

The wave does not rename runtime classes, does not change routes, provider keys, scope strings, Doctrine tables, or public command names.

Unmapped legacy tests such as outbox worker/publisher and provider event normalizer tests are intentionally left for a later mapping wave.

## Verification

Run:

```bash
composer report:test-legacy-duplicate-retirement
composer report:test-residual-name-form
composer report:canonical-structure-closure
```
