# FABLE 5 — MASTER EXECUTION PROMPT v2

## Camino del Dharma · Fase 3 · Static production → WordPress FSE

**Prompt version:** 2.6
**Status:** CURRENT — use this prompt for execution
**Date:** 2026-08-31
**Supersedes for execution:** `FABLE5-Fase3-WordPress-Master-Prompt-v1.md`

The v1 prompt remains a historical artifact. Do not execute it, copy its classic-theme architecture,
or rewrite it as if it had never been valid. This v2 prompt incorporates ADR 0029 and ADR 0032–0041,
plus the closed Fase 3 owner-decision backlog (v1.21). `POST-*` later-phase i18n rows must not be
implemented at cutover. Version 2.3 incorporated the permanent removal of the legacy source folder
under OWN-017 and ADR 0040. Version 2.4 incorporates ADR 0041 / OWN-018: Contact Form 7 is eligible
at cutover without waiting for legal review; the published `/privacidad` disclaimer is sufficient
for launch; WordPress updates only the form paragraphs of that notice. Version 2.5 splits WU-08
into 08A (front-end behavior; Opus, no full prompt) and 08B (SEO, redirects, OWN-015, a11y; attach
only §9.5 and §10). Version 2.6 schedules BUG-001 (Círculos `.ics` = every session) as its own
session **immediately before WU-10**, after WU-09. Do not paste this entire file to start those
sessions.

---

## 1. Role and repository

Act as a senior WordPress engineer, software engineer, migration engineer, and technical lead. Work
inside:

```text
/Users/rafaelfigueredo/Documents/demo-caminodeldharma
```

Build production-grade, maintainable code. Apply WordPress Coding Standards, security practices,
KISS, YAGNI, SOLID, Clean Code, and incremental refactoring pragmatically. Do not introduce
abstractions or dependencies without a current requirement.

Code, code comments, and Git commit messages are written in English. Editorial content and most
project documentation remain in Spanish. Preserve published Spanish copy exactly unless an approved
difference is recorded.

---

## 2. Mission

Start and execute **Fase 3: WordPress** by migrating the live static production site directly to:

1. A WordPress **block theme / Full Site Editing theme** named `camino-del-dharma`.
2. A first-party domain plugin named `camino-del-dharma-core`.
3. A reproducible local WordPress environment using Docker.
4. A deterministic extraction and idempotent WP-CLI import pipeline for real production content.
5. WordPress Pages, posts, events, blog-author profiles, gallery albums, media, settings, routes, and
   behavior that satisfy the migration contract.
6. Durable execution state and QA evidence that another session can resume without chat history.
7. A manual, bounded staging deployment runbook and a build ready for staging validation.

The migration changes the content engine, not the design. The current static site remains the public
production site until an independently authorized cutover.

```text
STATIC PRODUCTION → WORDPRESS FSE
```

There is no classic PHP theme step between them.

---

## 3. Authority granted by this prompt

This prompt authorizes:

- repository inspection;
- documentation and implementation changes required for Fase 3;
- creation of a dedicated local feature branch from an owner-approved clean baseline;
- creation of one local annotated pre-reorganization rollback tag;
- the root-to-`static/` monorepo reorganization;
- local Docker operations;
- creation of the theme, plugin, migration tools, tests, and durable execution artifacts;
- local fixture import and reviewed real-content payload import;
- small, coherent local Git commits after relevant checks pass.

This prompt does **not** authorize:

- pushing commits or tags;
- creating or modifying a Hostinger staging site;
- deploying to staging or production;
- running an importer against production;
- changing DNS or the production document root;
- production cutover;
- destructive database resets outside disposable local Docker volumes;
- activating HSTS.

Contact Form 7 is in scope for WU-09 and for an authorized cutover (ADR 0041). This prompt still
does not itself perform that cutover.

Prepare external actions and their runbooks, but request explicit current-session authorization when
an external write becomes the next action. Never request credentials in chat; ask the owner to
configure them through the appropriate secure mechanism.

---

## 4. Source-of-truth rules

Read the actual repository before acting. Summaries in this prompt do not replace canonical files.

### 4.1 Governance and architecture precedence

For architecture, scope, routing, security, deployment, and migration behavior:

1. Current system/user instructions and repository rules.
2. `CLAUDE.md`, `AGENTS.md`, and `.cursor/rules/`.
3. Accepted ADRs, with later accepted ADRs superseding earlier conflicting decisions.
4. Current migration contract and structured project documentation.
5. Current implementation evidence.
6. This prompt.

Do not modify an accepted ADR to change its meaning. A genuinely new architectural, URL, dependency,
security, or deployment decision requires a new ADR before implementation.

### 4.2 Content and presentation precedence is type-specific

There is no single source hierarchy for every content type:

- **Published production:** `https://caminodeldharma.org` is authoritative for current institutional
  copy, content, page structure, section order, hierarchy, and styles until cutover (OWN-007).
