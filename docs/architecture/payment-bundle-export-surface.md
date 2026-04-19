# Payment bundle export surface

## Purpose

This document defines the minimum dependency-oriented bundle/export surface for
the Paying component.

## Bundle

- `App\PayingBundle`

## Bundle extension and config tree

- `App\DependencyInjection\PayingExtension`
- `App\DependencyInjection\Configuration`
- config namespace: `paying`

## Early compile-phase parameters

These remain in `config/services.yaml` intentionally because Symfony resolves
package config for `framework`, `doctrine`, and `messenger` before later
extension-populated parameters would exist.

- `paying.messenger.dsn`
- `paying.storage.data_server_version`
- `paying.storage.infra_server_version`

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
