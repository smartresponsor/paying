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

## 2026-09-20 — Canon019 technical-role topology closure

### Reconnaissance baseline

- Target boundary remains `Paying`; sibling repositories are read-only references.
- Current Git state before mutation: branch `checkpoint/paying-release-audit-20260818`, HEAD `d435aef70bf4359fcdab042d9aef7c9e4cf7838c`, clean, tracking its origin branch with ahead/behind 0/0.
- Read current Paying `README.md`, `composer.json`, PHPUnit/static-analysis configuration, prior CMCP journal, documentation inventory/headings, Composer script inventory, and relevant current source/config/test references.
- Mandatory dependency contour verified in the Paying development manifest: `objecting/object`, `cruding/crud`, `collectioning/collection`, `tabling/table`, `viewing/view`, `interfacing/interface`, and EasyAdmin are direct runtime dependencies; local path repositories use symlinks and canonical `dev-master` identities.
- Read current Objecting, Cruding, Viewing, Interfacing, Gating and Canonization contract material relevant to this pass. Normative Canonization rules consulted: Canon018 composer identity mapping, Canon019 no competing layer taxonomy, Canon020 typed Symfony role roots, Canon021 Cruding ownership of generic CRUD, Canon022 standalone dependency baseline, Canon037 generated reference artifact, Canon043 local development dependency identity, and Canon045 local repository closure.
- Target-to-canon mapping: `paying/payment` maps to `App\\Paying\\` plus `Payment*` types (Canon018); generic CRUD remains owned by Cruding (Canon021); current dependency/package contour satisfies Canon022/043/045; `config/reference.php` remains non-source under Canon037.
- Concrete RC defect selected: the current HEAD still contains 26 PHP declarations under the prohibited `src/Infrastructure/` root. This directly violates Canon019 even though the legacy Paying canonical-readiness aggregate is green. The stale local aggregate is therefore not authoritative for this rule.
- RC-critical workstream: move infrastructure-bucket classes into their real Symfony technical roles (Command, Entity, Fixture, Repository, Service and corresponding interface roots), update FQCN/config/tests/docs, and prove zero active `src/Infrastructure` implementation remains.
- Growth workstream (post-RC): provider parity, disputes/chargebacks, richer reconciliation evidence, customer checkout UX, and expanded observability remain separate capability work and do not block this topology correction.
- Market benchmark: current Adyen guidance treats idempotent POST retries, authenticated/deduplicated webhooks, asynchronous event processing, and payment lifecycle state handling as baseline operational payment concerns; Payum similarly separates gateway behavior behind reusable handlers/contracts. Paying already owns these concerns, so this pass changes topology only, not payment semantics.
- Material risks: namespace moves touch DI, Doctrine mapping, command tests, fixture tests and service wiring; semantic behavior must remain unchanged.
- Planned gates: targeted forbidden-root/reference scans, strict Composer validation, canonical reports, lint/CS/PHPStan/PHPUnit, container/YAML/Doctrine/runtime smokes, then Git diff/status/branch review and integration.

Что имеем? Current runtime/package baseline is mature, but `src/Infrastructure/` is a factual Canon019 violation that must be removed from active source topology.
Что осталось? Perform the bounded technical-role migration, update all active references, run the full verification contour, repair any regressions, and integrate the verified change.

### Implementation and verification

- Migrated the active top-level `src/Infrastructure/` taxonomy into explicit technical roles without changing payment business semantics:
  - console commands -> `src/Command/`;
  - data entities -> `src/Entity/Business/`;
  - operational entities -> `src/Entity/Operational/`;
  - fixtures -> `src/Fixture/`;
  - projection persistence -> `src/Repository/` + `src/RepositoryInterface/`;
  - operational implementations/contracts -> `src/Service/` + `src/ServiceInterface/`.
