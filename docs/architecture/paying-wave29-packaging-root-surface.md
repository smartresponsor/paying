# Paying Wave 29 — Packaging/root surface guard

Wave 29 adds a report-only guard for the repository packaging and root surface.

## Scope

- Verify expected Symfony repository anchors: `config/`, `src/`, `tests/`, `tools/`, `docs/`, `deploy/`, `public/`.
- Verify deployment/Docker material is under `deploy/`, not scattered at root.
- Verify canonical report scripts from the release-candidate and closure waves are still registered.
- Verify historical loose root artifacts retired by earlier waves are not still present.

## Non-scope

- No runtime class rename.
- No route, provider key, command name, scope string, or Doctrine table changes.
- No destructive repository cleanup.
- No cumulative snapshot.

## Validation

Run:

```bash
composer report:packaging-root-surface
composer report:release-candidate-structure
composer report:canonical-structure-closure
```
