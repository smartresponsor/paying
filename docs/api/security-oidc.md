# Security — OIDC scopes

- RequireScope attribute enforces OAuth scopes on routes.
- Env:
  - OIDC_ISSUER, OIDC_AUDIENCE — expected issuer/audience.
  - OIDC_JWKS_URL — JWKS endpoint (used by PaymentOidcJwksCache).
  - OIDC_DISABLED=1 — bypass checks (dev only).
- Add header: `Authorization: Bearer <JWT>`
- Scopes may be in `scope` (space-separated) or `scp` (array).

- Runtime wiring uses explicit Symfony DI arguments for verifier/cache configuration.
