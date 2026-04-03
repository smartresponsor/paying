# LIMITS

## Not yet market-grade

- Provider orchestration is implemented for `internal`, `stripe`, and `paypal` in `ProviderRouter`.
- Adyen support is signature verification / event mapping level only.

## Multi-tenant limitations

- No explicit tenant isolation model in API/UI/runtime flows.
- No tenant-aware routing, quotas, or policy segmentation in the console surface.

## Lifecycle simplifications

- Lifecycle is intentionally reduced to create/start/finalize/refund/read with simplified reconciliation contour.
- Demo command explains flow but does not execute a full autonomous end-to-end transaction.
- Unknown webhook provider acceptance can be toggled by env flag, which is operationally convenient but less strict.

## Provider limitations

- Current provider matrix is asymmetric across surfaces:
  - Router lifecycle: `internal`, `stripe`, `paypal`
  - Dedicated webhook endpoints: `stripe`, `paypal`
  - Generic verifier signatures: `stripe`, `adyen`

## Scope boundaries

- This component is not a full billing/subscription platform.
- Chargeback/dispute workflows and external accounting sync are outside canonical scope.
