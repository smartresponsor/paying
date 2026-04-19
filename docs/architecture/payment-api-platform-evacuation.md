# Payment API Platform evacuation

## Current state

This note is retained as a historical record of the API Platform evacuation work. The `src/Api/*` tail has already been removed from the current slice, and the active runtime is controller-owned under `src/Controller/*`.

## Decision

Payment should not depend on API Platform as its application skeleton. The target state remains:

- Symfony controllers own the HTTP surface.
- Nelmio ApiDocBundle owns OpenAPI generation.
- Request/response DTOs remain possible, but they are controller-owned DTOs, not API Platform resources.

## Historical phase-out steps

1. Stop registering API Platform processors as application services.
2. Introduce Nelmio documentation endpoints and route coverage for the Symfony-owned surface.
3. Replace `src/Api/Resource/*` with controller request/response DTOs under an App-owned namespace.
4. Delete the legacy API Platform tail after controller coverage and tests are in place.
5. Keep this document as archival evidence, not as an active worklist.

## Exit criteria

- No runtime dependency on API Platform.
- No service registration for API Platform processors.
- OpenAPI available under `/api/docs` and `/api/docs.json`.
- Payment create/refund documentation generated from Symfony-owned endpoints and DTOs.
