= Paying Wave 38 All Test Repository Stubs Completion

This wave completes anonymous `PaymentRepositoryInterface` test doubles across the test tree.

== Reason

After the finalize-handler stubs were completed, unit runtime proof exposed the same interface drift in `PaymentConsoleReadModelTest`.

== Scope

* Scan `tests/**/*.php`.
* Detect anonymous classes implementing `PaymentRepositoryInterface`.
* Add neutral throwing implementations for missing public interface methods.
* Preserve the production repository interface.

== Non-scope

* No production contract weakening.
* No production behavior change.
* No broad test rewrite.
