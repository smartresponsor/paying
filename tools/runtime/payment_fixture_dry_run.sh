#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../.."

db_name="payment_fixture_dry_run_$(date +%s)"
infra_dir="/tmp/${db_name}"
infra_url="sqlite:///${infra_dir}/payment.infra.sqlite"
database_url="pgsql://app:app@pg:5432/${db_name}"

cleanup() {
  docker compose exec -T \
    -e DATABASE_URL="${database_url}" \
    -e INFRA_URL="${infra_url}" \
    -e OIDC_DISABLED=1 \
    -e PAYMENT_BOOTSTRAP_SCHEMA=0 \
    app sh -lc 'php bin/console doctrine:database:drop --force --if-exists --no-interaction >/dev/null 2>&1 || true; rm -rf "'"${infra_dir}"'"'
}

trap cleanup EXIT

bash tools/runtime/payment_docker_prepare.sh >/dev/null

docker compose exec -T \
  -e DATABASE_URL="${database_url}" \
  -e INFRA_URL="${infra_url}" \
  -e OIDC_DISABLED=1 \
  -e PAYMENT_BOOTSTRAP_SCHEMA=0 \
  app sh -lc 'mkdir -p "'"${infra_dir}"'" \
    && php bin/console doctrine:database:create --if-not-exists --no-interaction \
    && php tools/runtime/payment_runtime_bootstrap.php \
    && php bin/console doctrine:fixtures:load --group=payment --no-interaction'

echo "Fixture dry-run completed against disposable Docker databases: ${db_name}"
