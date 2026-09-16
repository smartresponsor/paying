= Paying Wave 47 Runtime Core Closure Integration

This wave integrates the static runtime proof closure guard into the runtime proof core aggregate.

== Updated command

[source,bash]
----
composer report:runtime-proof-core
----

== Added job

* Runtime proof closure

== Intent

The runtime proof core now checks both:

* execution proof: Composer validation, canonical readiness, unit, functional, and security tests;
* static proof surface closure: runtime proof scripts, docs, Composer metadata, and proof anchors.

== Non-scope

* No production code change.
* No runtime behavior change.
* No PHPUnit configuration change.
