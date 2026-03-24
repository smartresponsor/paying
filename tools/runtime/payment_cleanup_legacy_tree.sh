#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
SOURCE_ROOT="$PROJECT_DIR/src"
HOLDING_ROOT="$PROJECT_DIR/var/legacy-disabled-src"

move_to_quarantine() {
    local path="$1"
    local reason="$2"
    local relative="${path#${PROJECT_DIR}/}"
    local target="$HOLDING_ROOT/$relative"

    mkdir -p "$(dirname "$target")"
    if [[ -e "$target" ]]; then
        target="${target}__$(date +%s%N)"
    fi

    mv "$path" "$target"
    printf 'Quarantined %s -> %s [%s]\n' "$relative" "${target#${PROJECT_DIR}/}" "$reason"
}

declared_symbol() {
    local path="$1"
    python - "$path" <<'PY'
import re, sys
from pathlib import Path
path = Path(sys.argv[1])
text = path.read_text(encoding='utf-8', errors='ignore')
namespace = ''
ns = re.search(r'namespace\s+([^;{]+)', text)
if ns:
    namespace = ns.group(1).strip()
cls = re.search(r'(?m)^\s*(?:final\s+|abstract\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)', text)
if not cls:
    raise SystemExit(1)
name = cls.group(1)
print(f"{namespace}\\{name}".strip('\\'))
PY
}

path_rank() {
    local path="$1"
    local normalized="${path//\\//}"
    local score=0
    [[ "$normalized" == *"/src/Entity/"* ]] && score=-50
    [[ "$normalized" =~ /[^/]+\.php/ ]] && score=$((score + 100))
    local depth
    depth=$(awk -F/ '{print NF}' <<<"${normalized#/}")
    printf '%06d|%06d|%06d|%s\n' "$score" "$depth" "${#normalized}" "$normalized"
}

if [[ -d "$SOURCE_ROOT" ]]; then
    while IFS= read -r -d '' dir; do
        relative="${dir#${PROJECT_DIR}/}"
        IFS='/' read -r -a segments <<<"${relative//\\//}"
        for segment in "${segments[@]}"; do
            if [[ "$segment" == *.php ]]; then
                move_to_quarantine "$dir" 'legacy-php-path-directory'
                break
            fi
        done
    done < <(find "$SOURCE_ROOT" -depth -type d -print0)

    declare -A map
    while IFS= read -r -d '' file; do
        if symbol=$(declared_symbol "$file" 2>/dev/null); then
            key=$(tr '[:upper:]' '[:lower:]' <<<"$symbol")
            map["$key"]+="$file"$'\n'
        fi
    done < <(find "$SOURCE_ROOT" -type f -name '*.php' -print0)

    for key in "${!map[@]}"; do
        map_payload="${map[$key]}"
        [[ -z "$map_payload" ]] && continue
        mapfile -t files < <(printf '%s' "$map_payload" | sed '/^$/d')
        (( ${#files[@]} < 2 )) && continue

        sorted=$(for file in "${files[@]}"; do printf '%s %s\n' "$(path_rank "$file")" "$file"; done | sort)
        preferred=$(awk 'NR==1{print $NF}' <<<"$sorted")
        while IFS= read -r duplicate; do
            [[ -z "$duplicate" ]] && continue
            move_to_quarantine "$duplicate" "duplicate-symbol:${key} preferred=${preferred#${PROJECT_DIR}/}"
        done < <(awk 'NR>1{print $NF}' <<<"$sorted")
    done
fi
