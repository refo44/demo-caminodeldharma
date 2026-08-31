# Fase 3 — Estado de ejecución durable

Artefacto de continuidad exigido por FABLE5 v2.3 §12. Otra sesión debe poder retomar el trabajo
leyendo este archivo y verificándolo contra Git, sin historial de chat.

| | |
| --- | --- |
| **Última actualización** | 2026-08-31 (sesión WU-06) |
| **Fase** | Fase 3 — WordPress (iniciada) |
| **Work unit activo** | Ninguno — WU-00…WU-06 **cerrados**; checkpoint antes de WU-07 |
| **Rama** | `fase3-wordpress` (local, sin push) |
| **Commit baseline** | `d96bcbd` (`main`, árbol limpio, VERSION `1.0.35`) |
| **Tag de rollback** | `fase3-pre-reorg-v1.0.35` (anotado, local, apunta a `d96bcbd`) |
| **Paridad producción** | **Verificada 2026-08-31 (WU-06)**: `curl`+`diff` byte a byte contra `https://caminodeldharma.org` — 17/17 superficies idénticas al repo (`static/`, VERSION 1.0.35) + sitemap + `.ics`. Delta repo↔Hostinger = 0 (OWN-006/007); la extracción usa el mismo contenido publicado. |

## Trabajo completado

- **WU-00 — Preflight y harness durable** (commit `5088e32`): preflight ejecutado (repo limpio
  en `main`, Fase 3 no iniciada). Rama `fase3-wordpress` y tag `fase3-pre-reorg-v1.0.35`
  creados. Artefactos durables: este archivo, `.audit/fase3-validation-matrix.md`,
  `docs/operations/wordpress-manual-deployment.md`, `docs/operations/third-party-plugins.md`.
- **WU-01 — Reorganización monorepo** (commits `bfb6dc0` + tooling): superficie desplegable
  (receta ZIP, 238 archivos) movida raíz → `static/` con renames 100%; PDF OWN-002 archivado en
  `docs/archive/recitacion-practica-comida/` (nunca en `static/`). Tooling y docs actualizados.
  QA gate: matriz WU-01 todo Pass (local); sin despliegue.
- **WU-02 — Entorno local Docker** (commit `b9c9eb8`, ADR 0023): `docker-compose.yml` raíz
  (db MariaDB 11.8 healthcheck, wordpress PHP 8.3 en `127.0.0.1:${WORDPRESS_PORT:-8080}`,
  wpcli `33:33`), `.env.example` versionado, `.env` gitignored, fail-fast `${VAR:?}`,
  `WP_ENVIRONMENT_TYPE=local` + debug log. Core WordPress 7.1 instalado vía wpcli. Gotcha
  `WORDPRESS_DEBUG` documentado en el playbook. QA: matriz WU-02 todo Pass (local).
- **WU-03 — Scaffold del plugin y kit de calidad TDD** (commit `fe33b96`, ADR 0038/0027):
  primer PHP propio (`camino-del-dharma-core.php`, guard `ABSPATH`, `CDD_CORE_VERSION` 0.1.0),
  kit raíz Composer (PHPUnit 9.6.36, wp-phpunit 7.1.0, WPCS 3.4.1), suites `tests/Unit` +
  `tests/WordPress` (harness efímero `cdd-wp-phpunit`, `down -v`), `phpcs.xml.dist` (prefijos
  `cdd_core` / `camino_del_dharma`), `tools/`, CI solo-calidad `test.yml`. QA: matriz WU-03
  todo Pass (local); CI/Sonar `Unverified` (sin push).
- **WU-04 — Scaffold del theme FSE y baseline de tokens visuales** (sesión 2026-08-31,
  ADR 0029/0038, TDD honesto: RED documentado en unit **y** wp-phpunit antes del primer
  archivo del theme):
  - Reanudación verificada: preflight limpio + rerun gates WU-02 (fail-fast / db healthy /
    `wp_get_environment_type()` = `local`) y WU-03 (php-lint, unit 2/2, phpcs limpio).
  - Tests primero: `tests/Unit/Theme_ScaffoldTest.php` (style.css/header, index.html, parts,
    theme.json v3 + templateParts, bootstrap + hoja complementaria, **guard contra plantillas
    PHP clásicas**), `tests/Unit/Theme_TokensTest.php` (paridad de tokens contra el `:root`
    de `static/assets/css/main.css` **extraído programáticamente**: paleta 6 brand +
    text-on-dark, 11 roles semánticos con la misma indirección, 3 familias, 5 espaciados,
    65ch/70rem, ritmo, line-heights, política paleta-only),
    `tests/WordPress/Theme_RegisteredTest.php` (theme sin errores, `is_block_theme()`).
  - Scaffold: `wordpress/wp-content/themes/camino-del-dharma/` — `style.css` (metadata, text
    domain `camino-del-dharma`, v0.1.0), `theme.json` v3 (baseline de paridad visual),
    `templates/index.html` (fallback técnico con query loop mínimo),
    `parts/header|footer.html` (placeholders site-title), `functions.php` (supports +
    encolado `assets/css/main.css` versionado por mtime), hoja complementaria placeholder.
  - `package.json` `lint:css` ampliado a los dos árboles CSS (guía §7).
  - Theme **activo** en el entorno local: sin warnings/fatals, `debug.log` inexistente,
    presets en el HTML, sin cookies anónimas. QA: matriz WU-04 todo Pass (local);
    QA 4 visual y CI/Sonar `Unverified`.

