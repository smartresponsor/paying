# Payment quality gates

// Marketing America Corp. Oleksandr Tishchenko

## Current observed tools

The current slice already contains:

- `.php-cs-fixer.dist.php`
- `phpstan.neon`
- `rector.php`
- `.yamllint.yml`
- smoke scripts under `tools/smoke`

## Owned operational gate contour

The repository already owns a canonical QA/tooling matrix; the remaining gap is executed proof from an installed runtime.

## Target Composer scripts

- `test`
- `test:unit`
- `test:functional`
- `test:e2e`
- `stan`
- `cs`
- `cs:fix`
- `rector`
- `lint:yaml`
- `lint:container`
- `smoke`

## Minimum green pipeline

A Payment slice should be considered green only when all of the following pass:

1. Composer install on PHP 8.4
2. Container boot / kernel smoke
3. Doctrine migrations smoke
4. PHPUnit suites
5. PHPStan
6. php-cs-fixer dry-run
7. YAML lint
8. Selected operational smoke commands

## Important note

The current slice already owns the files and workflows for a proof-oriented gate system. The next engineering phase
should execute that owned matrix from an installed runtime and attach the resulting proof artifacts.

## Closure update

The repository now contains the missing owned pieces that previously kept the quality contour open:

- declared `friendsofphp/php-cs-fixer`
- Doctum configuration: `doctum.php`
- Doctum runtime helpers/checks under `tools/runtime/` and `tools/ci/`
- `.github/workflows/payment-quality.yaml`
- composer `qa` and documentation scripts

The remaining gap is no longer repository ownership, but installed-runtime execution proof.
