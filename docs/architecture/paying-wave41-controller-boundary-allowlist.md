= Paying Wave 41 Controller Boundary Allowlist

This wave refines the controller/service architecture test after Wave 40 replaced the invalid regex parser with token-based import parsing.

== Scope

* Keep the concrete service dependency rule.
* Allow typed exceptions.
* Allow webhook validator/normalizer/signature-validator collaborators only under `src/Controller/Webhook`.

== Non-scope

* No production controller rewrite.
* No production service contract change.
* No PHPUnit configuration weakening.
