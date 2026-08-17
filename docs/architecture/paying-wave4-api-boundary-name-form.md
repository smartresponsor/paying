# Paying Wave 4 — API Boundary Name-Form Canonicalization

## Scope

Wave 4 canonicalizes the shared API and security helper services that are injected into HTTP controllers and request subscribers.

The renamed boundaries are:

- `ApiErrorResponseFactory` -> `PaymentApiErrorResponseFactory`
- `ApiJsonBodyDecoder` -> `PaymentApiJsonBodyDecoder`
- `ApiRequestValidator` -> `PaymentApiRequestValidator`
- `ValidationErrorMapper` -> `PaymentValidationErrorMapper`
- `OidcJwksCache` -> `PaymentOidcJwksCache`
- `TokenVerifier` -> `PaymentTokenVerifier`

The matching `ServiceInterface` contracts and unit tests use the same `Payment*` class-form.

## Canonical decision

These services are payment-component boundaries, not generic application utilities. They remain in the Symfony-oriented `Service` / `ServiceInterface` layers, but their names now advertise the component prefix and role.

## Non-goals

- No provider/gateway model decision is made here.
- No Entity-first model rewrite is performed here.
- No repository-wide cleanup or destructive overwrite is performed.
- Legacy files are retired by the PowerShell overlay only when their exact SHA256 matches the touched source files from the prior applied state.

## Verification

Run:

```bash
composer dump-autoload
composer report:api-boundary-name-form
composer report:service-core-name-form
composer report:controller-name-form
composer report:canon-structure
composer test:unit
```