- **WU-05 — Modelos de dominio, routing y datos de calendario/ICS** (sesión 2026-08-31,
  ADR 0022/0035/0036/0037/0038, OWN-009/012/013; TDD honesto: RED documentado en unit
  **y** wp-phpunit antes del primer archivo de `includes/`):
  - Reanudación verificada: preflight limpio + rerun gates WU-03 (php-lint, unit 15/15,
    phpcs) y WU-04 (`run-phpunit-wp.sh` verde; theme activo como block theme sin
    warnings/fatals).
  - Clases puras (unit, 44 tests / 177 asserts): `Cdd_Core_Event_Status` (OWN-013 en
    `America/Bogota`, día final vigente, cancelado inmutable), `Cdd_Core_Ics_Generator`
    (paridad con los `.ics` de producción), `Cdd_Core_Calendar_Data` (celdas evento /
    lunes de práctica / mes del próximo vigente), `Cdd_Core_Featured_Event_Policy`
    (5 reglas del Inicio, doc 03 §3), `Cdd_Core_Authors_List`.
  - Registro WP (wp-phpunit, 34 tests / 258 asserts): CPT `event` (archive `eventos`,
    rutas sin barra final), taxonomías `event_type`/`event_city` **no públicas**,
    `gallery_album` sobre attachments (`/galeria/{slug}` sin robar la Page `/galeria`),
    CPT `blog_author` (query_var `blog_author`, rewrite `author`, caps propias + grant),
    meta saneado de evento, relación `authors` con guard de publicación (programático →
    draft; REST → 400; post publicado nunca a cero autores; legados intactos), archivos
    de usuario WP en 404, ruta `.ics` `/eventos/ical/{slug}.ics` (200 vigente / 410
    finalizado / 404), upgrade versionado con flush solo en activación/upgrade.
  - Plugin 0.2.0 activo en el entorno local (upgrade automático verificado). QA: matriz
    WU-05 todo Pass (local); HTTP bonito entrante, SEO/noindex y CI/Sonar `Unverified`.

- **WU-06 — Extractor, payload, importador WP-CLI y reconciliación** (sesión 2026-08-31,
  ADR 0032/0033/0034, OWN-001/002/003/006/007/009-img; TDD honesto: RED en unit (30 errors)
  y wp-phpunit (8 errors) antes de `includes/migration/`):
  - Extractores puros (74 tests unit / 290 asserts) sobre los archivos reales de `static/`:
    fechas en español, eventos (10, slugs ADR 0035, precedencia JSON-LD→texto, cronograma de
    Círculos como `event_calendar_dates`, excerpt = descripción `.ics` de producción), blog
    (bylines→fichas, hero→featured), galería (3 álbumes / 35 imágenes, sin `galeria-04`),
    páginas (URLs raíz-relativas), inventario de medios (81: 71 públicas + 10 ocultas),
    payload con `_source_key`/`_source_hash` y JSON determinista (MD5 estable).
  - `migration/payload.json` versionado (fuente: VERSION 1.0.35, commit `bfb6dc0` — último
    que tocó `static/`). Runner: `tools/extract-payload.sh` (lectura pura).
  - Importador (43 tests wp-phpunit / 313 asserts) + WP-CLI `wp cdd-core migrate
    validate|plan|import|verify` y `wp cdd-core seed`: dry-run por defecto, `--apply`,
    idempotente, create-missing-only, ediciones wp-admin intactas, guard de producción
    (`--confirm-production` + `--backup-evidence`), settings (front/posts page, permalinks
    `/blog/%postname%`) con flush correcto (re-registro de permastructs).
  - Pipeline ejecutado contra el entorno local: 109 objetos creados + 35 asignaciones de
    álbum; verify 0 missing; 2.º apply 0 created; rutas HTTP entrantes 200/301/404/410
    correctas; conteos reconcilian (los +2 son contenido demo del install local, explicado).
  - Dos bugs cazados por el QA con regresión+fix: evento futuro quedaba `future`; flush sin
    permastructs de CPT. Plugin 0.2.0 → **0.3.0**. Paridad live verificada (ver arriba).
  - QA: matriz WU-06 todo Pass (local); render de vistas (WU-07) y CI/Sonar `Unverified`.

