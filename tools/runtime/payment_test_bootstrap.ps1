$ErrorActionPreference = 'Stop'
New-Item -ItemType Directory -Force -Path 'var' | Out-Null
Remove-Item -Force -ErrorAction SilentlyContinue 'var/payment.test.data.sqlite'
Remove-Item -Force -ErrorAction SilentlyContinue 'var/payment.test.infra.sqlite'
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --group=payment --no-interaction
