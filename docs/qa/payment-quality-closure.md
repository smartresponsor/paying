# Payment quality closure baseline

## What this closes

This wave closes the repository-owned quality contour that was previously only partially present:

- `friendsofphp/php-cs-fixer` is now declared in `composer.json`
- `phpDocumentor` is now declared in `composer.json`
- `phpdoc.dist.xml` now defines the internal documentation entry point
- `.github/workflows/payment-quality.yaml` now defines a repository-owned CI quality baseline
- Composer scripts now expose a single `qa` entry point and phpDocumentor/OpenAPI document checks

## What this still does not close

This wave does not prove a fully installed runtime by itself. The following still remain outside the scope of this closure step:

- `composer.lock` is still absent from the current slice
- CI execution is defined but not proven from inside this archive
- phpDocumentor output generation is configured but not executed in this repository snapshot

## Owned quality entry points

- `composer qa`
- `composer lint:yaml`
- `composer lint:container`
- `composer stan`
- `composer cs`
- `composer test`
- `composer docs:phpdoc`
- `composer docs:openapi:lint`

## Release-hardening meaning

After this step, the repository has a materially better owned quality contour than before:

- local QA commands are discoverable and explicit
- CI wiring exists inside the repository
- internal documentation generation has a canonical config entry point
- OpenAPI presence is checked through a dedicated composer script

This is a quality/documentation closure step, not a final release proof.