- **Extraction input:** use the latest repository `VERSION` and exact commit/tag (OWN-006). Compare
  it against published production before import. Record every repo/Hostinger delta and reconcile
  conflicting fields explicitly; never assume parity or silently choose.
- **Events, blog posts, gallery JSON, dates, cards, and media relationships:** static HTML/JSON is
  real production content under ADR 0034. Hardcoded does not mean fixture.
- **Retired legacy material:** the former source folder was permanently removed under OWN-017 and
  ADR 0040. Do not recreate it or use external legacy copies as migration input.
- **Presentation and behavior:** published static HTML/CSS/JS is the visual and behavioral contract
  unless an accepted ADR records a replacement.
- **URLs:** `sitemap.xml`, ADR 0008, the redirect ledger, and incoming HTTP behavior.
- **After cutover:** WordPress owns editorial content; Git owns theme/plugin code.

If published production and the latest repository diverge, record the affected object, field, route,
and proposed resolution in the execution state and migration ledger before applying the payload.

### 4.3 Mandatory reading

Before implementation, read:

- `.cursor/rules/`, `CLAUDE.md`, `AGENTS.md`;
- `docs/17-orden-implementacion.md`;
- `docs/adr/README.md`;
- ADR 0001, 0002, 0003, 0008, 0013–0016, 0019–0041;
- `docs/contrato-migracion-static-wordpress.md`;
- `docs/inventario-contenido-produccion-static.md`;
- `docs/conteos-reconciliacion-migracion.md`;
- `docs/matriz-migracion-static-wordpress.md`;
- `docs/redirect-ledger.md`;
- `docs/backlog-decisiones-owner-migracion.md`;
- `docs/playbook-migracion-static-wordpress.md`;
- `docs/cutover-checklist-wordpress.md`;
- `docs/03-wordpress-content-model.md`;
- `docs/11-arbol-urls-final.md`;
- `docs/12-theme-file-structure.md`;
- `docs/13-static-file-structure.md`;
- `docs/15-assets-strategy.md`;
- `docs/19-accesibilidad-estandares.md`;
- `docs/20-layout-principles.md`;
- `docs/docker-wordpress-playbook.md`.

Check the ADR index for decisions newer than ADR 0041 and read any that affect this scope.

---

## 5. Binding architecture

### 5.1 Monorepo

The first implementation step of Fase 3 is ADR 0014's reorganization:

```text
demo-caminodeldharma/
├── static/                         # live static production during transition
├── wordpress/
│   └── wp-content/
│       ├── themes/
│       │   └── camino-del-dharma/
│       └── plugins/
│           └── camino-del-dharma-core/
├── docs/
├── scripts/
└── project files
```

Move only the deployable static-site surface to `static/`. Keep repository documentation, source
material, scripts, tooling, changelog, and project metadata at the root. Update all affected tooling
and manual deployment instructions. Preserve the static site's ability to be packaged and deployed
independently, but do not deploy it.

Before moving files, record the exact pre-reorganization commit as the rollback reference. Preserve
renames in one reviewable work unit and do not mix unrelated implementation into that commit. Work
from a dedicated feature branch and create a local annotated pre-reorganization tag before the move.

### 5.2 Block theme, never classic theme

Build `camino-del-dharma` directly as an FSE block theme:

- `theme.json` is the source of truth for design tokens and Global Styles.
- `templates/*.html` define block templates.
- `parts/*.html` define header, footer, and other true template parts.
- `patterns/*.php` may define reusable block structures, but never store real editorial collections.
- `functions.php` is a small bootstrap for setup and enqueueing.
- one complementary `assets/css/main.css` handles layout/components that Global Styles cannot express.
- JavaScript is minimal and loaded only where behavior requires it.

Do not create view templates such as:

```text
front-page.php
page-comunidad.php
page-practica.php
archive-event.php
single-event.php
```

PHP remains valid for `functions.php`, focused includes, patterns, server-side rendering, and plugin
code. The prohibition is against a classic-theme view layer.

The initial `theme.json` must reproduce the static design tokens exactly. Save that initial version
as the visual baseline before any later Global Styles changes.

### 5.3 Plugin/theme boundary

`camino-del-dharma-core` owns:

- CPT `event`;
- CPT `blog_author`, its `authors` post relationship, assignment UI, and publication guards;
- `event_type` and `event_city`;
- `gallery_album` and its `/galeria/{slug}` routing;
- event metadata and validation;
- event status calculation in `America/Bogota`, current/completed visibility, and signup/calendar rules;
- generated `.ics` responses, retirement/410 behavior, and the scoped orphan-`.ics` cleanup tool;
- author routing with `query_var=blog_author`, author archive policy, and suppression of native WP-user
  author archives;
- album/tag/author indexation inputs;
- domain queries and visibility rules;
- editorial capabilities or role adjustments that are actually required;
- WP-CLI extraction/import support;
- migration source keys and hashes;
- domain-level routing, metadata, and structured-data inputs;
- activation/upgrade rewrite handling.

The theme owns presentation:

- templates, parts, patterns, and `theme.json`;
- component CSS and frontend behavior;
- semantic rendering;
- presentation of data exposed by WordPress and the plugin.

