#!/usr/bin/env bash
set -euo pipefail

mkdir -p var
rm -f var/payment.test.data.sqlite var/payment.test.infra.sqlite
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --group=payment --no-interaction
