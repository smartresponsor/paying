= Paying Wave 51 RC-3 Milestone Hygiene Closure

This wave strengthens the RC-3 milestone marker so it verifies registry coverage too.

== Updated command

[source,bash]
----
composer report:rc3-milestone
----

== Added checks

* `PayingComposerScriptHygieneReport.php` requires `report:rc3-milestone`.
* `PayingRuntimeProofClosureReport.php` requires `report:rc3-milestone`.
* `PayingRuntimeProofClosureReport.php` requires `PayingRc3MilestoneReport.php`.
* Wave 50 closure integration documentation is present.

== Non-scope

* No production code change.
* No test execution.
* No PHPUnit configuration change.
