# CLAUDE.md

Project: **Comunidad Buddhista Camino del Dharma** — **live static production** → **FSE** (no classic PHP theme in between).

Independent of any other WordPress repository. Do not import foreign slugs, CPTs, hosts, or deploy
pipelines.

## Current state

- Production: static HTML on Hostinger (`https://caminodeldharma.org`) with real visitors. Not a disposable mockup.
- Hardcoded events (10 cards), blog posts (2), gallery JSON (35 + 3 albums) are **production content** (ADR 0034).
- Repo layout: **monorepo** (ADR 0014, since Fase 3 WU-01). Deployable static site lives in `static/`. `wordpress/` holds first-party plugin/theme trees: `camino-del-dharma-core` v0.4.0 owns the domain models (WU-05), the migration pipeline (WU-06) and the field-scoped content conversion `migrate convert` (WU-07) (CPT `event` + non-public `event_type`/`event_city`, `gallery_album`, CPT `blog_author` + `authors` relation with publication guard, request-time event status in `America/Bogota`, calendar data, generated `/eventos/ical/{slug}.ics`; TDD kit from WU-03); the FSE theme `camino-del-dharma` v0.2.0 has the **real views** since WU-07: 16 block templates, header/footer parts via PHP patterns, 11 dynamic blocks (events calendar with byte parity against the published grid, listing with compact past cards, home featured note, ADR 0037 bylines, native per-album galleries), full static CSS ported to presets, self-hosted fontFaces, native lightbox.
- Institutional copy and presentation: **published production wins** until cutover (OWN-007,
  ADR 0040). The legacy source folder was permanently removed; do not recreate it.
- Deploy: manual ZIP from `static/` (ADR 0015). WordPress: **Fase 3 started** (WU-00–WU-07 done; next is **WU-08A** then **WU-08B**). Migration pipeline: versioned `migration/payload.json` (VERSION 1.0.35; live parity verified, delta 0) + `wp cdd-core migrate validate|plan|import|verify|convert` and `seed` (dry-run by default, create-missing-only). Local env has the content imported **and converted** (dynamic home note, native album galleries, OWN-016 links). Durable state: `.audit/fase3-execution-state.md`.
- `/privacidad` is published (ADR 0039, provisional). Contact Form 7 is eligible at cutover
  (ADR 0041 / OWN-018); legal review does not block launch. WordPress updates only the form
  paragraphs of that notice. Do not change the live static notice while the static form does not
  submit.

## Must-read before WordPress or migration work

1. `docs/inventario-contenido-produccion-static.md` and `docs/conteos-reconciliacion-migracion.md` (ADR 0034)
2. `docs/contrato-migracion-static-wordpress.md` (ADR 0032)
3. `docs/matriz-migracion-static-wordpress.md`, `docs/redirect-ledger.md`
4. `docs/cutover-checklist-wordpress.md`
5. `docs/adr/README.md` — 0001, 0008, 0012, 0013, 0024, 0029, **0032–0041**
6. `docs/backlog-decisiones-owner-migracion.md` (Fase 3 cerrada, v1.21; `POST-*` = fases posteriores, no bloquear el corte)
7. `docs/11-arbol-urls-final.md`, `docs/12-theme-file-structure.md`, `docs/17-orden-implementacion.md`
8. `docs/guia-pruebas-plugin-theme-fse.md` — TDD, wp-phpunit, FSE, Sonar (ADR 0038)

## Hard rules

- Inspect before modify. Inventory before WordPress modeling.
- Live static content is production data (ADR 0034). Never discard hardcoded content without classification.
- Prefer programmatic extraction over manual retyping when deterministic.
- Migration counts must reconcile (`docs/conteos-reconciliacion-migracion.md`).
- Preserve URLs or record KEEP/301 in `docs/redirect-ledger.md`. Never silent URL changes.
- Template ≠ Page. FSE templates are presentation, not content storage. Patterns ≠ editorial collections.
- Importer ≠ fixtures. No generic destructive DB reset in production.
- Do not deploy while auditing. No cutover with broken navigation.
- Test incoming HTTP routes, not only `get_permalink()`. No trailing slash (ADR 0008).
- Preserve JS/DOM contracts, accessibility (`docs/19`), media relationships.
- Compare copy, content, and styles to published production (`https://caminodeldharma.org`), not only the local repo (OWN-007). Exception: WordPress `/privacidad` form paragraphs per ADR 0041 / OWN-018.
- Target: **static production → FSE** (ADR 0029). No classic PHP theme as a bridge.
- Update durable docs after verified implementation.
- New plugin/theme domain: TDD from the first line of FSE. Do not mock WordPress. Sonar
  scope is plugin + theme only (not the static site). Guide:
  `docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038).

## Docs-only vs implementation

If the task is documentation/architecture: change `docs/`, ADR, README, agent guidelines only.
Do not modify HTML, CSS, JS, PHP, workflows, or deployment scripts.

See also `AGENTS.md` and `.cursor/rules/`.
