# Payment documentation surface

This repository is an Antora-compatible documentation producer.

Canonical narrative entry set:

- [INSTALL](INSTALL.md)
- [ARCHITECTURE](ARCHITECTURE.md)
- [OPERATIONS](OPERATIONS.md)
- [API](API.md)
- [LIMITS](LIMITS.md)
- [PROOF_PACK](PROOF_PACK.md)
- [Runtime baseline](release/runtime-baseline.md)

Generated surfaces stay separate:

- OpenAPI: `docs/api/openapi.yaml`
- Doctum producer config: `doctum.php` (checked-in generated output is not included in the current slice)
