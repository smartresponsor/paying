#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../.."

docker compose run --rm -T \
  -e PAYMENT_BOOTSTRAP_SCHEMA=0 \
  -e OIDC_DISABLED=1 \
  app php vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite e2e
