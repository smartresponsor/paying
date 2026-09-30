= Paying Wave 30 RC Gate Hotfix

This wave updates inspection gates only.

== Scope

* Keep local Symfony generated directories `var/` and `vendor/` from failing the packaging/root surface guard.
* Scope double `PaymentPayment*` detection to runtime/application surfaces instead of self-scanning `tools/inspection`.
* Resolve canonical closure checks by canonical basename so valid Symfony-oriented class moves do not fail stale path expectations.

== Non-scope

* No runtime proof.
* No mass rename.
* No repository-wide cleanup.
* No destructive overwrite.