The theme must never register CPTs, taxonomies, roles, or domain fields. If the plugin is inactive,
the theme must avoid fatal errors and degrade safely without duplicating domain logic.

### 5.4 Native-first dependency policy

Use, in this order:

1. WordPress core APIs and Gutenberg blocks.
2. First-party code in `camino-del-dharma-core`.
3. A third-party plugin only when an accepted ADR explicitly approves it.

Contact Form 7 is the only third-party plugin currently approved. Do not add ACF, Elementor, Divi,
Yoast, RankMath, optimization suites, analytics plugins, calendar plugins, or lightbox plugins.

---

## 6. Five independent migration deliverables

Do not call the migration complete because the theme activates or looks correct.

1. **CONTENT:** actual WordPress Pages, posts, events, blog-author profiles, metadata, terms, media,
   and settings.
2. **PRESENTATION:** FSE templates, parts, patterns, `theme.json`, CSS, responsive layout, and
   accessibility structure.
3. **ROUTING:** incoming canonical URLs, CPT archive/singles, blog routes, redirects, 404, canonical
   behavior, and sitemap/indexing behavior.
4. **BEHAVIOR:** navigation, event calendar, share/calendar dialogs, audio, downloads, gallery
   replacement, and Contact Form 7 (ADR 0041).
5. **OPERATIONS:** environments, importer, evidence, backups/rollback plan, manual deployment scope,
   freeze/delta procedure, and cutover readiness.

Every row in `docs/matriz-migracion-static-wordpress.md` must eventually cover all five relevant
dimensions. A block template does not create a WordPress Page.

---

## 7. Current baseline and required defaults

Verify these values against the current commit before relying on them:

- 16 public sitemap URLs plus the non-public 404 template.
- 10 institutional/secondary Page objects, allowing the documented front-page counting variation.
- 10 real `event` entities: 1 current and 9 historical.
- 3 existing event single URLs plus 7 listing-only cards in the static source. The WordPress target is
  10 public event singles under ADR 0035.
- 2 real blog posts.
- 3 gallery albums.
- 35 publicly referenced gallery images.
- 36 original files in the gallery directory: 35 gallery images plus `galeria-04.jpg`, which is real
  media used by `/practica` and is not a gallery item.
- 10 event posters.
- 2 audio files.
- 2 `.ics` files on disk (Encuentro RETIRE; Círculos generated while current — OWN-009 / OWN-012).
- 1 historical recitation PDF path, now retired from the website by OWN-002.
- 0 production fixtures.

Owner backlog is **closed** for Fase 3 (`docs/backlog-decisiones-owner-migracion.md` v1.21).
Use those decisions; do not treat the rows below as still open. Later-phase `POST-*` rows
(i18n/English) stay out of this cutover: translation-ready PHP strings only (ADR 0027);
no multilingual plugin, no `/es` or `/en` prefix, no active language switcher. OWN-018 (ADR 0041)
makes Contact Form 7 eligible at cutover without legal review.

- Per OWN-001, import `galeria-04.jpg` exactly once as Media Library content for `/practica`; never
  add it to `/galeria`.
- Per OWN-002, retire `assets/documents/recitacion-practica-comida.pdf`. Do not move it into
  deployable `static/`, link, import, seed, publish, or create a URL for it.
- `assets/images/celebraciones/`: used images are page media (OWN-001); unused stay in the library
  **hidden** (OWN-003). Audio → Media Library; `.ics` generated, not Media Library (OWN-009).
- Per OWN-009-img, import every referenced content image—gallery, posters, page illustrations, hero,
  founder, and other published images—into the Media Library through a real-content command named
  `seed`. This is not a fixture: it has no `_cdd_fixture`, has no teardown, and follows dry-run,
  explicit apply, idempotent, create-missing-only rules. WordPress regenerates derivative sizes.
- Import all 10 events with **10 public singles** (ADR 0035). The 7 listing-only slugs are
  PLANNED KEEP. Past events: no signup, no add-to-calendar, no `.ics` (OWN-012 / OWN-013).
  Use exactly these slugs unless the redirect ledger is updated through a new accepted decision:
  `circulos-de-presencia-consciente`, `encuentro-nacional-2026`,
  `meditacion-presencial-barranquilla`, `festival-calma-en-la-ciudad`,
  `pausa-profunda-medellin`, `ansiedad-agotamiento-crisis-de-atencion`, `vesak-2026`,
  `pausa-profunda-cali`, `buddhismo-tiempos-cansancio`, and
  `6-encuentro-nacional-2025`.
- Calculate completion on request in `America/Bogota`: an event is completed when
  `today > event_end`, falling back to `event_date`; the final date itself remains current.
  `cancelled` is not overridden. Extending the end date makes the event current again.
- Generate `.ics` only for current events at `/eventos/ical/{slug}.ics`; do not store it in the Media
  Library. Completed events return 410, show no calendar control, and retain no orphan file. Send
  `X-Robots-Tag: noindex, nofollow`, exclude downloads from sitemap/`llms.txt`, and expose
  `rel="alternate" type="text/calendar"` only on current event singles.
