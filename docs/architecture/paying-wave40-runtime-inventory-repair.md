= Paying Wave 40 Runtime Inventory Repair

This wave fixes the runtime issue inventory surface and the unit warning source it exposed.

== Fixed

* `report:runtime-issue-inventory` now runs the security contour using the same filter as `composer test:security`.
* `composer.json` is normalized back to stable JSON spacing and UTF-8 without BOM.
* `ControllerServiceBoundaryTest` now uses PHP token parsing instead of an invalid regex over namespaced imports.

== Non-scope

* No production code change.
* No PHPUnit configuration weakening.
* No skipped-test policy change.
