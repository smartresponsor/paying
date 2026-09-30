= Paying Wave 37 Finalize Handler Repository Stub Completion

This wave completes all anonymous `PaymentRepositoryInterface` test doubles in `PaymentConsoleFinalizeHandlerTest`.

== Reason

Wave 36 completed the first anonymous repository stub only. Unit runtime proof then exposed a second anonymous stub in the same test file.

== Scope

* Scan all anonymous classes implementing `PaymentRepositoryInterface` in `PaymentConsoleFinalizeHandlerTest`.
* Add neutral throwing implementations for missing public interface methods.
* Preserve production repository contracts.

== Non-scope

* No production interface weakening.
* No production repository behavior changes.
* No broad test rewrite.