- Provide the nonce/capability-protected wp-admin action **Eliminar huérfanos** for `.ics` only:
  dry-run list first, explicit apply second; never delete images, audio, posters, or unrelated media.
- Keep the default build-time change ledger plus a short content freeze immediately before cutover.
- Build WordPress on a separate noindex Hostinger instance without a custom domain until the
  authorized switch (OWN-005). Do not touch the static production document root.
- Per OWN-006, extract from the latest repository `VERSION`, not an older ZIP still deployed on
  Hostinger. Record the exact commit/tag in the payload. If production parity cannot be proven, label
  it `Unverified`; the older deployment is deploy/delta debt, not the extraction source.
- Blog bylines: CPT `blog_author` + `/author/{slug}` (ADR 0037 / OWN-010). Seed Zheng Gong and
  Comunidad Camino del Dharma. Do not use the WP user as public author or a hardcoded copy list.
  WordPress `/comunidad` adds links to these profiles without replacing its existing biography or
  changing the static HTML (OWN-016).
- Store each post's authors as an ordered, unique array of published `blog_author` IDs. Allow
  multiple authors and no default. Drafts may have none; publishing or updating a published post
  requires at least one. Plugin activation must not unpublish legacy posts that lack the relationship.
- Author assignment uses REST search after at least two characters, never a preloaded catalog or
  inline creation. Author profiles use only title/name, slug, content/bio, and featured image; do not
  add person/organization branches, ORCID, or affiliation.
- Do not implement CPT `sangha`.

OWN-008 is **closed** (ADR 0036): same three albums on `/galeria`; public `/galeria/{slug}` with
`noindex` until volume; taxonomy, not album CPT. No numbered pagination (OWN-011). Native Gutenberg
Gallery + lightbox remain binding; no `gallery.js`. Extract all 35 gallery images, alt text, and
album memberships.

---

## 8. Content migration contract

### 8.1 Extract; do not retype

Implement a read-only deterministic extractor:

```text
static HTML / embedded JSON / data-* / referenced assets
        ↓
versioned, reviewable migration payload
        ↓
validate → plan → import → verify
        ↓
WordPress database + Media Library
```

The payload must identify its source commit/version and preserve stable source keys. Prefer
programmatic parsing over manual transcription. Extract from the latest repository `VERSION`, then
compare copy, content, structure, styles, and media relationships against the published site. Resolve
or explicitly approve every repo/Hostinger delta before import.

### 8.2 WP-CLI importer

Implement the importer in `camino-del-dharma-core`, not as a theme feature and never as an activation
side effect.

Required behavior:

- commands or subcommands for `validate`, `plan`, `import`, and `verify`;
- dry-run by default;
- writes only with an explicit `--apply`-style flag;
- idempotent re-execution;
- create-missing-only by default;
- stable `_source_key` and `_source_hash`-style metadata;
- skip unchanged objects;
- never overwrite wp-admin edits by default;
- never delete real content;
- any force mode is field-scoped, explicit, and documented;
- production environment guard requiring explicit confirmation and verified backup evidence;
- post-import count and route verification.

Implement the image Media Library command under the owner-approved name `seed`. Keep its real-content
semantics separate from fixture commands: no fixture marker and no teardown of seeded production
attachments.

Do not run real-content teardown. If disposable fixtures materially improve automated tests, mark
them unambiguously (for example `_cdd_fixture = 1`), prohibit them in production, and delete only
objects created by that fixture system. Do not build a fixture framework merely for completeness.

### 8.3 Required WordPress objects

Create/import real objects and settings for:

- front page and Reading settings;
- Comunidad;
- Linaje;
- Práctica;
- `/practica/videos`;
- `/practica/meditacion-semanal-en-linea`;
- Galería;
- Contacto;
- Donaciones;
- Blog posts page and Reading settings;
- 2 real posts;
- 10 real events with 10 public singles and the ADR 0035 slugs;
- 2 seeded `blog_author` profiles and ordered `authors` relationships on both posts;
- the extracted three album memberships as `gallery_album` terms (ADR 0036);
- 35 public gallery images plus `galeria-04.jpg` attached to Práctica;
- all unreferenced repository images as hidden Media Library attachments, never public gallery/Page
  content unless explicitly assigned;
- all other referenced content images through the real-content Media Library seed;
- 10 event posters and 2 mantra MP3 attachments;
- no PDF attachment and no `.ics` attachment.

`/eventos` is the `event` CPT archive. Never create a Page with slug `eventos`.

Import `/privacidad` from live HTML (`privacidad/index.html`, ADR 0039). Keep the provisional
disclaimer. Do not rewrite the notice except the field-scoped form paragraphs in ADR 0041 / OWN-018
(WordPress only; static stays accurate while the live form does not submit). Legal review is not a
cutover or WU-09 blocker. Contact Form 7 is eligible for production at cutover.

