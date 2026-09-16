= Paying Wave 42 Publish Metadata

This wave closes the remaining Composer strict publish metadata issue.

== Scope

* Add a root `description` field to `composer.json` when missing.
* Preserve existing dependencies, autoload, scripts, license, package name, and package type.
* Normalize `composer.json` as stable JSON and UTF-8 without BOM.

== Non-scope

* No dependency update.
* No lock-file rewrite.
* No production code change.
* No runtime behavior change.
