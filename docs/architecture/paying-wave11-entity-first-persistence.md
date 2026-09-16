# Paying Wave 11 — Entity-first persistence map and guard

## Scope

Wave 11 does not rename runtime classes. It formalizes the entity-first persistence topology that was missing from the architectural reading order.

## Added

- `docs/architecture/paying-entity-first-map.md`
- `tools/inspection/PayingEntityFirstPersistenceReport.php`
- Composer script `report:entity-first-persistence`
- Patch manifest for this wave

## Canonical rules enforced by the report

- Entity classes must live under `src/Entity` or `src/Infrastructure/Entity`.
- Entity class files must use `Payment*Entity` form.
- Doctrine table names must be explicit.
- Table names must be either `payment` or prefixed with `payment_`.
- Migration table references must follow the same prefix rule.

## Non-scope

- No repository overwrite.
- No entity relocation.
- No migration rewrite.
- No provider/gateway decision.
- No `/src/Domain` or port/adapter conversion.

## Validation

```bash
composer report:entity-first-persistence
composer report:canon-structure
```
