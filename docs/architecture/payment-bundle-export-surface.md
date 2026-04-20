# Payment bundle export surface

## Purpose

This document defines the minimum dependency-oriented bundle/export surface for
the Paying component.

## Bundle

- `App\Paying\PayingBundle`

## Bundle extension and config tree

- `App\Paying\DependencyInjection\PayingExtension`
- `App\Paying\DependencyInjection\Configuration`
- config namespace: `paying`

## Early compile-phase parameters

These remain in `config/services.yaml` intentionally because Symfony resolves
package config for `doctrine` and `messenger` before later
extension-populated parameters would exist.

- `paying.messenger.dsn`
- `paying.storage.data_server_version`
- `paying.storage.infra_server_version`

`framework.secret` is now read directly from `%env(APP_SECRET)%` and is treated
as framework bootstrap, not as part of the Paying outward config surface.

## Current outward config tree

```yaml
paying:
  storage:
    data_server_version: '16.0'
    infra_server_version: '3.45.1'
  messenger:
    dsn: '%env(resolve:RABBITMQ_DSN)%'
```

## Current package-config consumers

- `config/packages/payment_framework.yaml`
- `config/packages/payment_doctrine.yaml`
- `config/packages/payment_messenger.yaml`
- `config/packages/payment_messenger_consumer.yaml`

## Cleanup direction

Future cleanup waves should aim to:
- keep only truly early compile-phase parameters in `config/services.yaml`
- migrate the rest toward the bundle config tree and extension-owned surface
- preserve Symfony 8 / DoctrineBundle 3 compatibility

## Service layout

Simple interface-to-implementation aliases are imported from
`config/services/payment_aliases.yaml` so the root `config/services.yaml` can stay
focused on early parameters and explicit runtime wiring.