## Riesgos y hallazgos activos

- Paridad repo↔Hostinger **verificada** (WU-06, delta 0). Re-verificar en el freeze pre-corte.
- `descargas/Resumen programa EVF.mp4` en la raíz, no trazado y fuera de la receta ZIP: se
  preserva intacto; clasificar en el inventario si aparece referenciado.
- `_config.yml` y `.nojekyll` son restos de GitHub Pages (desactivado 2026-07-28), fuera del
  ZIP; su retiro es una limpieza separada.
- `test.yml` y el análisis Sonar de plugin+theme quedan `Unverified` hasta que exista push (la
  rama es local por diseño del master prompt).
- El front local con el theme activo muestra el scaffold sin las vistas reales — esperado
  hasta WU-07+; la comparación visual contra el estático (QA 4) queda `Unverified`.
- El entorno local ya usa la estructura definitiva `/blog/%postname%` (aplicada por el
  importador). Contenido demo del install local («Sample Page», «Hello world!») convive con
  el contenido importado; el staging partirá limpio.
- El contenido importado se almacena íntegro (bloques `wp:html`, medios reescritos a la
  biblioteca) pero el theme scaffold no lo pinta entero — plantillas reales en WU-07.

## Decisiones/asunciones usadas

- Autorización del propietario para Fase 3: mensaje «Implementar» sobre FABLE5 v2.3
  (2026-08-31); esta sesión ejecuta solo WU-04 por orden explícita del owner.
- Prefijos primer partido (ADR 0027 + sniff WPCS): plugin `cdd_core`, theme
  `camino_del_dharma`. Registrado en `phpcs.xml.dist`.
- WU-04: (1) sin `fontSizes` en el baseline — el `:root` del estático no define tokens de
  tamaño tipográfico; se añadirán cuando las plantillas los necesiten, sin inventar escala.
  (2) woff2 no copiados aún al theme; `fontFace` llega con las plantillas reales (WU-07+).
  (3) parts header/footer son placeholders mínimos para registrar `templateParts`.
  (4) `register_nav_menus()` pospuesto: block theme gestiona menús con el bloque Navigation;
  revisar al construir el header real. (5) Política paleta-only en el editor
  (`settings.color.custom/defaultPalette/... = false`, docs/12 §8), asertada por test.
- `tools/wp-tests.env` versiona credenciales **desechables** del harness efímero (localhost,
  `down -v`); no son secretos. El `.env` del entorno de desarrollo sigue gitignored.
- WU-05: (1) nombres de meta = doc 03 y `authors` sin prefijo (contrato ADR 0037 §6);
  (2) `event_signup_payment` boolean (el pago siempre vía `event_signup_url`);
  (3) `event_calendar_dates` (sesiones sueltas) añadido por el contrato del calendario
  publicado — el rango solo es fallback; el extractor WU-06 lo puebla para Círculos;
  (4) DESCRIPTION del `.ics` = excerpt editorial (el extractor trae el copy, el plugin no
  lo inventa); (5) «Eliminar huérfanos» (OWN-015) pospuesto a WU-08 (el `.ics` WordPress
  se genera bajo demanda, sin archivos); (6) guard REST con stash por request para
  publicar con autores en el mismo request. Detalle en la matriz § WU-05.

## Evidencia de validación

Ver `.audit/fase3-validation-matrix.md` § WU-05. Estados: `Unverified`, `Pass (local)`,
`Pass`, `Fail`.

## Bloqueos

- Ninguno. WU-06 (extractor determinista, payload versionado, importador WP-CLI
  validate/plan/import/verify, `seed` de medios, reconciliación de conteos) es el
  siguiente work unit; puede arrancar en la próxima sesión tras rerun del preflight y de
  los gates (§ Reanudación).

## Archivos cambiados en la sesión actual (WU-06)

