= Paying Wave 39 Runtime Issue Inventory

This wave adds a runtime issue inventory report.

== Goal

Expose the remaining PHPUnit warnings, notices, deprecations, and skipped tests after the unit, functional, and security suites no longer have fatal/error/failure blockers.

== Command

[source,bash]
----
composer report:runtime-issue-inventory
----

== Scope

* Run unit, functional, and security test suites.
* Display warnings, notices, deprecations, skips, and selected issue lines.
* Keep this as an inspection/reporting surface.

== Non-scope

* No production code change.
* No PHPUnit configuration weakening.
* No broad test rewrite.
