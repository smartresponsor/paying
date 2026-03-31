#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"
include_smokes=0
include_reports=0
include_security=0
fail_on_errors=0
for arg in "$@"; do
  case "$arg" in
    --include-smokes) include_smokes=1 ;;
    --include-reports) include_reports=1 ;;
    --include-security) include_security=1 ;;
    --fail-on-errors) fail_on_errors=1 ;;
  esac
done
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
REPORT_BASE="$ROOT_DIR/var/report/local"
REPORT_ROOT="$REPORT_BASE/$TIMESTAMP"
LATEST_ROOT="$REPORT_BASE/latest"
LOG_ROOT="$REPORT_ROOT/logs"
mkdir -p "$LOG_ROOT"
rm -rf "$LATEST_ROOT"
mkdir -p "$LATEST_ROOT"
steps=(
  'install-preflight|composer install:preflight'
  'lint|composer lint'
  'lint-yaml|composer lint:yaml'
  'lint-container|composer lint:container'
  'cs-check|composer cs:check-quiet'
  'stan|composer stan:runtime-target'
  'docs-phpdoc-check|composer docs:phpdoc:check'
  'test-bootstrap-reset|composer test:bootstrap:reset'
  'test|composer test'
  'test-ui-playwright|composer test:ui:playwright'
)
if [[ "$include_smokes" -eq 1 ]]; then
  steps+=(
    'smoke-runtime|composer smoke:runtime'
    'smoke-fixtures|composer smoke:fixtures'
    'smoke-container|composer smoke:container'
    'smoke-doctrine|composer smoke:doctrine'
  )
fi
if [[ "$include_reports" -eq 1 ]]; then
  steps+=(
    'report-route-inventory|composer report:route-inventory'
    'report-runtime-proof|composer report:runtime-proof'
  )
fi
if [[ "$include_security" -eq 1 ]]; then
  steps+=(
    'security-composer-audit|composer security:composer-audit'
    'security-importmap-audit|composer security:importmap-audit'
    'security-gitleaks|composer security:gitleaks'
    'security-semgrep-ce|composer security:semgrep-ce'
    'test-security|composer test:security'
  )
fi
SUMMARY_ROWS=''
JSON_LINES_FILE="$REPORT_ROOT/steps.jsonl"
: > "$JSON_LINES_FILE"
PIPELINE_START="$(date +%s)"
OVERALL_STATUS='passed'
run_step(){
  local name="$1" command="$2" log_file="$LOG_ROOT/${name}.log" cmd_file="$LOG_ROOT/${name}.cmd.txt"
  printf '%s\n' "$command" > "$cmd_file"
  printf '[RUN ] %s -> %s\n' "$name" "$command"
  # shellcheck disable=SC2155
  local step_start="$(date +%s)"
  set +e
  bash -lc "$command" 2>&1 | tee "$log_file"
  local exit_code=${PIPESTATUS[0]}
  set -e
  local step_end="$(date +%s)" duration=$((step_end-step_start)) status='passed'
  if [[ "$exit_code" -ne 0 ]]; then status='failed'; OVERALL_STATUS='failed'; fi
  printf '[%s] %s\n' "$( [[ "$exit_code" -eq 0 ]] && echo PASS || echo FAIL )" "$name"
  SUMMARY_ROWS+="| ${name} | ${status} | ${exit_code} | ${duration} | \`logs/${name}.log\` |"$'\n'
  python3 - "$name" "$command" "$status" "$exit_code" "$duration" >> "$JSON_LINES_FILE" <<'PY'
import json, sys
name, command, status, exit_code, duration = sys.argv[1:6]
print(json.dumps({"name": name, "command": command, "status": status, "exit_code": int(exit_code), "duration_seconds": int(duration), "log": f"logs/{name}.log", "command_file": f"logs/{name}.cmd.txt"}))
PY
  [[ "$exit_code" -eq 0 ]] || [[ "$fail_on_errors" -ne 1 ]]
}
for step in "${steps[@]}"; do
  name="${step%%|*}"; command="${step#*|}"
  run_step "$name" "$command" || break
done
PIPELINE_END="$(date +%s)"; DURATION_TOTAL=$((PIPELINE_END-PIPELINE_START))
cat > "$REPORT_ROOT/summary.md" <<MD
# Payment local pipeline report

- Timestamp: ${TIMESTAMP}
- Report root: \`${REPORT_ROOT}\`
- Result: ${OVERALL_STATUS^^}
- Duration seconds: ${DURATION_TOTAL}
- Include smokes: ${include_smokes}
- Include reports: ${include_reports}
- Include security: ${include_security}
- Fail on errors: ${fail_on_errors}

| Step | Status | Exit | Duration s | Log |
|---|---:|---:|---:|---|
${SUMMARY_ROWS}
MD
python3 - "$TIMESTAMP" "$REPORT_ROOT" "$OVERALL_STATUS" "$DURATION_TOTAL" "$JSON_LINES_FILE" "$include_smokes" "$include_reports" "$include_security" "$fail_on_errors" > "$REPORT_ROOT/report.json" <<'PY'
import json, sys
from pathlib import Path
timestamp, report_root, status, duration, jsonl_path, include_smokes, include_reports, include_security, fail_on_errors = sys.argv[1:10]
steps=[json.loads(line) for line in Path(jsonl_path).read_text().splitlines() if line.strip()]
print(json.dumps({"pipeline":"payment-local","timestamp":timestamp,"report_root":report_root,"status":status,"duration_seconds":int(duration),"include_smokes":bool(int(include_smokes)),"include_reports":bool(int(include_reports)),"include_security":bool(int(include_security)),"fail_on_errors":bool(int(fail_on_errors)),"steps":steps}, indent=2))
PY
cp -R "$REPORT_ROOT"/* "$LATEST_ROOT/"
printf '%s\n' "$TIMESTAMP" > "$LATEST_ROOT/LATEST.txt"
printf '%s\n' "$TIMESTAMP" > "$REPORT_BASE/latest.txt"
printf '\nReport root: %s\nSummary: %s\nJSON: %s\n' "$REPORT_ROOT" "$REPORT_ROOT/summary.md" "$REPORT_ROOT/report.json"
[[ "$OVERALL_STATUS" == 'passed' ]]
