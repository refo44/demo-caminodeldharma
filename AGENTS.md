# Agent guidelines — Camino del Dharma

This repository is the **Camino del Dharma** static website (production today) and the planning
docs for a future WordPress / block-theme migration. It is **not** another WordPress project.
Do not copy names, URLs, slugs, CPTs, hosts, or deploy architecture from other repos.

## Inspect before modify

Read `README.md`, `docs/17-orden-implementacion.md` (current phase), `docs/adr/README.md`,
`docs/inventario-contenido-produccion-static.md`, `docs/contrato-migracion-static-wordpress.md`,
and `docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038) before changing architecture, WordPress
plans, or adding tests.

## Git (trunk-based — ADR 0043)

- **`main` is protected.** Never push directly to `main`. Always create a short-lived branch
  and open a PR.
- **Branch names:** [Conventional Branch](https://conventionalbranch.org/) —
  `feature/…`, `fix/…`, `chore/…`, `cursor/…` (for Cursor agent work), etc.
- **Commit messages:** [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
  in **English**.
- **PR labels:** at least one relevant GitHub label per PR; multiple when applicable.
- Guide: `docs/git-workflow.md`. Required merge checks: `php`, `css` (`test.yml`).

## Three times

### Historical

Older docs may mention classic PHP templates (`*.php`) or a previous WordPress on this domain
(`.htaccess` leftovers). Do not rewrite those as if they were never true.

### Current

Production is the **live static** site at `https://caminodeldharma.org` (real visitors). The
monorepo reorg (ADR 0014) is done: deployable HTML lives in `static/` and is production data,
not a disposable mockup (ADR 0001, ADR 0034). Hardcoded events/blog/gallery JSON are REAL
PRODUCTION CONTENT. Local Docker environment exists (WU-02, ADR 0023). Plugin
`camino-del-dharma-core` is scaffolded with the TDD quality kit (WU-03, ADR 0038): root
Composer, PHPUnit + wp-phpunit, PHPCS/WPCS, `tools/`, quality-only `test.yml`. The FSE theme
`camino-del-dharma` (v0.5.1) has the real views since WU-07 (ADR 0029): 18 block templates,
header/footer parts via PHP patterns, 14 dynamic blocks (events calendar with byte parity
against the published grid, current/past listing with compact archive cards, home featured note,
ADR 0037 bylines, native per-album galleries), the full static CSS ported to presets,
self-hosted fontFaces and the native lightbox. The plugin owns the domain models since WU-05:
CPT `event` + non-public `event_type`/`event_city`, `gallery_album` taxonomy, CPT
`blog_author` and ordered `authors` relation with publication guard, request-time event status in
`America/Bogota`, monthly calendar data and the generated `/eventos/ical/{slug}.ics` route
(200 current / 410 completed) — all test-protected (105 unit + 60 wp-phpunit tests as of WU-07,
with the theme active in the harness). Fase 3 durable state: `.audit/fase3-execution-state.md`.

### Future

Rest of Fase 3: **live static production → FSE block theme** (ADR 0029). No classic PHP theme
in between. The migration pipeline landed in WU-06 and WU-07 (plugin v0.4.0): pure extractors,
deterministic `migration/payload.json` (source VERSION 1.0.35; live parity verified
byte-for-byte, delta 0), WP-CLI `wp cdd-core migrate validate|plan|import|verify|convert` +
`seed` (dry-run by default, create-missing-only, production guard); content imported and
converted in the local env (staging order: import --apply → seed → convert --apply). WU-08A
landed the front-end behavior (plugin v0.5.0 / theme v0.3.0): share dialog, add-to-calendar
dialog sharing one payload with the generated `.ics`, native `core/audio` mantra players, and
the published share copy as editable `share_*` meta. WU-08B landed SEO, routing and
accessibility (plugin v0.6.0 / theme v0.4.0): first-party head + JSON-LD with no SEO suite,
`noindex,follow` on `/author`/album terms/tags/404, trimmed native `/wp-sitemap.xml`, the
redirect ledger ported to a versioned `wordpress/.htaccess` verified against real Apache, the
wp-admin «Eliminar huérfanos» tool (OWN-015) and the docs/19 pass. WU-09 landed the contact
form (plugin v0.7.0 / theme v0.5.0): Contact Form 7 6.1.7 approved and installed per
environment but **never vendored in Git** — the repo owns the form/mail/message definition it
provisions via `wp cdd-core contact provision`, gated on the ADR 0041 `/privacidad` copy
delta, rendered by a theme block that falls back to WhatsApp/email when CF7 is off. Real mail
delivery stays `Unverified` (no MTA in Docker). BUG-001 closed the Círculos `.ics` right
before WU-10 (plugin v0.7.1 / theme v0.5.1): the generated file carries **one VEVENT per
`event_calendar_dates` session** with its own UID (`slug-Ymd@host`) and its own all-day
exclusive end, an event without a schedule keeps the published range entry and UID, and —
since a Google/Outlook deep link carries a single entry — the dialog names the **next
session**, a date the file contains, and says the file holds them all. The published static
`.ics` still carries only the welcome session and is untouched. **WU-10** closed (2026-08-31);
next: Hostinger staging (OWN-005) and cutover prep — see `.audit/fase3-execution-state.md`.

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
- Privacy page is published at `/privacidad` (ADR 0039, provisional disclaimer). Contact Form 7 is
  wired since WU-09 (ADR 0041 / OWN-018); legal review does not block launch. The form-paragraph
  delta is applied **on the WordPress Page only** — static HTML stays untouched while the live form
  uses `action="#"`. Do not rewrite the rest of that notice, and keep the provisional disclaimer.
  Do not vendor CF7's code in Git, and do not add another antispam plugin without its own ADR.
  Real delivery to `caminodeldharma1@gmail.com` must be proven in Hostinger staging before release:
  `Pass (local)` is not enough.
- Owner audit backlog is **closed** for Fase 3 (`docs/backlog-decisiones-owner-migracion.md`,
  v1.21). Do not reopen A/B/C for authors, gallery, ICS, or pagination without a new owner
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
  Gutenberg meta UI for authors/events/SEO is later (ADR 0042): no unsynced classic metabox at
  cutover.
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
