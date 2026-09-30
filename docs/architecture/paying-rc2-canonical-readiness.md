= Paying RC-2 Canonical Readiness

Wave 31 introduced a single canonical readiness gate for the Paying component. Wave 33 integrates the dedicated entity-first consistency gate into that readiness chain.

== Goal

Provide one command that summarizes the current non-runtime canonicalization state:

[source,bash]
----
composer report:paying-canonical-readiness
----

== Included gates

* Packaging/root surface
* Release-candidate structure
* Canonical structure closure
* Canonical name-form summary
* Application surface name-form
* Source residual name-form
* Entity-first persistence
* Entity-first consistency
* Test canonical closure
* Inspection script registry
* Composer script hygiene

== Separate from runtime proof

This gate intentionally does not prove runtime behavior. Runtime proof remains a later phase and should include composer validation, autoload generation, unit tests, functional tests, security tests, and Symfony container checks.
