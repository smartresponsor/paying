# Paying Wave 21 — Application Surface Name-Form Guard

Wave 21 closes the first canonicalization pass over the application-facing surface that was intentionally left out of the earlier runtime rename waves.

Scope:

- forms under `src/Form`;
- repositories and repository contracts;
- messenger commands, events, messages, handlers, and consumers under `src/Message`.

This is a report-only wave. It does not rename runtime classes, does not change routes, does not change provider keys, does not change Doctrine tables, and does not retire files.

The new guard is available through:

```bash
composer report:application-surface-name-form
```

The rule is deliberately narrow: application-surface classes must keep the `Payment` prefix and the class suffix must identify the Symfony/application form (`Type`, `Repository`, `RepositoryInterface`, `Command`, `Event`, `Message`, `Handler`, or `Consumer`).
