#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../.."

docker compose up -d pg redis app
docker compose exec -T app sh -lc 'php tools/runtime/payment_runtime_bootstrap.php'