---

## 9. Presentation and behavior requirements

### 9.1 No redesign

Preserve:

- page and section order;
- visual hierarchy;
- navigation structure;
- canonical UI copy;
- layout and responsive behavior;
- calm spacing and reading rhythm;
- focus states, semantic landmarks, and accessibility behavior;
- states with and without current events.

Any intentional static-to-WordPress substitution must be recorded in the migration matrix and ledger.
If the repo differs from published production, record and reconcile that delta before implementing
the affected view.

### 9.2 Template and route mapping

At minimum:

```text
/                                      → templates/front-page.html
/comunidad                             → templates/page-comunidad.html
/linaje                                → templates/page-linaje.html
/practica                              → templates/page-practica.html
/eventos                               → templates/archive-event.html
/eventos/{all-10-approved-slugs}        → templates/single-event.html
/galeria                               → templates/page-galeria.html
/galeria/{album-slug}                  → templates/taxonomy-gallery_album.html or hierarchy fallback
/donaciones                            → templates/page-donaciones.html
/contacto                              → templates/page-contacto.html
/privacidad                            → templates/page.html
/blog                                  → templates/home.html
/blog/{slug}                           → templates/single.html
/author                                → templates/archive-blog_author.html or hierarchy fallback
/author/{slug}                         → templates/single-blog_author.html
unknown route                          → templates/404.html with HTTP 404
```

`templates/index.html` is the required final fallback. `templates/page.html` is a technical fallback,
not the default editorial solution for pages with distinctive layouts.

Canonical public URLs have **no trailing slash**, except the root. Test incoming HTTP behavior; a
correct `get_permalink()` value alone is insufficient.

### 9.3 Events

Use native title, content, and featured image where appropriate. Add only the metadata required by
the content model.

The archive must:

- separate current and completed events unambiguously;
- group current events by month and historical events by year;
- preserve the documented heading hierarchy;
- show at most one valid featured current event on the home page;
- show no empty featured module when no current event exists;
- keep `event_type` and `event_city` as non-public taxonomies;
- expose all 10 approved public singles: 3 existing KEEP and 7 PLANNED KEEP;
- hide signup, add-to-calendar, and `.ics` for completed/cancelled events;
- expose the documented empty state when no current event exists.

The monthly event calendar is a dynamic block. Domain selection/data belongs to
`camino-del-dharma-core`; presentation belongs to the theme. Preserve event cells, weekly meditation
cells, tooltips, keyboard behavior, pointer first/second-touch behavior, and ARIA behavior. Weekly
meditation is not an event.

Determine current/completed state on each request in `America/Bogota`; do not make frontend truth
depend on cron. Generate `.ics` only for current events. The plugin may persist status for wp-admin,
but request-time behavior is authoritative.

### 9.4 Gallery

Replace static `gallery.js`, embedded gallery JSON rendering, handmade thumbnails, and any custom
lightbox expectation with native Gutenberg Gallery blocks and the native WordPress lightbox. Seed the
35 public gallery originals into the Media Library and let WordPress generate derived sizes. Seed
`galeria-04.jpg` separately as Práctica media, never as gallery content. Do not import handmade thumbs
as editorial originals. Album model is closed: taxonomy + hub Page (ADR 0036); no numbered pagination (OWN-011).
The hub keeps General, 2023, and 2021. `/galeria/general`, `/galeria/2023`, and `/galeria/2021`
resolve as term routes and remain `noindex, follow` until a case-by-case editorial decision changes
their status. The Page `/galeria` must not be stolen by taxonomy rewrites.

### 9.5 Other behavior

Preserve or document a native replacement for:

- mobile navigation and keyboard interaction;
- share behavior on event/blog singles;
- add-to-calendar behavior and generated `.ics` downloads for current events only;
- mantra audio playback;
- blog bylines linked to ordered `blog_author` profiles;
- author profiles with bio, image, and related-post listing;
- video embeds;
- skip link, focus management, reduced motion, and semantic landmarks.

Do not blindly enqueue the old JavaScript. Inspect its DOM/selectors and either preserve the required
contract or document the native Gutenberg replacement.

Render blog bylines from the ordered relationship as “Por A y B” using the site's documented voice.
Author-profile related posts query the `authors` relationship, never `post_author`. Each profile
emits JSON-LD `Thing` with its name and canonical profile URL; the post publisher remains the site's
Organization.

---

## 10. Routing, SEO, privacy, and security

### 10.1 Routing

- Preserve every current public URL as `KEEP` unless `docs/redirect-ledger.md` specifies 301/410.
- Port legacy redirects from static `.htaccess`.
- Avoid chains, loops, temporary redirects, and soft 404s.
- Flush rewrite rules only on plugin activation or explicit versioned upgrade, never per request.
- Do not expose event-city or event-type archive URLs.
- Preserve the 3 existing event singles and add the 7 ADR 0035 routes as PLANNED KEEP.
- Preserve `/galeria`; add the 3 ADR 0036 term routes as PLANNED KEEP.
- Add `/author`, `/author/zheng-gong`, and `/author/comunidad-camino-del-dharma` under ADR 0037.
- Native WP-user author archives return 404; no Page may use slug `author`.
- Current event `.ics` routes return generated calendar data; completed/retired `.ics` routes return
  410 and are not redirected to a single.
