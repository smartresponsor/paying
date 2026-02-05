# Payment (SmartResponsor)

Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

## Quick start
```bash
composer install
cp .env.example .env
php bin/console doctrine:migrations:migrate --no-interaction
symfony server:start -d
curl -X POST http://localhost:8000/payment/start -H 'Content-Type: application/json' -d '{"amount":"1.00","currency":"USD","provider":"internal"}'
```
