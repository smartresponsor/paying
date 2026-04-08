# Runtime baseline

## Current baseline

- PHP 8.4 is the canonical runtime target.
- Symfony 8 is the canonical manifest target.
- `composer.json` is aligned to Symfony 8-only core constraints.
- `composer.lock` must be refreshed under Symfony 8 to prove installed-runtime parity.

## Documentation split

- Antora producer pages: owner/manual/governance/operations narrative entry layer.
- Nelmio/OpenAPI: canonical HTTP contract generation.
- Swagger UI: runtime viewer for the generated OpenAPI contract.
- Doctum: generated code-reference surface.

## Release posture

A release candidate should only be tagged after:

1. `composer.lock` is regenerated against Symfony 8.
2. local QA lanes pass against the refreshed graph.
3. GitHub Actions quality/security/pages/release lanes are green.
