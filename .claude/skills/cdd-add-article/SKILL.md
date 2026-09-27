---
name: cdd-add-article
description: >-
  Adds one Camino del Dharma blog article to production WordPress at
  https://caminodeldharma.org using SSH and WP-CLI. Use only when the user
  explicitly asks to add, create, schedule, or publish a production article
  and supplies ARTICLE INPUT (title, slug, body, editorial authors, image).
  Do not use for research, code changes, local fixtures, or migration.
---

# Add a production blog article

Execute [prompt.md](prompt.md) verbatim. Do not paraphrase it, skip a phase,
or rediscover the migration history.

The owner replaces the `ARTICLE INPUT` block at the end of that file in the
chat. Keep `PUBLISH_MODE: draft` unless they set `publish`.

Stop conditions in the prompt are mandatory. If a required fact is missing,
stop and ask. Do not invent it. Do not create a new `blog_author` unless a
separate author-profile workflow was authorized.

Mark scannable facts in the body. Bold is
`<strong style="font-weight:600">`. Italic is `<em>`. A bare `<strong>`
does not look bold on this site.
