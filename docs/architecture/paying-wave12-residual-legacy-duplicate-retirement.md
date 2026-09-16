# Paying Wave 12 — Residual Legacy Duplicate Retirement

Wave 12 is a cleanup-only continuation of the Paying canonicalization milestone.

It does not introduce a new runtime abstraction and does not rename any additional canonical classes. The wave retires residual legacy duplicate files that still exist beside already-created `Payment*` replacements from Waves 2–10.

## Scope

- Retire tracked residual duplicate PHP files only.
- Keep canonical `Payment*` files in place.
- Add a report-only guard for the residual duplicate list.
- Keep Symfony command names and runtime contracts unchanged.

## Safety model

The PowerShell applicator backs up every retired file under `.paying_patch_backups` and removes a file only when its SHA256 matches the expected content from the working slice used to build the wave. Missing files are skipped because earlier applicators may already have retired them.

## Tracked retirements

Total tracked residual duplicate files: **87**.

The machine-readable list is stored in `docs/architecture/paying-wave12-patch-manifest.json` and mirrored by `tools/inspection/PayingResidualLegacyDuplicateRetirementReport.php`.
