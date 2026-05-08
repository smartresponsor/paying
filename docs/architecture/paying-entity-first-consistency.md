= Paying Entity-First Consistency Gate

Wave 32 adds a focused entity-first consistency report for the Paying component.

== Checked areas

* Doctrine entity class name-form.
* Explicit `payment` / `payment_*` table prefix.
* Entity `repositoryClass` linkage.
* Repository class name-form.
* Repository interface name-form.
* Symfony form type name-form.

== Scope boundary

This is an inspection gate. It does not run migrations, connect to a database, validate the Symfony container, or execute runtime tests.
