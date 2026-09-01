# CLAUDE.md

Project: **Comunidad Buddhista Camino del Dharma** — **live static production** → **FSE** (no classic PHP theme in between).

Independent of any other WordPress repository. Do not import foreign slugs, CPTs, hosts, or deploy
pipelines.

## Current state

- Production: static HTML on Hostinger (`https://caminodeldharma.org`) with real visitors. Not a disposable mockup.
- Hardcoded events (10 cards), blog posts (2), gallery JSON (35 + 3 albums) are **production content** (ADR 0034).
- Repo layout: **monorepo** (ADR 0014, since Fase 3 WU-01). Deployable static site lives in `static/`. `wordpress/` holds first-party plugin/theme trees plus the deployable `wordpress/.htaccess` (WU-08B): `camino-del-dharma-core` v0.7.1 owns the domain models (WU-05), the migration pipeline (WU-06), the field-scoped content conversion `migrate convert` (WU-07/WU-08A), the share/calendar data of WU-08A and the **first-party SEO** of WU-08B (CPT `event` + non-public `event_type`/`event_city`, `gallery_album`, CPT `blog_author` + `authors` relation with publication guard, request-time event status in `America/Bogota`, calendar data, `cdd_core_event_calendar_payload()` as the single source of the calendar dialog **and** the generated `/eventos/ical/{slug}.ics`, which since BUG-001 carries **one VEVENT per `event_calendar_dates` session** (own UID `slug-Ymd@host`; range fallback when there is no schedule), editable `share_*` and `seo_*` meta, request-time head + JSON-LD, `noindex,follow` policies, trimmed native sitemap, wp-admin «Eliminar huérfanos», and the **Contact Form 7 definition** of WU-09 — form/mail/messages templates, `wp cdd-core contact provision`, gated on the `/privacidad` copy delta; TDD kit from WU-03); the FSE theme `camino-del-dharma` v0.5.1 has the **real views** since WU-07, the **ported behavior** since WU-08A and the **printed head** since WU-08B: 18 block templates, header/footer parts via PHP patterns, 14 dynamic blocks (events calendar with byte parity against the published grid, listing with compact past cards, home featured note, ADR 0037 bylines, native per-album galleries, share and add-to-calendar triggers — on a course the calendar trigger deep-links the **next session** and says the `.ics` holds them all (BUG-001) — the contact form with its CF7-off fallback), full static CSS ported to presets, self-hosted fontFaces, native lightbox, share/calendar dialogs enqueued per view.
- Institutional copy and presentation: **published production wins** until cutover (OWN-007,
  ADR 0040). The legacy source folder was permanently removed; do not recreate it.
- Deploy: manual ZIP from `static/` (ADR 0015). WordPress: **Fase 3** (WU-00–**WU-10** and **BUG-001** done). **OWN-020 / D-08** is decided: author singles stay indexable and reuse published short copy + photos; **implementation pending** ([issue #5](https://github.com/refo44/demo-caminodeldharma/issues/5)). Next after that and other pre-staging work: Hostinger staging per OWN-005 and `.audit/fase3-execution-state.md`. Migration pipeline: versioned `migration/payload.json` (VERSION 1.0.35; live parity verified, delta 0) + `wp cdd-core migrate validate|plan|import|verify|convert` and `seed` (dry-run by default, create-missing-only; `convert` takes an optional `--payload` to seed the share and SEO copy add-only; the payload carries a per-object `seo` object and an uncounted `site` section). Local env has the content imported **and converted** (dynamic home note, native album galleries, OWN-016 links, native mantra audio, share and SEO meta). Durable state: `.audit/fase3-execution-state.md`.
- `/privacidad` is published (ADR 0039, provisional). Contact Form 7 (v6.1.7 locally) is wired
  since WU-09 and the ADR 0041 copy delta is applied **on the WordPress Page only** — the live
  static notice and its `action="#"` form stay untouched. CF7's code is never vendored in Git;
  record the installed version per environment in `docs/operations/third-party-plugins.md`.
  `wp cdd-core contact provision` refuses until that copy delta is in place. **Real mail delivery
  to `caminodeldharma1@gmail.com` is still `Unverified`** — `wp_mail()` fails locally (no MTA);
  prove it in Hostinger staging before release. If it fails there, cut over with CF7 disabled plus
  WhatsApp/email (operational fallback, not a legal gate).

## Must-read before WordPress or migration work

1. `docs/inventario-contenido-produccion-static.md` and `docs/conteos-reconciliacion-migracion.md` (ADR 0034)
2. `docs/contrato-migracion-static-wordpress.md` (ADR 0032)
3. `docs/matriz-migracion-static-wordpress.md`, `docs/redirect-ledger.md`
4. `docs/cutover-checklist-wordpress.md`
5. `docs/adr/README.md` — 0001, 0008, 0012, 0013, 0024, 0029, **0032–0042**
6. `docs/backlog-decisiones-owner-migracion.md` (Fase 3 cerrada, v1.26; OWN-020 / D-08 decidido, implementación pendiente issue #5; `POST-*` = fases posteriores, no bloquear el corte)
7. `docs/11-arbol-urls-final.md`, `docs/12-theme-file-structure.md`, `docs/17-orden-implementacion.md`
8. `docs/guia-pruebas-plugin-theme-fse.md` — TDD, wp-phpunit, FSE, Sonar (ADR 0038)
9. `docs/git-workflow.md` — trunk-based, Conventional Branch/Commits (ADR 0043)

## Hard rules

- **Git:** trunk-based on protected `main` (ADR 0043). Always branch first
  ([Conventional Branch](https://conventionalbranch.org/)); commits
  [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) in English; merge
  only via PR with green `php`/`css` and **at least one relevant PR label**. See
  `docs/git-workflow.md` and `.cursor/rules/git-workflow.mdc`.
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
