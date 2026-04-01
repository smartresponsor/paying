# Payment local pipeline reporting

## Composer entry points

- `composer pipeline:local`
- `composer pipeline:local:full`
- `composer pipeline:local:strict`
- `composer report:local:latest`

## QA and smoke/report commands

- `composer lint`
- `composer cs:check`
- `composer stan:runtime-target`
- `composer test`
- `composer smoke:runtime`
- `composer smoke:fixtures`
- `composer smoke:container`
- `composer smoke:doctrine`
- `composer report:route-inventory`
- `composer report:runtime-proof`

## Local report tree

Each pipeline run writes files under:

- `var/report/local/<timestamp>/summary.md`
- `var/report/local/<timestamp>/report.json`
- `var/report/local/<timestamp>/logs/*.log`

The latest run is mirrored to:

- `var/report/local/latest/`

## Exit mode

- `composer pipeline:local` and `composer pipeline:local:full` are non-strict.
  They write report artifacts and return success even when some QA steps fail.
  Those step failures are preserved in `summary.md` and `report.json` as report evidence.
- `composer pipeline:local:strict` is strict.
  It returns a non-zero exit code when any step fails.
