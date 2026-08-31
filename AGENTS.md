# Agent guidelines — Camino del Dharma

This repository is the **Camino del Dharma** static website (production today) and the planning
docs for a future WordPress / block-theme migration. It is **not** another WordPress project.
Do not copy names, URLs, slugs, CPTs, hosts, or deploy architecture from other repos.

## Inspect before modify

Read `README.md`, `docs/17-orden-implementacion.md` (current phase), `docs/adr/README.md`,
`docs/inventario-contenido-produccion-static.md`, `docs/contrato-migracion-static-wordpress.md`,
and `docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038) before changing architecture, WordPress
plans, or adding tests.

## Three times

| | |
| --- | --- |
| **Historical** | Older docs may mention classic PHP templates (`*.php`) or a previous WordPress on this domain (`.htaccess` leftovers). Do not rewrite those as if they were never true. |
| **Current** | Production is the **live static** site at `https://caminodeldharma.org` (real visitors). The monorepo reorg (ADR 0014) is done: deployable HTML lives in `static/` and is production data, not a disposable mockup (ADR 0001, ADR 0034). Hardcoded events/blog/gallery JSON are REAL PRODUCTION CONTENT. Local Docker environment exists (WU-02, ADR 0023). Plugin `camino-del-dharma-core` is scaffolded with the TDD quality kit (WU-03, ADR 0038): root Composer, PHPUnit + wp-phpunit, PHPCS/WPCS, `tools/`, quality-only `test.yml`. The FSE theme tree is still README-only. Fase 3 durable state: `.audit/fase3-execution-state.md`. |
| **Future** | Rest of Fase 3: **live static production → FSE block theme** (ADR 0029). No classic PHP theme in between. Next work unit: WU-04 FSE theme scaffold and visual token baseline. |

## Canonical content

Until cutover, **live static HTML/JSON is the production content source** for events, posts, gallery,
and institutional copy (ADR 0034, ADR 0040, OWN-007). After cutover, WordPress owns editorial
content.

Do not recreate or restore the retired legacy source folder. Do not discard hardcoded HTML as dummy.
Prefer deterministic extraction over retyping. Counts must reconcile. Before cutover, compare copy,
content, and styles to the **published** site (`https://caminodeldharma.org`), not only the local
repo.

## Static site is production + visual contract

The static HTML/CSS/JS is both **published content** and the visual/behavioral contract (ADR 0001,
ADR 0002), unless an ADR records an exception (e.g. ADR 0021 gallery lightbox).

## WordPress migration rules (docs only until Fase 3 is explicitly started)

- A **template does not create a Page.** `templates/page-comunidad.html` ≠ `/comunidad`.
- Theme deploy/activation ≠ migration complete (ADR 0032). Five deliverables: CONTENT, PRESENTATION,
  ROUTING, BEHAVIOR, OPERATIONS.
- Do not assume a generic `page.html` covers special layouts or page-specific JS.
- Check JS selectors, DOM, `data-*`, ARIA, and events when markup changes. Enqueuing `main.js` is
  not parity.
- Test **incoming routes** (HTTP), not only `get_permalink()`.
- Public URLs have **no trailing slash** (ADR 0008).
- No search feature (`docs/04-mapa-pantallas.md`).
- Distinguish **fixtures** from real content (ADR 0033). Never mark live events/posts/gallery as fixtures.
- Never discard hardcoded content without classification (ADR 0034). Inventory before modeling.
- Prefer deterministic extraction over retyping. Counts must reconcile.
- Preserve URLs or record KEEP/301. **NO CUTOVER WITH BROKEN NAVIGATION.**
- FSE templates and patterns are presentation, not editorial storage.
- Preserve wp-admin edits; default importer behaviour is create-missing-only.
- No destructive DB reset in production. No implicit overwrite of production content.
- `STATIC DEPLOY ≠ WORDPRESS CODE DEPLOY ≠ WORDPRESS CONTENT`. Never static-ZIP over a WordPress
  document root after cutover.
- Flush rewrite rules on activation/upgrade only, never on every request.
- Future theme is **FSE / block theme built directly from the static mockup** (ADR 0029). Do **not**
  scaffold a classic PHP theme (`front-page.php`, `page-*.php`) as a bridge.
- CPT `sangha` is out of initial Fase 3 scope unless the owner reopens it (ADR 0024).
- Privacy page is published at `/privacidad` (ADR 0039). Copy is provisional until legal review; do not rewrite it. Contact Form 7 stays gated until the notice is updated to describe a server-side form.
- Owner audit backlog is **closed** for Fase 3 (`docs/backlog-decisiones-owner-migracion.md`,
  v1.20). Do not reopen A/B/C for authors, gallery, ICS, or pagination without a new owner
  decision. Later-phase rows (`POST-*`) are i18n/English after cutover: do not implement
  them in Fase 3.
- Gallery albums: taxonomy `/galeria/{slug}`, hub `/galeria` KEEP, term archives noindex until
  volume (ADR 0036). No numbered pagination at cutover (OWN-011).
- Every event has a public single (ADR 0035). Past: no signup, no add-to-calendar, no `.ics`
  (OWN-012). Status follows end date in `America/Bogota` (OWN-013).
- `.ics` is generated by the plugin, not Media Library (OWN-009); `noindex` (OWN-014). mp3 → Media
  Library. wp-admin «Eliminar huérfanos» is `.ics` only (OWN-015).
- Blog byline is CPT `blog_author` at `/author/{slug}` (ADR 0037). `query_var` must not be
  `author`. WP user archives 404. Do not copy another repo’s author plugin; follow ADR 0037.
- `/comunidad` stays; in WP only, add links to author profiles (OWN-016). Do not edit static HTML
  for that now.

## Testing (ADR 0038)

WordPress FSE (plugin **and** theme) is TDD from the first production line. wp-phpunit is
the default for in-process WordPress contracts. The FSE theme does not own domain. Do not
mock WordPress APIs. Do not write `theme.json`, templates, or plugin PHP before a failing
test. `wordpress/` placeholders are not Fase 3. Sonar Automatic Analysis is ON and
**WordPress-only**: never add the static site to `.sonarcloud.properties`.
Guide: `docs/guia-pruebas-plugin-theme-fse.md`.

## Do not deploy while auditing or documenting

This includes Hostinger File Manager, FTP, and production. Do not run content migrations, create
database content, commit, or push unless the owner asked.

## After verified implementation

Update durable docs (matriz, ledger `migracion-static-wordpress.md`, ADR if the decision changed).
Do not silently resolve doc vs code conflicts.

## Code language

Code, comments, and Git messages: English. Editorial copy and most `docs/`: Spanish (Colombia).
