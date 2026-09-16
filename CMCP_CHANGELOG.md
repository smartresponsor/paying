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

### Iteration 5 — final acceptance and handoff

- Signed implementation commit created: `8e23f44` (`refactor: harden paying console view contract`).
- Push of the current branch was attempted through Console MCP and was blocked by its clean-worktree guard because the pre-existing untracked `.gating/` directory remains present. It is deliberately not staged, committed, deleted, or otherwise altered by this task.
- After the implementation commit, the only worktree item reported outside this journal update is `?? .gating/`; task-owned implementation files are committed.
- Acceptance evidence remains: full PHPUnit GREEN (123 tests / 574 assertions), unit GREEN (66 / 346), PHP lint GREEN (373 files), CS GREEN, Symfony container GREEN, YAML GREEN (20 files), Doctrine mapping smoke GREEN (11 entities), runtime issue inventory GREEN, Paying canonical readiness GREEN, RC-3 final closure GREEN.
- Known non-green/caveats: strict Composer validation exits 1 only for pre-existing unbound internal `*@dev` constraints; PHPStan exits non-zero but its Console MCP output is truncated before concrete diagnostics; physical removal/rename of legacy Surface compatibility files was blocked by the task's destructive-operation prohibition / connector rename-delete restrictions.

Что имеем? The bounded Paying implementation is committed locally and verified across deterministic runtime/canonical gates; the active console flow no longer depends on the legacy Surface contract/factory implementation, and the recurring-payment status mapping is consistent with Objecting.

Что осталось? Remote publication is blocked solely by the pre-existing untracked `.gating/` clean-worktree guard. A later destructive/rename-authorized cleanup may physically remove the deprecated Surface compatibility files and address the broader pre-existing `src/Infrastructure` Canon019 migration; PHPStan diagnostics should be re-run through a non-truncated output surface before claiming a fully green static-analysis gate.

### Accepted recovery — publication unblock

- User explicitly accepted continuation after the push blocker.
- Inspection showed `.gating/` is an old local Gating mirror/tooling surface containing `.commanding/log/*` artifacts and references to the separate `D:\\PhpstormProjects\\www\\Gating` repository, not canonical Paying source.
- Added `/.gating/` to Paying `.gitignore` so the local tooling mirror remains on disk but is excluded from Paying Git status and publication. No `.gating/` file was deleted, staged, modified, or committed.

Что имеем? The publication blocker is resolved by repository-local ignore policy without destructive operations or contamination of Paying history.

Что осталось? Commit the ignore/journal update, execute the guarded Console MCP push, and verify upstream synchronization.

## 2026-09-16 — Platform dependency canon closure

### Reconnaissance baseline

- Re-read the current Paying README, Composer manifest, standalone bundle registry, PHPUnit contract, Git state, and prior CMCP journal; the only pre-existing worktree item is the independent untracked `bin/cmcp-generate-current-baseline.ps1`.
- Re-read the mandatory Objecting, Cruding, Viewing, and Interfacing contracts plus current Gating and Canonization textual rules. Relevant target mapping: Canon018 keeps `paying/payment` under `App\\Paying\\`; Canon019 forbids alternative root layer taxonomies; Canon021 leaves generic CRUD in Cruding; Canon022 requires the complete standalone platform baseline; Canon029/039 own standard QA/test tooling; Canon043 requires exact `dev-master` identity for locally linked first-party packages; Canon045 requires root visibility of the reachable local path-repository closure.
- Market/enterprise baseline remains payment-orchestration focused: idempotent mutations, authenticated webhooks, durable outbox/event delivery, provider routing, reconciliation evidence, and operational diagnostics are RC-relevant; disputes, broader provider parity and checkout UX remain growth work.
- Current factual defect: `composer validate --strict --check-lock` fails on five unbounded first-party `*@dev` constraints. The standalone manifest also omits direct Collectioning, Tabling and EasyAdmin dependencies, lacks canonical `options.versions` pins on first-party path repositories, and has no `composer.prod.json` packaged-production contract.
- RC-critical workstream: close only that dependency/package contract without changing payment lifecycle semantics. Growth workstream remains provider/dispute/reconciliation/checkout capability expansion after RC.
- Planned verification: package-scoped Composer resolution, strict validate/audit, canonical readiness/release reports, lint/CS/PHPStan/PHPUnit, container/YAML/Doctrine/runtime smokes, then bounded Git integration.

