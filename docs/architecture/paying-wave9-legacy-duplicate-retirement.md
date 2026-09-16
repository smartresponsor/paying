# Paying Wave 9 — Legacy Duplicate Retirement

Wave 9 closes the residual duplicate-file surface left by the earlier name-form waves.

The earlier waves introduced Payment-prefixed canonical classes and interfaces. Because touched-file zip overlays cannot remove files safely by themselves, the old unprefixed files can remain in a local checkout even after the canonical replacements are present. That creates an ambiguous autoload surface, makes search results noisy, and can hide future accidental references to retired class forms.

## Scope

This wave retires only legacy files that have a direct Payment-prefixed canonical replacement from Waves 2-8.

It does not rename command names, routes, table names, entity names, DTOs, form types, or provider codes. It does not restructure Docker, deployment, migrations, or Doctrine mapping.

## Safety model

The PowerShell delivery script:

1. expands the touched-files payload into a temporary directory;
2. overlays only the files contained in that payload;
3. creates a timestamped backup folder under `.paying_patch_backups`;
4. retires legacy duplicate files only when the current file SHA-256 matches the expected file hash;
5. skips already-absent legacy paths;
6. aborts if any retirement candidate has unexpected content.

## Verification

Run:

```bash
composer report:legacy-duplicate-retirement
composer report:service-adapter-name-form
composer report:business-service-name-form
composer report:infrastructure-name-form
composer report:console-command-name-form
composer report:api-boundary-name-form
composer report:service-core-name-form
composer report:controller-name-form
composer report:canon-structure
```

The new report is intentionally strict: residual retired files fail the report even if the canonical replacement exists.
