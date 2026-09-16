= Paying Wave 49 RC-3 Milestone Registry Integration

This wave updates composer script hygiene so the RC-3 milestone marker is covered by the registry.

== Added to hygiene expectations

* `report:rc3-milestone`

== Scope

* Update `PayingComposerScriptHygieneReport`.
* Keep RC-3 milestone separate from runtime proof execution.
* Preserve UTF-8/BOM safety check in the hygiene report.

== Non-scope

* No production code change.
* No test execution.
* No PHPUnit configuration change.