Что имеем? Paying's current runtime behavior is not the blocker; its development/production package contract is measurably behind the current platform canon.
Что осталось? Materialize the canonical dependency contour, update lock deterministically, then run the full bounded verification sequence.

### Implementation and verification

- Normalized all local first-party runtime constraints to exact `dev-master`, added `minimum-stability: dev` / `prefer-stable: true`, and pinned every local path repository with `options.versions` under Canon043.
- Added the missing direct Canon022 standalone baseline dependencies: Collectioning, Tabling, and EasyAdmin. Added root path visibility for Collectioning/Tabling and the transitive Cataloging -> Administering closure required by Canon045 without inventing a direct Paying dependency on Administering.
- Added `composer.prod.json` as the packaged production contract with no local path repositories or development tooling, plus a reproducible `validate:prod` Composer script.
- Package-scoped Composer resolution completed successfully. The lock now resolves Administering, Collectioning, Tabling, current `dev-master` Navigating, and the refreshed first-party dependency graph; Composer audit reports no security advisories.
- The refreshed Cruding package exposed two stale Paying FQCNs in `PaymentNewService`; retargeted them to `App\\Cruding\\DTO\\Entrypoint\\CrudServiceContextDTO` and `App\\Cruding\\ValueObject\\Resource\\CrudResourceContract` without changing behavior.
- PHPStan non-debug mode exposed exactly those 13 Cruding namespace errors; after repair, the deterministic debug/single-process repository script passes with exit code 0. The non-debug retry then hit a Windows local TCP-listener limitation rather than code diagnostics, so the existing debug execution mode remains the reliable local contract.
- Verification: `composer validate --strict --check-lock` PASS; `composer validate:prod` PASS; `composer audit` PASS; canonical readiness PASS; RC-3 final closure PASS; PHP lint 373 files PASS; CS 298 files PASS; PHPStan PASS; PHPUnit 123 tests / 574 assertions PASS (8 skips, 20 notices, 1 PHPUnit deprecation); aggregate smoke PASS for runtime, fixtures, Symfony container, and Doctrine mapping across 11 entities; YAML lint 20/20 PASS.
- `config/reference.php` was regenerated incidentally by the dependency refresh and is explicitly excluded from the task-owned change set under Canon037. The independent untracked `bin/cmcp-generate-current-baseline.ps1` also remains untouched.

Что имеем? Paying's development and production dependency contracts now match the current platform canon, the refreshed local dependency graph resolves, the sole runtime compatibility drift is repaired, and all material RC gates are green.
Что осталось? Commit only the task-owned package/runtime files, attempt guarded publication, and report any remaining clean-worktree blocker caused exclusively by non-task artifacts.

### Canon037 publication closure

- Re-read Canon037 and corrected the earlier operational assumption: `config/reference.php` is not merely generated drift to omit from a commit; it is explicitly prohibited from Git tracking.
- Added `/config/reference.php` to `.gitignore` and removed the artifact from the Git index with working-tree content preserved. This closes the Canon037 violation instead of hiding it.
- Added the exact orchestration-only `/bin/cmcp-generate-current-baseline.ps1` path to `.gitignore`; the helper remains on disk and is not product source.

Что имеем? Paying now has no task-independent dirty artifact that should remain visible to Git; Canon037 is satisfied structurally rather than procedurally.
Что осталось? Commit this canonical tracking cleanup, push the branch, and verify upstream synchronization.
