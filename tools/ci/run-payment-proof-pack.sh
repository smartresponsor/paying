#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

TIMESTAMP="$(date -u +%Y%m%d-%H%M%S)"
REPORT_BASE="$ROOT_DIR/var/report/proof-pack"
REPORT_ROOT="$REPORT_BASE/$TIMESTAMP"
LATEST_ROOT="$REPORT_BASE/latest"
LOG_ROOT="$REPORT_ROOT/logs"

mkdir -p "$LOG_ROOT"
rm -rf "$LATEST_ROOT"
mkdir -p "$LATEST_ROOT"

steps=(
  'install-preflight|composer install:preflight'
  'qa-style|composer qa:style'
  'qa-static|composer qa:static'
  'qa-test|composer qa:test'
  'qa-smoke|composer qa:smoke'
  'qa-report|composer qa:report'
  'pipeline-strict|bash tools/ci/run-payment-local-pipeline.sh --include-smokes --include-reports --fail-on-errors'
)

SUMMARY_ROWS=''
JSON_LINES_FILE="$REPORT_ROOT/steps.jsonl"
: > "$JSON_LINES_FILE"
PACK_START="$(date +%s)"
OVERALL_STATUS='passed'

run_step(){
  local name="$1" command="$2" log_file="$LOG_ROOT/${name}.log" cmd_file="$LOG_ROOT/${name}.cmd.txt"
  printf '%s\n' "$command" > "$cmd_file"
  printf '[RUN ] %s -> %s\n' "$name" "$command"
  local step_start="$(date +%s)"
  set +e
  bash -lc "$command" 2>&1 | tee "$log_file"
  local exit_code=${PIPESTATUS[0]}
  set -e
  local step_end
  step_end="$(date +%s)"
  local duration=$((step_end-step_start))
  local status='passed'
  if [[ "$exit_code" -ne 0 ]]; then status='failed'; OVERALL_STATUS='failed'; fi
  printf '[%s] %s\n' "$( [[ "$exit_code" -eq 0 ]] && echo PASS || echo FAIL )" "$name"
  SUMMARY_ROWS+="| ${name} | ${status} | ${exit_code} | ${duration} | \`logs/${name}.log\` |"$'\n'
  python3 - "$name" "$command" "$status" "$exit_code" "$duration" >> "$JSON_LINES_FILE" <<'PY'
import json, sys
name, command, status, exit_code, duration = sys.argv[1:6]
print(json.dumps({"name": name, "command": command, "status": status, "exit_code": int(exit_code), "duration_seconds": int(duration), "log": f"logs/{name}.log", "command_file": f"logs/{name}.cmd.txt"}))
PY
  return "$exit_code"
}

for step in "${steps[@]}"; do
  name="${step%%|*}"; command="${step#*|}"
  if ! run_step "$name" "$command"; then
    break
  fi
done

PACK_END="$(date +%s)"; DURATION_TOTAL=$((PACK_END-PACK_START))
cat > "$REPORT_ROOT/summary.md" <<MD
# Payment canonical proof pack

- Timestamp (UTC): ${TIMESTAMP}
- Report root: \`${REPORT_ROOT}\`
- Result: ${OVERALL_STATUS^^}
- Duration seconds: ${DURATION_TOTAL}
- Scope: clean install check + QA + smokes + reports + strict local pipeline

| Step | Status | Exit | Duration s | Log |
|---|---:|---:|---:|---|
${SUMMARY_ROWS}
MD

python3 - "$TIMESTAMP" "$REPORT_ROOT" "$OVERALL_STATUS" "$DURATION_TOTAL" "$JSON_LINES_FILE" > "$REPORT_ROOT/report.json" <<'PY'
import json, sys
from pathlib import Path
timestamp, report_root, status, duration, jsonl_path = sys.argv[1:6]
steps=[json.loads(line) for line in Path(jsonl_path).read_text().splitlines() if line.strip()]
print(json.dumps({"proof_pack":"payment-canonical","timestamp":timestamp,"report_root":report_root,"status":status,"duration_seconds":int(duration),"steps":steps}, indent=2))
PY

cp -R "$REPORT_ROOT"/* "$LATEST_ROOT/"
printf '%s\n' "$TIMESTAMP" > "$LATEST_ROOT/LATEST.txt"
printf '%s\n' "$TIMESTAMP" > "$REPORT_BASE/latest.txt"
printf '\nProof pack root: %s\nSummary: %s\nJSON: %s\n' "$REPORT_ROOT" "$REPORT_ROOT/summary.md" "$REPORT_ROOT/report.json"

[[ "$OVERALL_STATUS" == 'passed' ]]
