#!/usr/bin/env bash
set -euo pipefail

cd /app

if [[ ! -f vendor/autoload.php ]]; then
  composer install --no-interaction --prefer-dist
fi

if [[ "${PAYMENT_BOOTSTRAP_SCHEMA:-0}" == "1" && -f tools/runtime/payment_runtime_bootstrap.php ]]; then
  php tools/runtime/payment_runtime_bootstrap.php
fi

exec "$@"
