#!/usr/bin/env bash
set -euo pipefail

# Marketing America Corp. Oleksandr Tishchenko

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

missing=0
for path in composer.json .env.example .env.test phpunit.xml.dist docs/architecture/payment-installed-runtime-proof-track.md; do
  if [[ -f "$path" ]]; then
    printf 'OK   %s\n' "$path"
  else
    printf 'MISS %s\n' "$path" >&2
    missing=1
  fi
done

if [[ -f composer.lock ]]; then
  printf 'OK   composer.lock\n'
else
  printf 'WARN composer.lock not committed yet\n'
fi

mkdir -p var
printf 'OK   var\n'

exit "$missing"