- Preserved the dual Doctrine-manager topology by mapping the data manager to `Entity/Business` and the infrastructure connection/entity manager to `Entity/Operational`; operational storage semantics were not merged into the business manager.
- Updated all active PHP callers, DI service IDs/aliases, workflow config, Doctrine mapping, tests and fixture documentation. Workspace-level scans confirm no old `App\\Paying\\Infrastructure*` or `App\\Paying\\Entity\\Payment*` references remain in active `src/config/tests/docs`; residual old names exist only in generated local cache artifacts.
- Hardened two local smoke guards that were stale after the topology move: Doctrine mapping smoke now discovers both entity subtrees and fails closed on zero entities; fixture sanity now scans `src/Fixture`.
- Updated `PayingEntityFirstPersistenceReport` so local executable evidence matches the current Canon019-compliant Entity topology instead of permitting the obsolete Infrastructure taxonomy.
- Verification after repairs:
  - PHP lint PASS: 373 files.
  - YAML lint PASS: 20 files.
  - Symfony container lint PASS.
  - Doctrine mapping smoke PASS: 16 entities (11 business, 5 operational).
  - Fixture/runtime/container aggregate smoke PASS: 5 fixtures and all runtime/container checks green.
  - PHP CS Fixer check PASS: 298 files, zero fixes required.
  - PHPStan PASS on the repository's canonical PHP 8.4 runtime-target script.
  - Unit PHPUnit PASS: 66 tests / 355 assertions.
  - Full PHPUnit PASS: 123 tests / 583 assertions, 8 documented skips; existing PHPUnit notices/deprecation remain non-failing.
  - Composer validate `--strict --check-lock` PASS.
  - Composer audit PASS: no security vulnerability advisories.
  - Paying canonical readiness PASS: 11/11 reports.
  - RC-3 final closure PASS.
- Concurrent/non-task drift appeared after the clean baseline and is intentionally excluded from this topology change: `composer.json`, `composer.lock`, `composer.prod.json` gained independent Gating/package-format changes, and `PRODUCT_CAPABILITY_AUDIT.adoc` appeared untracked. These are not attributed to this RC workstream and must not be folded into its commit.
- Two temporary untracked `.gitkeep` files were used only to materialize new nested Entity directories. Console MCP policy forbids source-file deletion, so they remain local and will not be staged or committed.

Что имеем? The Canon019 migration is implemented and verified across runtime, Doctrine, static analysis, tests, Composer integrity/security and the repository's canonical RC reports.
Что осталось? Integrate only task-owned files into coherent signed commits, attempt guarded publication, then inspect final HEAD/upstream/worktree. Concurrent Composer/audit drift and untracked temporary placeholders remain outside the task-owned integration set.

### Integration and publication status

- Signed task-owned commit created: `015e208c062a34a65016cf2e215de01cc40befa7` (`refactor: align paying with role-first topology`), containing the Canon019 migration, callers/config/tests, guard repairs, documentation synchronization, and this execution journal.
- A separate concurrent local commit appeared during the run: `29ada50` (`Retain Gating artifact surface`). It is not part of this task's implementation and is not attributed to this run.
- Post-commit branch state: `checkpoint/paying-release-audit-20260818`, upstream `origin/checkpoint/paying-release-audit-20260818`, ahead 2 / behind 0.
- Guarded push was attempted and refused with `GIT_PUSH_GUARD_BLOCKED` because the working tree is dirty.
- The remaining dirty state is isolated from the committed Canon019 work: modified `composer.json`, `composer.lock`, `composer.prod.json`; untracked `PRODUCT_CAPABILITY_AUDIT.adoc`; and the two temporary untracked Entity subtree `.gitkeep` files. The Composer/audit changes appeared after the clean baseline and are not safe to discard or commit as part of this task. Console MCP policy forbids deleting the temporary source-tree placeholders.
- Implementation-run capture confirms the only commits since baseline `d435aef` are the concurrent `29ada50` and task-owned `015e208`; the task implementation itself is fully committed.