- Tests nuevos (RED antes de la implementación): `tests/Unit/Spanish_DateTest.php`,
  `tests/Unit/Event_ExtractorTest.php`, `tests/Unit/Blog_ExtractorTest.php`,
  `tests/Unit/Gallery_ExtractorTest.php`, `tests/Unit/Page_ExtractorTest.php`,
  `tests/Unit/Media_InventoryTest.php`, `tests/Unit/Payload_BuilderTest.php`,
  `tests/WordPress/ImporterTest.php`
- Plugin `camino-del-dharma-core` 0.2.0 → **0.3.0**: `includes/migration/` nuevo
  (`class-cdd-core-spanish-date.php`, `class-cdd-core-dom.php`,
  `class-cdd-core-event-extractor.php`, `class-cdd-core-blog-extractor.php`,
  `class-cdd-core-gallery-extractor.php`, `class-cdd-core-page-extractor.php`,
  `class-cdd-core-media-inventory.php`, `class-cdd-core-payload-builder.php`,
  `class-cdd-core-importer.php`, `class-cdd-core-cli.php`), bootstrap con requires + registro
  WP-CLI, `README.md`
- `migration/payload.json` (nuevo, versionado), `tools/extract-payload.php|sh` (nuevos)
- `docker-compose.yml` (mounts RO `migration/` + `static/` en wpcli)
- `docs/migracion-static-wordpress.md`, `docs/conteos-reconciliacion-migracion.md`,
  `docs/matriz-migracion-static-wordpress.md`, `docs/docker-wordpress-playbook.md`
- CLAUDE.md, AGENTS.md, `CHANGELOG.md`, `.audit/fase3-validation-matrix.md`, este archivo

## Último commit verificado

`PENDIENTE-WU06` — implementación de WU-06 (QA local verde). Baseline visual del theme:
`d3b30f5` (docs/12 §8). Historial en `fase3-wordpress`: `5088e32` (WU-00) →
`bfb6dc0`/`54cd09f` (WU-01) → `11237a1` → `b9c9eb8` (WU-02) → `81d7547` → `fe33b96` (WU-03)
→ `36d368b` → `d3b30f5` (WU-04) → `196ef78` → `e8c52c9` (WU-05) → `c270a37` →
`PENDIENTE-WU06` (WU-06).

## Estado del entorno local

Contenedores del proyecto `camino-del-dharma` levantados al cierre (`docker compose stop`
para pararlos). WordPress local: `http://localhost:8081`. Plugin `camino-del-dharma-core`
**activo** (v0.3.0). Theme `camino-del-dharma` **activo** (v0.1.0, scaffold). **Contenido
importado** por `wp cdd-core migrate import --apply` (payload 1.0.35/`bfb6dc0`): 11 páginas
+ 10 eventos + 2 posts + 2 fichas + 3 álbumes + 81 medios; permalinks `/blog/%postname%`;
front page `inicio`, posts page `blog`. Además contenido demo del install («Sample Page»,
«Hello world!»). El harness efímero `cdd-wp-phpunit` no deja contenedores ni volúmenes.

## Próxima acción exacta

WU-06 está cerrado. **La sesión actual se detiene aquí** (checkpoint FABLE5 §12). La
siguiente sesión: rerun del preflight (§ Reanudación) y de los gates (php-lint, unit — ahora
74 tests —, phpcs limpio, `run-phpunit-wp.sh` — ahora 43 tests —, plugin 0.3.0 y theme
activos sin warnings; `wp cdd-core migrate verify` → 0 missing en el entorno local), luego
implementar y validar **solo WU-07** — Pages, posts, autores, media, plantillas FSE y
galería: plantillas reales del theme (`front-page`, `page-*`, `archive-event`,
`single-event`, `home`, `single`, `single-blog_author`, `taxonomy-gallery_album`, `404`),
partes header/footer reales, conversión del contenido `wp:html` a bloques donde aplique
(force explícito de campo o edición wp-admin; el `_cdd_source_hash` delata lo intacto),
bloque dinámico del calendario (datos ya en `cdd_core_calendar_month_data`), galería con
bloque nativo + lightbox, bylines «Por …» desde la relación `authors` — comparando copy y
estructura contra `https://caminodeldharma.org` (OWN-007) — actualizar este archivo y
detenerse en el checkpoint de WU-07.

## Procedimiento de reanudación

```bash
git status --short
git branch --show-current   # esperar: fase3-wordpress
git log -5 --oneline
```

Leer este archivo, verificarlo contra Git (¿existe el theme con `theme.json`? ¿qué commits
hay en `fase3-wordpress`?), rerun del último gate QA relevante de la matriz, y continuar
desde «Próxima acción exacta». El estado del repositorio prevalece sobre la memoria de chat.
