= Paying Wave 36 Runtime Test Hotfix

This wave addresses the first runtime-proof blockers after RC-2 canonical readiness became green.

== Fixed runtime blockers

* Reformat `composer.json` using stable UTF-8 without BOM and standard JSON spacing.
* Align unit test class names with their file names:
  ** `PaymentProjectionLagServiceTest`
  ** `PaymentRefundServiceTest`
* Complete the anonymous `PaymentRepositoryInterface` test double used by `PaymentConsoleFinalizeHandlerTest`.

== Scope boundary

This wave is test/runtime-proof focused. It does not change production contracts, Doctrine mappings, runtime services, or canonical namespace policy.
