= Paying Wave 57 Final Closure Self Verification

This wave strengthens the final RC-3 closure marker so it verifies its own registry integration.

== Updated command

[source,bash]
----
composer report:rc3-final-closure
----

== Added checks

* `PayingComposerScriptHygieneReport` requires `report:rc3-final-closure`.
* `PayingRuntimeProofClosureReport` requires `PayingRc3FinalClosureReport.php`.
* `PayingRuntimeProofClosureReport` requires `report:rc3-final-closure`.
* `PayingRuntimeProofCoreReport` runs `PayingRc3FinalClosureReport.php`.
* `PayingRuntimeProofCoreReport` includes the `RC-3 final closure` job label.
* Wave 56 registry integration documentation is present.

== Non-scope

* No production code change.
* No test execution.
* No PHPUnit configuration change.