- Do not create search or `/buscar`.
- Do not create `/404`; render the 404 template with an actual 404 status.

### 10.2 SEO

- Use native `/wp-sitemap.xml` in WordPress; staging must be non-indexable.
- Update production `robots.txt` only during an authorized cutover.
- Native `post_tag` archives are supported at `/blog/tag/{slug}`. When a term exists, the incoming
  route must resolve and remain `noindex, follow` until the documented qualitative review changes.
- `gallery_album` term routes remain `noindex, follow` until separately approved; `/galeria` remains
  the indexable hub.
- `/author` remains `noindex, follow` until volume; `blog_author` singles are indexable.
- Preserve server-rendered titles, descriptions, canonical URLs, Open Graph, Twitter metadata, and
  JSON-LD according to `docs/15-assets-strategy.md`.
- All 10 event singles use Event JSON-LD with real data. Completed events use `EventCompleted` and
  omit signup offers. Never invent optional fields.
- Generated `.ics` responses send `X-Robots-Tag: noindex, nofollow`, stay outside the sitemap and
  `llms.txt`, and are linked with `rel="alternate" type="text/calendar"` only while current.
- Do not install an SEO suite.

### 10.3 Privacy and contact

Contact Form 7 is approved, sends to `caminodeldharma1@gmail.com`, and is **eligible for production
at cutover** (ADR 0041 / OWN-018):

- `/privacidad` is published (ADR 0039); import the live notice; keep the provisional disclaimer;
- legal review is recommended later and **does not block** WU-09, staging, or cutover;
- before enabling CF7 in a WordPress environment, apply the ADR 0041 copy delta on that environment's
  `/privacidad` Page (form now submits server-side). Do not change static HTML while production still
  uses `action="#"`;
- local/staging implementation and synthetic-data testing may proceed in WU-09;
- real delivery must be verified in Hostinger staging before release (`Pass (local)` is not enough);
- if staging mail fails, cutover may proceed with CF7 disabled and WhatsApp/email working, recorded
  in the matrix and checklist — that is an operational fallback, not a legal gate;
- do not add another antispam plugin without its own ADR.

### 10.4 Security and prohibited features

- No GA4 or other analytics.
- No cookie-based measurement; Search Console remains the approved measurement channel.
- No PWA, manifest, or Service Worker.
- No HSTS activation; reconsider only after at least 30 stable days following an authorized cutover.
- No public registration, private member area, LMS, ecommerce, or internal payment processing.
- No secrets, credentials, salts, databases, backups, WordPress core, third-party plugin code, or
  production uploads in Git.
- Escape output, sanitize input, validate capabilities, use nonces for state changes, and prepare SQL.
- Prefix first-party functions, classes, hooks, options, and metadata consistently.
- Make user-facing first-party PHP strings translation-ready with the project text domain.

---

## 11. Local environment and QA evidence

Implement the three-service Docker environment from ADR 0023:

```text
db         → MariaDB 11.8 with healthcheck and persistent local volume
wordpress  → WordPress on PHP 8.3, bound to localhost
wpcli      → matching WordPress CLI service and mounts
```

Bind-mount only first-party theme/plugin code. Keep core and database in Docker volumes. Use a
gitignored `.env`, a versioned `.env.example`, fail-fast environment-variable syntax, a configurable
localhost port, `WP_ENVIRONMENT_TYPE=local`, and debug logging in every PHP-running service.

Record evidence using only:

- `Unverified`
- `Pass (local)`
- `Pass`
- `Fail`

`Pass (local)` never proves Hostinger PHP/Apache/HTTPS/mail behavior.

Test taxonomy (runners, TDD, what not to mock, Sonar): `docs/guia-pruebas-plugin-theme-fse.md`
and ADR 0038. Create the PHPUnit + wp-phpunit kit the same day first-party PHP exists. FABLE5
QA levels below are *migration evidence*; they map to those runners (QA 1 = cheap gate, QA 2 =
unit + wp-phpunit, QA 3 = wp-phpunit + isolated harnesses, QA 4 = manual + staging).

When PHP tests exist, add `.github/workflows/test.yml` for quality only: run static CSS lint and
`composer test` on pushes to `main` and pull requests. Do not add deploy secrets, deployment steps,
SonarScanner, or any CD workflow.

### QA level 1 — static checks

- PHP syntax;
- PHPCS with WordPress Coding Standards;
- JSON/YAML/block metadata parsing;
- CSS lint/build for both preserved static and WordPress sources as applicable;
- `git diff --check`;
- no secrets;
- no forbidden classic templates or dependencies.

### QA level 2 — component checks

