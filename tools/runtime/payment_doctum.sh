#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PHAR_PATH="${ROOT_DIR}/tools/runtime/doctum.phar"
CONFIG_PATH="${ROOT_DIR}/doctum.php"

if [[ ! -f "${PHAR_PATH}" ]]; then
  echo "Missing ${PHAR_PATH}. Download Doctum PHAR outside the project dependency graph before running code reference generation." >&2
  exit 1
fi

php "${PHAR_PATH}" update "${CONFIG_PATH}" --force "$@"