Что имеем? Paying's Canon019 role-first topology work is locally complete, committed and green across all relevant deterministic gates. The implementation is not mixed with the concurrent Composer/Gating worktree drift.
Что осталось? Remote publication is blocked solely by the dirty-worktree guard around independent/concurrent files that this task must not destroy or absorb. Once that separate worktree is resolved, push the current branch and verify upstream synchronization.

## 2026-09-23 — Typed-role canonicalization continuation

### Reconnaissance baseline

- Target boundary: Paying only; sibling repositories are reference-only for this run.
- Current branch: `checkpoint/paying-release-audit-20260818`, HEAD `4c475f6a42955710e37f562e70880fc5992bcfba`, ahead 4 / behind 0, with a 143-path in-progress canonicalization worktree.
- Read Paying README, Composer manifest, architecture/API/operations/limits docs, runtime configuration, bundle extension, Composer script inventory and current Git state.
- Read current Canonization textual rules relevant to the migration: Canon001, Canon003, Canon006, Canon019, Canon020, Canon021, Canon022, Canon039, Canon041, Canon043, Canon045, Canon047 and Canon052; read the Gating owner contract and mandatory Objecting/Cruding/Viewing/Interfacing dependency contour materials.
- Canon mapping: DTOs belong in `src/DTO/` with exact DTO suffix/casing; first-class Builder/Factory/Handler/Normalizer/Policy/Provider/Verifier roles must not be hidden in Service buckets; Subscriber implementations are expected under EventSubscriber by current Gating; Doctrine manager dependencies belong only under Repository; generic CRUD remains owned by Cruding.
- Market baseline: payment orchestration RC expectations remain idempotent mutations, authenticated/deduplicated webhooks, durable asynchronous processing, reconciliation visibility and operational diagnostics. Provider/dispute/checkout expansion remains growth work.
- RC-critical workstream selected: finish the current typed-role migration, eliminate concrete Gating failures introduced/exposed by that migration, synchronize stale architecture documentation, and verify the resulting runtime/package contract.
- Growth workstream: broader provider parity, dispute/chargeback handling, richer reconciliation evidence and customer checkout UX remain post-RC and are not allowed to block structural correctness.
- Initial Gating result: Canon003/006/019/021/022/043/045 are green; hard failures remain in Canon001, Canon020, Canon030, Canon039, Canon041, Canon047 and Canon052. Canon011/015/040/042 are warnings/coverage evidence gaps.

Что имеем? The current worktree is directionally canonical and already closes the main DTO and typed-role migration, but the executable canon exposes several concrete RC gaps that must be addressed before integration.
Что осталось? Close the bounded source-topology and tooling failures that can be safely repaired inside Paying, re-run Gating and runtime gates, then integrate only the verified task-owned state.

### RC continuation result

- Closed the active typed-role migration tail: Symfony subscribers now live under `src/EventSubscriber`; domain `*Event` classes live under `src/Event`.
- Added canonical Doctrine schema/migration scripts, persistent branch-aware PHPUnit coverage, Panther, and repository-root Playwright configuration.
- Replaced forbidden capability symlinks for Cataloging and Navigating with their verified Git VCS remotes; Canon053 now passes.
- Introduced typed repository contracts for operational, outbox/DLQ, webhook, and reconciliation persistence. Direct `EntityManagerInterface` usage is now repository-owned; Canon047 passes.
- Replaced implicit `unique: true` schema names with deterministic semantic lower_snake_case constraints; Canon054 passes.
- Reduced consumer `.gating/` to the artifact-only README contract; Canon052 passes.
- Corrected the refund route segment order and removed the broad destructive cleanup pattern from the local pipeline; both legacy/profile checks now pass.
- Synchronized authoritative architecture docs with `Entity/Operational`, repository-owned persistence, and `EventSubscriber` topology.
- Verification: Gating reaches 69/70 without hard failure. The sole remaining hard failure is Canon001, whose executable closed-root behavior conflicts with the consulted textual Canon001 rule that explicitly treats unknown role roots as escalation candidates; the reported roots are standard `Attribute`, `ControllerInterface`, `EntityInterface`, and `Fixture` surfaces.
- Verification: targeted `php -l` passes for the new repository/event-subscriber code; `composer validate --strict --check-lock` passes.
- Runtime test blocker: PHPUnit bootstrap currently fails inside the mandatory symlinked Viewing dependency because `Viewing/Resources/config/services.php` expects `App\\Viewing\\Controller\\ViewHomeController`, which is absent from the current Viewing worktree. Paying must not patch Viewing under this task boundary.