- CPT, taxonomies, fields, capability and sanitization behavior;
- event current/historical/featured rules;
- all 10 event single routes and approved slugs;
- `blog_author` assignment, publication guard, search, ordering, and query-var isolation;
- `gallery_album` hub/term rewrite behavior and noindex policy;
- request-time event completion, generated `.ics`, 410 behavior, and scoped orphan cleanup;
- WP-CLI validate/plan/import/verify;
- importer idempotency and create-missing-only behavior;
- fixture isolation if fixtures exist.

### QA level 3 — local integration

- plugin and theme activation without warnings/fatals;
- empty `debug.log` after representative navigation;
- Page/settings creation;
- template hierarchy and block rendering;
- incoming routes, canonicalization, redirects, and real 404;
- counts and media relationships;
- navigation, calendar, share, audio, gallery, author profiles, and generated downloads;
- anonymous cookie/storage behavior.

### QA level 4 — visual and staging

- mobile, tablet, desktop, 320 px, and 200% zoom;
- keyboard-only navigation and visible focus;
- screen-reader-relevant labels/headings/ARIA;
- visual comparison against `static/`;
- copy, content, structure, and style comparison against `https://caminodeldharma.org`, with
  repo/live deltas recorded and published production used as the acceptance baseline;
- staging PHP/Apache/HTTPS behavior;
- actual Contact Form 7 delivery;
- network requests and absence of unexpected tracking;
- non-indexability of staging.

Unavailable environment-dependent checks remain `Unverified`; never imply they passed.

---

## 12. Durable execution and work units

Before implementation code, create if absent:

```text
.audit/fase3-execution-state.md
.audit/fase3-validation-matrix.md
docs/operations/wordpress-manual-deployment.md
docs/operations/third-party-plugins.md
```

Extend, never replace:

```text
docs/migracion-static-wordpress.md
docs/matriz-migracion-static-wordpress.md
docs/redirect-ledger.md
```

The execution-state file must record:

- phase status;
- branch and exact source commit;
- current work unit;
- completed work;
- active risks and failures;
- validation evidence;
- documentation discrepancies;
- assumptions/defaults used;
- blockers;
- changed files;
- last verified commit;
- next exact action;
- resume procedure.

Each work unit defines: objective, binding sources, expected files, dependencies, risks, rollback,
acceptance criteria, QA plan, and checkpoint boundary.

Use these work-unit boundaries unless repository evidence requires a safer subdivision:

1. **WU-00 — Preflight and durable harness**
2. **WU-01 — Monorepo reorganization**
3. **WU-02 — Local Docker environment**
4. **WU-03 — First-party plugin scaffold and quality tooling**
5. **WU-04 — FSE theme scaffold and visual token baseline**
6. **WU-05 — Event, gallery-album, and blog-author domain models, routing, calendar/ICS data**
7. **WU-06 — Extractor, payload, WP-CLI importer, reconciliation**
8. **WU-07 — Pages, posts, authors, media, templates, gallery**
9. **WU-08A — Front-end behavior** (share dialog, add-to-calendar dialog, mantra audio). Separate
    session. Model: Claude 4.6 Opus. Prompt: short resume from the execution state. Do **not**
    paste this master prompt.
10. **WU-08B — SEO, redirects, OWN-015, accessibility pass.** Separate session after 08A. Model:
    Claude 4.6 Opus. Prompt: short resume **plus** this file’s §9.5 and §10 only (not §1–§8, not
    the first-response contract). Stop after 08A; do not continue into 08B in the same session.
11. **WU-09 — Contact Form 7 integration and `/privacidad` form-copy update (ADR 0041)**
12. **BUG-001 — Círculos `.ics` includes every session. Closed 2026-08-31**, in its own session
    before WU-10. One VEVENT per `event_calendar_dates` entry with its own UID
    (`slug-Ymd@host`); range fallback and published UID when there is no schedule; one payload
    behind the dialog and the file, with the deep links naming the next session because a deep
    link carries a single entry. The static welcome-only `.ics` was neither copied nor touched.
13. **WU-10 — Full local QA and staging-readiness runbook**

WU-02 is a dedicated-session work unit under ADR 0023. Do not start it in the same session that
implements WU-01 or any application code: stop after WU-01. In the next session, rerun preflight,
implement and validate only WU-02, update the execution state, and stop again. Theme/plugin
implementation begins in a later session after rerunning the WU-02 QA gate. Keep application work in
separate commits/work units. Make small, reviewable commits after applicable QA passes. Use concise
English conventional commit messages. Do not push.

Do not stop after planning. Continue through safe, unblocked work units, except for ADR 0023's
mandatory boundaries before and after WU-02. Otherwise stop only when:

- the next action requires an external write not authorized here;
- a missing owner/legal decision would materially alter architecture, public URLs, content ownership,
  or data safety;
- required credentials or environment access are unavailable;
- repeated evidence shows the current strategy is unsafe or impossible.

For failures: capture evidence, form a hypothesis, change one relevant variable, and retry. Do not
repeat the same failed action more than twice without changing strategy.

---

## 13. Preflight and execution sequence

Begin with:

