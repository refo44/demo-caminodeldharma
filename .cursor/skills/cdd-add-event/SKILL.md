---
name: cdd-add-event
description: >-
  Adds one Camino del Dharma event to production WordPress at
  https://caminodeldharma.org using SSH and WP-CLI. Use only when the user
  explicitly asks to add, create, or publish a production event and supplies
  EVENT INPUT (title, slug, dates, poster). Do not use for research, code
  changes, local fixtures, or migration.
disable-model-invocation: true
---

# Add a production event

Execute [prompt.md](prompt.md) verbatim. Do not paraphrase it, skip a phase,
or rediscover the migration history.

The owner replaces the `EVENT INPUT` block at the end of that file in the
chat. Keep `PUBLISH_MODE: draft` unless they set `publish`.

Stop conditions in the prompt are mandatory. If a required fact is missing,
stop and ask. Do not invent it.
