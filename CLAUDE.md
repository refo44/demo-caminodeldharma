# CLAUDE.md

Project: **Comunidad Buddhista Camino del Dharma** — **live static production** → **FSE** (no classic PHP theme in between).

Independent of any other WordPress repository. Do not import foreign slugs, CPTs, hosts, or deploy
pipelines.

## Current state

- Production: static HTML on Hostinger (`https://caminodeldharma.org`) with real visitors. Not a disposable mockup.
- Hardcoded events (10 cards), blog posts (2), gallery JSON (35 + 3 albums) are **production content** (ADR 0034).
- Repo layout: site at **root**. `static/` does **not** exist. `wordpress/` holds empty plugin/theme trees (README only) for Sonar; no FSE implementation yet.
- Institutional copy: **live HTML wins** (OWN-007). `content-source/` is reference; do not overwrite production from the doc.
- Deploy: manual ZIP (ADR 0015). WordPress: **not started**.

## Must-read before WordPress or migration work

1. `docs/inventario-contenido-produccion-static.md` and `docs/conteos-reconciliacion-migracion.md` (ADR 0034)
2. `docs/contrato-migracion-static-wordpress.md` (ADR 0032)
3. `docs/matriz-migracion-static-wordpress.md`, `docs/redirect-ledger.md`
4. `docs/cutover-checklist-wordpress.md`
5. `docs/adr/README.md` — 0001, 0008, 0012, 0013, 0024, 0029, **0032–0038**
6. `docs/backlog-decisiones-owner-migracion.md` (Fase 3 cerrada, 0 abiertas; `POST-*` = fases posteriores, no bloquear el corte)
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
- Compare copy, content, and styles to published production (`https://caminodeldharma.org`), not only the local repo (OWN-007).
- Target: **static production → FSE** (ADR 0029). No classic PHP theme as a bridge.
- Update durable docs after verified implementation.
- New plugin/theme domain: TDD from the first line of FSE. Do not mock WordPress. Sonar
  scope is plugin + theme only (not the static site). Guide:
  `docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038).

## Docs-only vs implementation

If the task is documentation/architecture: change `docs/`, ADR, README, agent guidelines only.
Do not modify HTML, CSS, JS, PHP, workflows, or deployment scripts.

See also `AGENTS.md` and `.cursor/rules/`.
