= Paying Wave 44 Runtime Proof Registry Integration

This wave updates the composer script hygiene report so it knows about the runtime proof scripts added during the runtime proof phase.

== Added to hygiene expectations

* `report:runtime-issue-inventory`
* `report:runtime-proof-core`

== Scope

* Update the inspection registry/hygiene surface.
* Keep canonical readiness separate from runtime proof.
* Keep runtime proof aggregation under `report:runtime-proof-core`.

== Non-scope

* No production code change.
* No PHPUnit configuration weakening.
* No runtime behavior change.
