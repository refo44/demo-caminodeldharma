# CLAUDE.md

Project: **Comunidad Buddhista Camino del Dharma** — static mockup → **FSE** (no classic PHP theme in between).

Independent of any other WordPress repository. Do not import foreign slugs, CPTs, hosts, or deploy
pipelines.

## Current state

- Production: static HTML on Hostinger (`https://caminodeldharma.org`).
- Repo layout: site at **root** (`index.html`, section folders, `assets/`). `static/` and
  `wordpress/` do **not** exist yet (ADR 0014 applies when Fase 3 starts).
- Canonical copy: `content-source/` (never paraphrase). Then `docs/`. Generated HTML is presentation,
  not a second editorial source.
- Deploy today: manual ZIP (ADR 0015). No GitHub Actions deploy in this repo.
- WordPress implementation: **not started**.

## Must-read before WordPress or migration work

1. `docs/contrato-migracion-static-wordpress.md` (ADR 0032)
2. `docs/matriz-migracion-static-wordpress.md`
3. `docs/cutover-checklist-wordpress.md`
4. `docs/adr/README.md` — especially 0001, 0008, 0012, 0013, 0024, 0029, 0032, 0033
5. `docs/11-arbol-urls-final.md`, `docs/12-theme-file-structure.md`, `docs/17-orden-implementacion.md`

## Hard rules

- Inspect before modify.
- Static prototype is the visual/behavioral contract unless an ADR supersedes it.
- Template ≠ Page. Theme activation does not create content.
- Do not deploy while auditing. Do not touch production, databases, or importers unless asked.
- Never overwrite production content implicitly. create-missing-only. Preserve wp-admin edits.
- Fixtures ≠ editorial content. No generic destructive DB reset in production.
- No static deploy over a WordPress document root after cutover.
- Test incoming HTTP routes, not only generated permalinks. No trailing slash on public URLs (ADR 0008).
- When migrating markup, re-check JS selectors, DOM, data attributes, ARIA, events.
- Preserve accessibility behaviour (`docs/19-accesibilidad-estandares`).
- No on-site search. No invented `/privacidad` copy. No `sangha` CPT unless scope is reopened.
- Target architecture: **static mockup → FSE** (ADR 0029). Do not build a classic PHP theme as a bridge. Domain lives in `camino-del-dharma-core`.
- Update durable docs after verified implementation.

## Docs-only vs implementation

If the task is documentation/architecture: change `docs/`, ADR, README, agent guidelines only.
Do not modify HTML, CSS, JS, PHP, workflows, or deployment scripts.

See also `AGENTS.md` and `.cursor/rules/`.
