#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PHAR_PATH="${ROOT_DIR}/tools/runtime/phpDocumentor.phar"
CONFIG_PATH="${ROOT_DIR}/phpdoc.dist.xml"

if [[ ! -f "${PHAR_PATH}" ]]; then
  echo "Missing ${PHAR_PATH}. Download phpDocumentor PHAR outside the project dependency graph before running docs generation." >&2
  exit 1
fi

php "${PHAR_PATH}" -c "${CONFIG_PATH}" "$@"
