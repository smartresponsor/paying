= Paying Wave 58 Final Closure Handoff Integration

This wave updates the RC-3 handoff surface so it includes the final closure marker.

== Updated command

[source,bash]
----
composer report:rc3-handoff
----

== Added handoff expectation

* `report:rc3-final-closure`

== Updated delivery memo

`delivery/rc/paying-rc3-handoff.md` now names the final closure command in the verification set.

== Non-scope

* No production code change.
* No test execution.
* No PHPUnit configuration change.
