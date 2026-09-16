= Paying Wave 43 Runtime Proof Core Aggregate

This wave adds a single runtime proof aggregate command.

== Command

[source,bash]
----
composer report:runtime-proof-core
----

== Included jobs

* `composer validate`
* RC-2 canonical readiness report
* unit test suite
* functional test suite
* security test filter
* runtime issue inventory

== Status model

* `FAILED` means at least one strict proof job returned a non-zero exit code.
* `OK_WITH_ISSUES` means strict jobs returned zero but PHPUnit still reported warnings, notices, deprecations, or similar issue markers.
* `OK` means all strict jobs returned zero and no issue markers were detected.

== Non-scope

* No production behavior change.
* No PHPUnit configuration weakening.
* No skipped-test policy change.
