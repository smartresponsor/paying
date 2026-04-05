# Changelog

All notable changes to this repository should be documented in this file.

The format follows a Keep a Changelog style adapted to the current Smartresponsor owner workflow.

## [Unreleased]

### Added

- Owner-facing GitHub badge block in `README.md` wired to the canonical repository URLs.
- First-release checklist and release preparation guidance.

## First release checklist

Before cutting the first public or internal tagged release, close the following gates:

- [ ] Commit and validate `composer.lock` from the owned dependency graph.
- [ ] Prove `composer install` on a clean environment.
- [ ] Prove migrations and fixtures in the installed runtime.
- [ ] Prove green local QA baseline (`lint`, CS, PHPStan, PHPUnit, smoke commands).
- [ ] Prove green GitHub Actions baseline for the quality workflow.
- [ ] Review `docs/INSTALL.md`, `docs/OPERATIONS.md`, and `README.md` for final operator accuracy.
- [ ] Regenerate and verify OpenAPI/docs surfaces.
- [ ] Prepare a release section below using the final version number and release date.
- [ ] Push the annotated tag and publish the GitHub Release.
- [ ] Verify GitHub Pages publication and metrics/badges freshness.

## First release template

Use this section as the canonical template for the first tagged release.

## [0.1.0] - YYYY-MM-DD

### Added

- Controller-owned HTTP payment lifecycle for create, read, start, finalize, and refund flows.
- Internal console surface built on Symfony Forms, Twig, and Bootstrap.
- Provider routing for internal, Stripe, and PayPal flows with dedicated webhook endpoints.
- Outbox, DLQ, reconciliation, projection, idempotency, and SLO operational contours.
- Nelmio/OpenAPI documentation entrypoints and repository-owned QA/runtime documentation.

### Changed

- Consolidated the repository to a flat Symfony-oriented structure without `src/Domain` and without legacy `src/*/Payment` tails.
- Synchronized architecture/readiness documents with the current controller-owned runtime contour.

### Notes

- Replace `YYYY-MM-DD` with the actual release date.
- Edit the bullets so they describe the final proven scope of the first tagged release, not the pre-release plan.
- Keep older release sections below newer ones, newest first.
