# Paying relationship and lifecycle hardening

This component participates in the platform-wide entity relationship hardening pass.

Implemented in this patch:

- lifecycle transition policy for the component-owned aggregate lifecycle;
- boundary-reference canon is available through `Objecting\ValueObject\ObjectReference`;
- cross-component references should remain reference values, not Doctrine foreign keys.

Explicitly deferred:

- locale-specific `*EnGb*` translation normalization;
- Attachment/Attaching integration and business document/media link decisions.

Use this pass as a guardrail before adding new ORM relations: if the target
aggregate is owned by another component, store an ObjectReference-style boundary
reference instead of a Doctrine association.
