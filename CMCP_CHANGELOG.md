# CMCP orchestration journal

## engine-20260912085103-paying-fb172b

### Iteration 1 — reconnaissance and baseline

- Target boundary: `Paying` only; sibling repositories were read as references and remain untouched.
- Read: `README.md`, `composer.json`, `docs/ARCHITECTURE.md`, `docs/API.md`, `docs/OPERATIONS.md`, `docs/INSTALL.md`, current Git state, relevant Paying source/config/tests, and dependency contracts from Objecting, Cruding, Viewing, and Interfacing.
- Canon consulted: Canonization `AGENTS.md`, `Canon001TechnicalRoleFirstRule`, `Canon019NoAlternativeLayerTaxonomyRule`, `Canon020TypedSymfonyRoleRootRule`, `Canon021CrudingOwnsGenericCrudRule`, and `Canon038ConfigYamlSubjectPrefixRule`; Gating `README.md`, `AGENTS.md`, and `composer.json` were also inspected.
- Dependency contour: `objecting/object`, `cruding/crud`, `viewing/view`, and `interfacing/interface` are explicit Composer dependencies and local path repositories. Paying remains the owner of payment lifecycle semantics while generic CRUD remains in Cruding.
- Baseline gates: `report:paying-canonical-readiness` GREEN; `report:rc3-final-closure` GREEN.
- Market/enterprise baseline for payment orchestration: idempotent mutation handling, authenticated webhooks, fast acknowledgement plus asynchronous processing, durable event/outbox handling, refund safety, and operational diagnostics are table stakes. Current Paying already exposes idempotency, webhook verification/ingest, outbox/DLQ, provider routing, and metrics/status surfaces.
- RC-critical workstream selected: remove the legacy `Surface` implementation vocabulary from active Paying PHP type/folder names and routing/DI references, aligning presentation payload naming with the role-first Symfony canon and Interfacing/Viewing terminology without changing payment behavior.
- Growth workstream (post-RC, not blocking this run): broader provider parity, disputes/chargebacks, richer reconciliation evidence, and checkout/customer UX remain product-maturity opportunities outside this bounded canonical cleanup.
- Material risks: routes currently target service classes directly; renaming must be complete across source, DI, routes, tests, reports, and documentation references. Existing untracked `.gating/` predates this run and will not be modified or committed unless required by a verified gate.
- Planned gates after implementation: targeted grep for legacy type names, `composer validate --strict`, Paying canonical reports, style/static/test gates, Symfony container/YAML smoke where available, then Git status/diff review.

Что имеем? Structural RC reports are green, but active code still contains a legacy `src/Value/Surface` folder and `*Surface*` PHP type names.

Что осталось? Rename that bounded active implementation surface canonically, update all callers/contracts/tests, run gates, integrate Git, and perform final acceptance.

### Iteration 2 — material implementation

- Added canonical `PaymentConsoleViewPayload` under `src/ValueObject/` and `PaymentConsoleViewFactory` under `src/Service/Payment/`.
- Switched the active payment console builder and unit coverage to the canonical view factory.
- The task forbids destructive operations and Console MCP rejected file deletion/rename; therefore legacy `PaymentSurfaceContract` and `PaymentSurfaceContractFactory` were reduced to explicit `@deprecated` compatibility bridges with stated removal conditions instead of being deleted.
- No sibling repository was modified.

Что имеем? Active Paying console presentation flow uses the canonical view payload/factory; legacy Surface types no longer own implementation.

Что осталось? Verify runtime/static/Doctrine behavior and close any factual in-scope failure exposed by the gates.

### Iteration 3 — verification and fix

- `test:unit` first exposed a transient parse error in the newly added payload; the file was repaired immediately.
- The next unit bootstrap exposed a real pre-existing Doctrine failure: `PaymentRecurringEntity` mapped local `status` while Objecting `ObjectStateEmbeddableTrait` already maps canonical `status`. Removed the duplicate local mapped field while preserving `status()` / `setStatus()` as Paying business aliases over Objecting state.
- Verification after fixes: `test:unit` GREEN (66 tests, 346 assertions), `lint` GREEN (373 files), `lint:container` GREEN, `lint:yaml` GREEN (20 files), `smoke:doctrine` GREEN (11 entities), `cs:check` GREEN, canonical readiness GREEN, runtime issue inventory GREEN, full `test` GREEN (123 tests, 574 assertions; 8 documented skips, notices/deprecation remain non-failing).
- `composer validate --strict` confirms valid JSON but exits non-zero only for existing unbound `*@dev` constraints on internal packages.
- `stan` exits non-zero, but Console MCP returned a truncated debug stream without the diagnostic tail; deterministic gates and runtime tests do not reproduce a regression. This remains an acceptance caveat until a non-debug/static diagnostic surface exposes the concrete findings.

Что имеем? Runtime, Doctrine, syntax, container, YAML, style, unit/full tests, and Paying canonical reports are green after the mapping fix.

Что осталось? Review the exact diff, integrate only task-owned files, leave pre-existing `.gating/` untouched, publish the branch if Git state permits, then perform final acceptance.

### Iteration 4 — debt closure and integration

- Reviewed the worktree and restored incidental `config/reference.php` regeneration so it is not part of the task diff.
- Task-owned integration set is limited to the CMCP journal, the canonical console view payload/factory, their bounded deprecated compatibility bridges/callers/test, and the verified `PaymentRecurringEntity` Objecting-status fix.
- Pre-existing untracked `.gating/` remains untouched and excluded from staging.
- Console MCP explicitly rejected file rename/copy patches and file deletion. Consequently `PaymentConsoleSurfaceBuilder` and the two deprecated Surface compatibility files cannot be physically renamed/deleted in this non-destructive run; their active implementation dependencies have nevertheless been migrated away from the legacy contract/factory.
- Branch `checkpoint/paying-release-audit-20260818` tracks `origin/checkpoint/paying-release-audit-20260818`, is not a protected push branch, and was already two commits ahead before this task.

Что имеем? The bounded code change is verified and isolated; no unrelated generated drift or sibling repository change remains in the integration set.

Что осталось? Create the coherent signed commit, push the current branch, inspect post-push HEAD/worktree/upstream state, and record final acceptance.
