= Paying Wave 45 Runtime Proof Closure Guard

This wave adds a lightweight runtime proof closure guard.

== Command

[source,bash]
----
composer report:runtime-proof-closure
----

== Purpose

The report checks the runtime proof surface without re-running heavy test suites:

* runtime proof report files exist;
* runtime proof docs exist;
* Composer scripts for canonical readiness, runtime issue inventory, runtime proof core, and test suites exist;
* Composer package description is present;
* `composer.json` is valid UTF-8 without BOM.

== Relationship to runtime proof

Use `report:runtime-proof-core` for execution proof. Use `report:runtime-proof-closure` for static surface closure.

== Non-scope

* No production code change.
* No PHPUnit configuration weakening.
* No test execution.
