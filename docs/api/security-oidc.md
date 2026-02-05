# Security — OIDC scopes

- RequireScope attribute enforces OAuth scopes on routes.
- Env:
  - OIDC_ISS, OIDC_AUD — expected issuer/audience.
  - OIDC_JWKS_URL — JWKS endpoint (used by OidcJwksCache).
  - OIDC_DISABLED=1 — bypass checks (dev only).
- Add header: `Authorization: Bearer <JWT>`
- Scopes may be in `scope` (space-separated) or `scp` (array).
