# Paying Wave 8 — Service Adapter Name-Form Canonicalization

Wave 8 canonicalizes the remaining service-adapter class names that were still missing the component `Payment` prefix after the controller, core service, API boundary, console, infrastructure, and business-service waves.

## Scope

This wave covers adapter/helper services under:

- `src/Service/Gateway/`
- `src/Service/Mapper/`
- `src/Service/Order/`
- `src/Service/Webhook/`

It also retires residual duplicate root service/interface files that were superseded by the canonical `Payment*` files from earlier waves.

## Canonical rule

Concrete service adapter classes must expose the component prefix in the class/file name unless they are intentionally generic contracts. Examples:

- `PaymentPayPalGateway`
- `PaymentStripeGateway`
- `PaymentAdyenEventMapper`
- `PaymentNullOrderPaymentSync`
- `PaymentPayPalSignatureValidator`

Symfony service IDs and autowiring remain class-based. Existing route and command names are not changed by this wave.

## Safety

The companion PowerShell script applies only files present in the touched archive. Legacy files are removed only when the current file hash matches the expected pre-patch hash; otherwise, the script leaves them in place and prints a warning.
