# Agent guidelines — Camino del Dharma

This repository is the **Camino del Dharma** static website (production today) and the planning
docs for a future WordPress / block-theme migration. It is **not** another WordPress project.
Do not copy names, URLs, slugs, CPTs, hosts, or deploy architecture from other repos.

## Inspect before modify

Read `README.md`, `docs/17-orden-implementacion.md` (current phase), `docs/adr/README.md`,
and `docs/contrato-migracion-static-wordpress.md` before changing architecture or WordPress plans.

## Three times

| | |
| --- | --- |
| **Historical** | Older docs may mention classic PHP templates (`*.php`) or a previous WordPress on this domain (`.htaccess` leftovers). Do not rewrite those as if they were never true. |
| **Current** | Production is the **static** site at `https://caminodeldharma.org`. HTML lives at the **repo root**. There is no `wordpress/` tree, no `static/` folder, no CI deploy workflow. |
| **Future** | Fase 3: `static/` + `wordpress/` (ADR 0014). Migration path is **static mockup → FSE block theme** (ADR 0029). No classic PHP theme in between. Plugin `camino-del-dharma-core` (ADR 0024). |

## Canonical content

```text
content-source/  >  structured docs (03, 09, 16)  >  generated static HTML
```

Do not treat published HTML as a second editorial source. Do not paraphrase `content-source/`.
Priority: `content-source/` > `docs/` > other files.

## Static prototype is a contract

Unless an ADR records an exception (e.g. ADR 0021 gallery lightbox), the static HTML/CSS/JS is the
visual and behavioral contract (ADR 0001, ADR 0002).

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
- Distinguish **fixtures** from real content (ADR 0033). Never generic teardown against editorial data.
- Preserve wp-admin edits; default importer behaviour is create-missing-only.
- No destructive DB reset in production. No implicit overwrite of production content.
- `STATIC DEPLOY ≠ WORDPRESS CODE DEPLOY ≠ WORDPRESS CONTENT`. Never static-ZIP over a WordPress
  document root after cutover.
- Flush rewrite rules on activation/upgrade only, never on every request.
- Future theme is **FSE / block theme built directly from the static mockup** (ADR 0029). Do **not**
  scaffold a classic PHP theme (`front-page.php`, `page-*.php`) as a bridge.
- CPT `sangha` is out of initial Fase 3 scope unless the owner reopens it (ADR 0024).
- Privacy page copy is not invented (ADR 0028).

## Do not deploy while auditing or documenting

This includes Hostinger File Manager, FTP, and production. Do not run content migrations, create
database content, commit, or push unless the owner asked.

## After verified implementation

Update durable docs (matriz, ledger `migracion-static-wordpress.md`, ADR if the decision changed).
Do not silently resolve doc vs code conflicts.

## Code language

Code, comments, and Git messages: English. Editorial copy and most `docs/`: Spanish (Colombia).
