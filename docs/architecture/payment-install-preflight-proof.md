# Payment install preflight proof

This document captures the repository-owned preflight contour for installed-runtime hardening.

## Goal

Provide deterministic pre-install checks before claiming installed-runtime proof for the Payment component.

## Owned preflight entry points

- `composer install:preflight`
- `composer install:proof:checklist`
- `composer runtime:preflight:sh`
- `composer runtime:preflight:ps1`
- `composer docs:phpdoc:check`

## What these checks prove

- Required repository files for install/runtime are present.
- Test/runtime environment files are committed.
- Runtime helper scripts exist for Unix and PowerShell operators.
- Documentation tooling presence can be checked without polluting the application dependency graph.
- The repo explicitly reports whether `composer.lock` is present.

## What these checks do not prove

These checks do **not** prove a completed installed runtime. They do not execute:

- `composer install`
- Doctrine migrations on an installed dependency graph
- fixture load on a fully installed runtime
- HTTP/UI/CLI execution against an actually installed vendor set

## Next closure step

The remaining installed-runtime proof still requires:

1. committed `composer.lock`
2. successful `composer install`
3. migrations on data and infra stores
4. fixture load on installed runtime
5. smoke execution against installed vendor + console runtime