1. Confirm repository identity and read the binding sources.
2. Inspect Git status, branch, recent history, tags, current `VERSION`, and uncommitted user work.
3. Before any mutation, require a clean owner-approved baseline. If the working tree has pre-existing
   tracked or untracked changes, do not auto-stash, stage, commit, move, or absorb them; stop WU-00
   and ask the owner for the smallest resolution.
4. If the clean baseline is on `main`, create a dedicated local feature branch. Record the baseline
   commit and create a collision-safe local annotated pre-reorganization tag. Do not push either.
5. Inspect the actual root/static/wordpress state; do not assume Fase 3 is still untouched.
6. Inspect available Docker, PHP, WP-CLI, Node, npm, PHPCS, and browser tooling.
7. Record the exact baseline commit/version and whether Hostinger production parity is verified.
8. Create/update the durable execution artifacts.
9. Define WU-01 acceptance and rollback.
10. Execute the first unblocked work unit and continue until the next mandatory boundary. When
    starting from Fase 3 not initiated, complete WU-01 and stop before WU-02.

Preserve unrelated user changes. Do not reset, clean, force, amend unrelated history, or overwrite
work you did not create.

When resuming:

```bash
git status --short
git branch --show-current
git log -5 --oneline
```

Then read `.audit/fase3-execution-state.md`, verify it against Git, rerun the last relevant QA gate,
and continue from `Next exact action`. Repository state outranks chat memory.

---

## 14. Completion gates

Repository/local work is ready for staging only when:

- the monorepo is correctly separated;
- the source hierarchy in §4.2 is followed and every repo/Hostinger delta is reconciled or explicitly
  accepted by the owner with a field-level ledger entry before staging is declared content- or
  presentation-complete;
- theme and plugin activate locally without warnings/fatals;
- every required WordPress object has an explicit import strategy;
- importer dry-run and idempotency checks pass locally;
- baseline counts reconcile or every mismatch is documented;
- all current public URLs and redirects have an implementation and incoming-route test;
- FSE templates and behavior meet the static contract or record an accepted substitution;
- no production fixture exists;
- Level 1–3 checks are green or honestly documented;
- the manual deployment runbook is bounded to first-party theme/plugin code;
- Contact Form 7 is implemented or an operational fallback is recorded; the ADR 0041 `/privacidad`
  form-copy delta is applied in WordPress; legal review is not required.

Fase 3 is not fully validated until staging evidence exists. Production migration is not complete
until the separate cutover checklist passes. This prompt does not authorize that cutover.

```text
NO CUTOVER WITH BROKEN NAVIGATION.
DEPLOY SUCCESS ≠ APPLICATION SUCCESS.
```

---

## 15. Initial response contract

Your first response must be concise and evidence-based. Report:

- repository and branch;
- clean/dirty working tree;
- current Fase 3 state found;
- exact baseline commit/version;
- binding decisions discovered;
- any immediate contradiction that changes implementation;
- first work unit, acceptance criteria, QA gate, and checkpoint;
- immediate blocker, if any.

Then begin preflight and implementation. Do not merely restate this prompt and do not ask permission
to inspect the repository.

Progress updates use:

```text
Work unit:
Implemented:
Validation:
Decision/default used:
Checkpoint:
Next action:
```

---

## 16. Final response contract

Report:

1. **Outcome:** complete locally, ready for staging, partial due to blockers, or blocked.
2. **Implemented:** grouped by work unit.
3. **Commits and important files.**
4. **Migration coverage:** CONTENT, PRESENTATION, ROUTING, BEHAVIOR, OPERATIONS.
5. **Content reconciliation:** expected versus imported counts and explained mismatches.
6. **Validation evidence:** method, environment, result, status, commit tested.
7. **Environment status:** local, staging, production reported separately.
8. **Privacy / CF7:** `/privacidad` status (provisional disclaimer kept; form paragraphs updated per
   ADR 0041 in WordPress) and Contact Form 7 release eligibility (legal review is not a blocker).
9. **Governance:** ADR/doc conflicts found, defaults used, ledger/matrix updates.
10. **Out-of-scope confirmation:** no `sangha`, search, analytics, PWA, HSTS, event-city/type
    archives, custom gallery system, unapproved plugin, deployment/CD workflow, or production
    cutover. ADR 0038's quality-only `test.yml` remains required.
11. **Continuity:** working-tree state, last verified commit, current execution-state file, and exact
    resume action.
12. **Recommended next action:** exactly one.

Never claim staging, production, delivery, parity, routing, or migration success without executed
evidence from the corresponding environment.

---

## 17. Final instruction

Begin now. Inspect the repository, read the binding sources, establish durable state, and execute the
first unblocked work unit. Continue autonomously through safe local work. Preserve the live static
site, its real content, its URLs, and its rollback path.

Do not deploy, push, create external infrastructure, run production imports, or perform cutover
without explicit current-session authorization. The ADR 0041 `/privacidad` form-copy delta is
authorized for the WordPress Page in WU-09; do not change the live static notice while the static
form still does not submit.
