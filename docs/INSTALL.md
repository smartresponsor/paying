# INSTALL

## Requirements

- PHP 8.4
- Composer
- PostgreSQL (user data)
- SQLite (application/infrastructure data file)

## 1) Install

```bash
composer install
cp .env.example .env
```

## 2) Environment

Minimal required env values:

```dotenv
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=devsecret
DATABASE_URL="pgsql://app:app@127.0.0.1:5432/payment"
INFRA_URL="sqlite:///%kernel.project_dir%/var/payment.app.sqlite"
STRIPE_WEBHOOK_SECRET=
PAYMENT_WEBHOOK_ALLOW_UNKNOWN=0
```

Notes:

- `DATABASE_URL` is the Doctrine `data` connection (user/payment entities).
- `INFRA_URL` is the Doctrine `infra` connection (application infrastructure state).

## 3) Bootstrap

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

## 4) Local run

```bash
symfony server:start -d
# or
php -S 127.0.0.1:8000 -t public
```


## Composer dependency direction

The component is being hardened toward a dependency-oriented install surface.

Current outward bundle/config surface:

- Bundle: `App\PayingBundle`
- Bundle alias: `paying`
- Config tree:
  - `paying.storage.data_server_version`
  - `paying.storage.infra_server_version`
  - `paying.messenger.dsn`

Current early compile-phase parameters:

- `paying.messenger.dsn`
- `paying.storage.data_server_version`
- `paying.storage.infra_server_version`

Framework bootstrap secret is read directly from `%env(APP_SECRET)%` and is not
part of the Paying outward config namespace.


Alias-only DI mappings are imported from `config/services/payment_aliases.yaml`.
