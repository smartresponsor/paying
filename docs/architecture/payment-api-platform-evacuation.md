# Payment API Platform evacuation

## Current state

The current slice still contains an `src/Api/*` tail based on API Platform resource metadata and processors. The runtime
dependency is not part of the canonical Payment application and the HTTP surface already exists as Symfony-owned
controllers under `src/Controller/*`.

## Decision

Payment should not depend on API Platform as its application skeleton. The target state is:

- Symfony controllers own the HTTP surface.
- Nelmio ApiDocBundle owns OpenAPI generation.
- Request/response DTOs remain possible, but they are controller-owned DTOs, not API Platform resources.

## Controlled phase-out steps

1. Stop registering API Platform processors as application services.
2. Keep the `src/Api/*` code parked as a legacy tail until replacement endpoints and DTO contracts are complete.
3. Introduce Nelmio documentation endpoints and route coverage for the Symfony-owned surface.
4. Replace `src/Api/Resource/*` with controller request/response DTOs under an App-owned namespace.
5. Delete the legacy API Platform tail after controller coverage and tests are in place.

## Exit criteria

- No runtime dependency on API Platform.
- No service registration for API Platform processors.
- OpenAPI available under `/api/docs` and `/api/docs.json`.
- Payment create/refund documentation generated from Symfony-owned endpoints and DTOs.
