= Paying Wave 59 Final Closure Handoff Registry

This wave strengthens the final RC-3 closure marker so it verifies the Wave 58 handoff integration.

== Updated command

[source,bash]
----
composer report:rc3-final-closure
----

== Added checks

* Wave 58 documentation exists.
* `PayingRc3HandoffReport` requires `report:rc3-final-closure`.
* `PayingRc3HandoffReport` names the final closure milestone.
* `delivery/rc/paying-rc3-handoff.md` includes `composer report:rc3-final-closure`.

== Non-scope

* No production code change.
* No test execution.
* No PHPUnit configuration change.
