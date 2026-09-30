# Paying Wave 16 — attribute name-form canonicalization

## Scope

Wave 16 canonicalizes the authorization attribute class-form for the Paying component.

## Canonical decision

`App\Paying\Attribute\RequireScope` is retired in favor of:

`App\Paying\Attribute\PaymentRequireScopeAttribute`

This keeps the class type visible by suffix (`Attribute`) and keeps the component prefix visible by prefix (`Payment`).

## Runtime contract

The scope strings are not changed:

- `payment:read`
- `payment:write`
- `payment:admin`

Controller route paths, Symfony route names, provider keys, service IDs, database tables, and public API contracts are not changed by this wave.

## Files updated

- `src/Attribute/PaymentRequireScopeAttribute.php`
- canonical payment controllers using the scope attribute
- `src/Infrastructure/ScopeGuardSubscriber.php`
- `tests/Unit/ScopeGuardSubscriberTest.php`
- `tools/inspection/PayingAttributeNameFormReport.php`
- `composer.json`

## Guard

Run:

```bash
composer report:attribute-name-form
```

The guard fails when legacy `RequireScope` references or class declarations remain in `src/` or `tests/`.