Что имеем? Paying's own Canon020/030/039/041/047/052/053/054 and legacy typed-layer/route/mutation hard failures are closed, manifest validation is green, and the remaining local hard gate is a Canonization/Gating mirror disagreement rather than a justified Paying topology mutation.
Что осталось? RC integration is blocked by the external Viewing runtime bootstrap defect and the Canon001 textual/executable mismatch. Do not absorb or mutate those sibling responsibilities from Paying; re-run PHPUnit/full quality and integrate after the owning repositories resolve those blockers.

### 2026-09-24 final acceptance tail

- The prior external Viewing bootstrap blocker is no longer present in the current dependency state: full PHPUnit now executes successfully.
- Canon039 was made executable with PHPUnit 12-compatible `--path-coverage`; Gating reports Canon039 PASS. The persistent coverage artifact remains stale because the long coverage run exceeded the Console MCP foreground wrapper before rewriting `var/coverage/phpunit.txt`; Canon040 therefore remains warning-only evidence debt.
- Canon031 semantic PHPDoc coverage was raised above threshold without behavior changes: classes 199/257 (77.4%), contract methods 313/447 (70.0%); Canon031 PASS.
- Static-analysis residuals from the repository-contract migration were closed: restored the explicit `PaymentNotFoundException` import in `PaymentApiSurfaceBuilder` and removed redundant always-true entity filters now guaranteed by typed repository contracts.
- Final managed acceptance run `e3e354d9-b928-436f-a868-1c6079dde22a` completed successfully with exit 0:
  - PHP CS Fixer: PASS, 299 files, zero fixes required.
  - PHPStan: PASS, no errors.
  - PHPUnit: PASS, 123 tests / 566 assertions / 8 skips; existing PHPUnit notice/deprecation output remains non-failing.
  - Symfony container lint: PASS.
  - Doctrine default entity manager: mapping correct and database schema in sync.
  - Doctrine infrastructure entity manager: mapping correct and database schema in sync.
  - Doctrine migrations currentness: PASS, no migrations to execute.
  - Paying canonical readiness: PASS, 11/11 reports, zero failed reports.
- Aggregate Gating now has exactly one hard failure: Canon001. The current normative Canon001 explicitly states that its role-root list is not closed and unknown roots are escalation candidates; Gating instead hard-fails the legitimate Paying technical-role roots `Attribute`, `ControllerInterface`, `EntityInterface`, and `Fixture`. Paying must not misclassify these types merely to satisfy the closed executable allow-list.
- Canon052 and mutation firewall both pass after quarantining the obsolete embedded Gating checkout outside active source/tooling surfaces.
- Temporary diagnostic artifacts are excluded from Git integration: the acceptance helper remains under ignored `.codex-tmp/`, while the preserved legacy Gating checkout is quarantined under ignored `var/legacy-gating-consumer-checkout/` outside active source/tooling scans.

Что имеем? Paying is runtime-, persistence-, static-analysis-, local-readiness-, and quality-green. All justified Paying-owned hard canonical defects found in this run are closed.
Что осталось? Only the external Canon001 Canonization/Gating mirror mismatch prevents aggregate Gating from reporting all-hard-green. Integrate the verified Paying-owned tail without mutating sibling Gating/Canonization responsibility.
