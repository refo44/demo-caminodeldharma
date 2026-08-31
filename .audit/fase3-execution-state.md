# Fase 3 — Estado de ejecución durable

Artefacto de continuidad exigido por FABLE5 v2.5 §12. Otra sesión debe poder retomar el trabajo
leyendo este archivo y verificándolo contra Git, sin historial de chat.

| | |
| --- | --- |
| **Última actualización** | 2026-08-31 (WU-08A implementado y validado) |
| **Fase** | Fase 3 — WordPress (iniciada) |
| **Work unit activo** | Ninguno — WU-00…**WU-08A cerrados**; checkpoint antes de **WU-08B** |
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

- **WU-07 — Pages, posts, autores, media, plantillas FSE y galería** (sesión 2026-08-31,
  ADR 0021/0029/0035/0036/0037, OWN-007/011/012/016; TDD honesto: RED documentado en unit
  (15 E + 14 F) **y** wp-phpunit (13 E + 4 F) antes del primer archivo de vistas):
  - Reanudación verificada: preflight limpio + rerun de todos los gates (unit 74, phpcs,
    wp-phpunit 43, plugin 0.3.0/theme activos, `migrate verify` 0 missing).
  - Theme **0.2.0** — vistas reales: 16 plantillas de bloques (front-page, 7 `page-*`,
    `page.html`, home, single, single-event, archive-event, single-blog_author,
    taxonomy-gallery_album, 404, index), parts header/footer vía patterns PHP (URLs
    generadas, contrato DOM de main.js intacto), 11 bloques dinámicos
    (`camino-del-dharma/eventos-calendar` con **paridad byte a byte** contra el grid
    publicado, eventos-listado con tarjeta compacta doc 03 §3, evento-destacado con
    estado vacío, evento-tipo/meta/cta, entrada-cabecera ADR 0037, blog-listado/recientes,
    autor-ficha, album-galeria nativa), CSS estático portado a presets
    (`--wp--preset/custom/style--*`, sin `:root`), fontFace autohospedadas en theme.json,
    lightbox nativo habilitado, main.js portado + calendar-tooltips.js (mitad grid).
  - Plugin **0.4.0**: `cdd_core_past_events`, `cdd_core_posts_by_blog_author`,
    `cdd_core_album_attachments`, convertidor puro `Cdd_Core_Content_Converter` +
    `Cdd_Core_Convert_Service` y `wp cdd-core migrate convert` (dry-run por defecto,
    `--apply`, idempotente, guard de producción): inicio (aside y cards → bloques
    dinámicos; `<picture>`/thumbs hechas a mano → biblioteca), galeria (mount JS → 3
    galerías Gutenberg por álbum con headings enlazados al término), comunidad (2 enlaces
    de ficha OWN-016, reversibles).
  - Dos bugs latentes cazados con regresión+fix: `event_modality` descartaba el copy
    publicado (select doc 03 → texto libre, OWN-007) y los `<source srcset>`/thumbs sin
    reescribir rompían las imágenes del Inicio.
  - QA: unit 105/105, wp-phpunit 60/60 (theme activo en el harness), phpcs limpio,
    stylelint verde, conversión aplicada en el entorno local, rutas y render verificados,
    `debug.log` limpio. Matriz § WU-07. QA 4 visual parcial; share/calendario/SEO → WU-08.

- **WU-08A — Comportamiento front: compartir, calendario, audio de mantras** (sesión
  2026-08-31, sin FABLE5 pegado; ADR 0021/0029/0033/0034/0038, OWN-007/012; TDD honesto:
  RED documentado en unit (5 E + 8 F) **y** wp-phpunit (2 E + 11 F) antes del primer archivo
  de comportamiento):
  - Reanudación verificada: preflight limpio + rerun de los gates (unit 105, phpcs,
    wp-phpunit 60, plugin 0.4.0 y theme 0.2.0 activos).
  - Plugin **0.5.0**: `Cdd_Core_Share_Extractor` (las 9 plantillas `<template>` publicadas,
    normalizadas igual que `share.js`, `{{SHARE_URL}}` intacto); meta editable
    `share_whatsapp`/`share_x`/`share_threads` en `event` y `post` (REST + saneo de texto
    plano multilínea); `cdd_core_event_calendar_payload()` como **fuente única** del diálogo
    y del `.ics` (la ruta ICS se refactorizó para leerla); importador escribe la meta al
    crear; `migrate convert` gana `practica` (mantras → `core/audio`) y la siembra add-only
    del copy de compartir con `--payload=<path>`.
  - Theme **0.3.0**: `share.js` portado literal y `calendar-dialog.js` (mitad restante de
    `calendar.js`); bloques `camino-del-dharma/evento-acciones` y `entrada-compartir`
    (13 en total), presentes en `single-event.html`, `single.html` y en la card vigente del
    listado; scripts encolados solo por el bloque que los usa; filtro `render_block` que
    devuelve el `aria-label` a los `core/audio` desde el `figcaption` (docs/19).
  - `migration/payload.json` regenerado: mismos `counts` y misma fuente (VERSION 1.0.35,
    commit `bfb6dc0`); **el único delta es el campo `share`** (verificado objeto a objeto).
  - QA: unit 119/119 (586 asserts), wp-phpunit 75/75 (524 asserts), phpcs limpio, stylelint
    verde, `php -l` OK. Conversión aplicada en el entorno local (4 items; 2.º apply = 0).
    Verificado en navegador real: ambos diálogos con el copy publicado carácter a carácter,
    enlaces de Google/Outlook idénticos al `.ics` servido, audio nativo sonando desde la
    biblioteca con nombre accesible, y **0 bloques inválidos** en el editor de `/practica`.
    Matriz § WU-08A. QA 4 visual completo y staging siguen `Unverified`.

## Riesgos y hallazgos activos

- Paridad repo↔Hostinger **verificada** (WU-06, delta 0). Re-verificar en el freeze pre-corte.
- `descargas/Resumen programa EVF.mp4` en la raíz, no trazado y fuera de la receta ZIP: se
  preserva intacto; clasificar en el inventario si aparece referenciado.
- `_config.yml` y `.nojekyll` son restos de GitHub Pages (desactivado 2026-07-28), fuera del
  ZIP; su retiro es una limpieza separada.
- `test.yml` y el análisis Sonar de plugin+theme quedan `Unverified` hasta que exista push (la
  rama es local por diseño del master prompt).
- El entorno local ya usa la estructura definitiva `/blog/%postname%` (aplicada por el
  importador). Contenido demo del install local («Sample Page», «Hello world!») convive con
  el contenido importado; el staging partirá limpio. En staging: `import --apply` →
  `seed` → `convert --payload=<path> --apply` (la conversión es parte del pipeline
  documentado; el `--payload` solo hace falta si algún objeto se importó antes de que el
  copy de compartir viajara — en una importación limpia la meta ya viene del importador).
- QA 4 visual solo parcial (escritorio home/eventos + breakpoint móvil); el pase completo
  (320px, zoom 200%, teclado, lector de pantalla) y staging quedan `Unverified`.
- Comportamiento pendiente para **WU-08B**: SEO dinámico (title/meta/OG/JSON-LD, noindex de
  `/author`, términos de álbum y tags), redirects del `.htaccess`, «Eliminar huérfanos»
  (OWN-015) y el pase completo de docs/19. Los diálogos y el audio ya están (WU-08A).
- **Delta abierto para el propietario (WU-08A):** el diálogo «Añadir al calendario» y el
  `.ics` de WordPress describen el **rango completo del evento** (Círculos: 3 sep → 25 oct
  exclusivo) con `SUMMARY` = título y `LOCATION` = `event_place`. El estático publicado
  describe solo la **sesión de bienvenida** (3 → 4 sep, «Curso … — sesión de bienvenida»,
  «Virtual (hora de Colombia)»). Es el modelo de dominio aceptado en WU-06; si el calendario
  debe seguir apuntando a la bienvenida hace falta un campo editorial nuevo. No se inventó
  ninguno.
- **ADR 0041 / OWN-018 (2026-08-31):** Contact Form 7 es elegible en el corte. La revisión
  legal **no** bloquea WU-09 ni el lanzamiento. WU-09 actualiza en WordPress solo los
  párrafos del formulario de `/privacidad`; el HTML estático no se toca.
- Deltas de copy registrados (matriz WU-07): fechas generadas, filas `Hora`/`Aporte` fuera
  del modelo, resumen de card vigente = intro del single, excerpt del listado = deck,
  tiempo de lectura de Sangha 6′ vs 8′, label «Preinscribirme», byline enlazada.

## Decisiones/asunciones usadas

- Autorización del propietario 2026-08-31: CF7 en el corte **sin** espera de asesoría legal
  (OWN-018, ADR 0041, FABLE5 v2.4). El disclaimer de `/privacidad` basta para lanzar. Copy
  WordPress del formulario = delta field-scoped; estático intacto.
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
- WU-07: 13 decisiones/sustituciones registradas en la matriz de validación § WU-07 (tarjeta
  compacta doc 03 §3, fechas generadas calibradas, CTA «Preinscribirme», deck como excerpt,
  byline enlazada, `<picture>`/thumbs no migran, headings de álbum enlazados, strings
  estructurales en plantillas, harness con theme activo). **Discrepancia doc 03 resuelta**:
  `event_modality` es texto libre (el copy publicado es descriptivo; OWN-007 > select doc 03).
- WU-05: (1) nombres de meta = doc 03 y `authors` sin prefijo (contrato ADR 0037 §6);
  (2) `event_signup_payment` boolean (el pago siempre vía `event_signup_url`);
  (3) `event_calendar_dates` (sesiones sueltas) añadido por el contrato del calendario
  publicado — el rango solo es fallback; el extractor WU-06 lo puebla para Círculos;
  (4) DESCRIPTION del `.ics` = excerpt editorial (el extractor trae el copy, el plugin no
  lo inventa); (5) «Eliminar huérfanos» (OWN-015) pospuesto a WU-08 (el `.ics` WordPress
  se genera bajo demanda, sin archivos); (6) guard REST con stash por request para
  publicar con autores en el mismo request. Detalle en la matriz § WU-05.

## Evidencia de validación

Ver `.audit/fase3-validation-matrix.md` § WU-08A. Estados: `Unverified`, `Pass (local)`,
`Pass`, `Fail`.

## Bloqueos

- Ninguno. Siguiente implementación: **WU-08B**, sesión aparte (Opus + FABLE5 §9.5 y §10
  solamente). WU-09 (CF7) no espera asesoría legal (ADR 0041 / OWN-018).

## Archivos cambiados en WU-08A

- Tests nuevos/ampliados (RED antes de la implementación): `tests/Unit/Theme_BehaviorTest.php`
  y `tests/WordPress/Share_MetaTest.php` (nuevos); ampliados `tests/Unit/Event_ExtractorTest.php`,
  `tests/Unit/Blog_ExtractorTest.php`, `tests/Unit/Content_ConverterTest.php`,
  `tests/WordPress/Event_QueriesTest.php`, `tests/WordPress/ThemeRenderTest.php`,
  `tests/WordPress/ConvertTest.php`, `tests/WordPress/ImporterTest.php`.
- Plugin `camino-del-dharma-core` 0.4.0 → **0.5.0**:
  `includes/migration/class-cdd-core-share-extractor.php` (nuevo), `includes/meta.php`
  (meta `share_*` + saneo), `includes/events.php` (`cdd_core_event_calendar_payload`, ruta
  `.ics` refactorizada para leerla), `includes/migration/class-cdd-core-event-extractor.php`,
  `class-cdd-core-blog-extractor.php`, `class-cdd-core-importer.php` (`share_meta`),
  `class-cdd-core-content-converter.php` (`convert_practica`),
  `class-cdd-core-convert-service.php` (paso `practica` + siembra de share),
  `class-cdd-core-cli.php` (`--payload` opcional en convert), bootstrap.
- Theme `camino-del-dharma` 0.2.0 → **0.3.0**: `assets/js/share.js` y
  `assets/js/calendar-dialog.js` (nuevos, porte literal), `assets/js/calendar-tooltips.js`
  (cabecera), `inc/blocks.php` (2 bloques, encolado condicional, filtro `render_block` del
  audio), `inc/class-camino-del-dharma-renderers.php` (acciones de evento, compartir de
  entrada, plantillas de mensaje), `templates/single-event.html`, `templates/single.html`,
  `style.css`.
- `migration/payload.json` regenerado (delta = campo `share`).
- Docs: `docs/03-wordpress-content-model.md`, `docs/contrato-migracion-static-wordpress.md`,
  `docs/matriz-migracion-static-wordpress.md`, `docs/migracion-static-wordpress.md`, READMEs
  de plugin y theme, `CLAUDE.md`, `AGENTS.md`, `CHANGELOG.md`,
  `.audit/fase3-validation-matrix.md`, este archivo.

## Archivos cambiados en la sesión de gobernanza (ADR 0041, 2026-08-31)

ADR 0041, OWN-018, FABLE5 v2.4, AGENTS/CLAUDE/reglas, backlog v1.21, contrato, matriz,
checklist, docs 17, operations, ledger, inventario, redirect-ledger, CHANGELOG Unreleased,
este archivo. Sin PHP, HTML estático ni plugin CF7 en esta sesión.

## Archivos cambiados en WU-07 (histórico de esa sesión)

- Tests nuevos (RED antes de la implementación): `tests/Unit/Theme_TemplatesTest.php`,
  `tests/Unit/Theme_FormatTest.php`, `tests/Unit/Content_ConverterTest.php`,
  `tests/WordPress/ThemeRenderTest.php`, `tests/WordPress/ConvertTest.php`; ampliados
  `tests/WordPress/Event_QueriesTest.php` (past events) y `Event_ModelTest.php` (modalidad
  texto libre). Harness: `tests/WordPress/bootstrap.php` + `wp-tests-config.php`
  (`WP_DEFAULT_THEME` = camino-del-dharma).
- Theme `camino-del-dharma` 0.1.0 → **0.2.0**: `templates/` (16), `parts/`, `patterns/`
  (header, footer, single-event-nav, blog-single-nav), `inc/`
  (`class-camino-del-dharma-format.php`, `class-camino-del-dharma-renderers.php`,
  `blocks.php`), `functions.php`, `theme.json` (fontFace + lightbox),
  `assets/css/main.css` (porte completo del estático a presets), `assets/js/main.js` +
  `calendar-tooltips.js`, `assets/fonts/`, `assets/images/logo.png`, `style.css`.
- Plugin `camino-del-dharma-core` 0.3.0 → **0.4.0**:
  `includes/migration/class-cdd-core-content-converter.php` y
  `class-cdd-core-convert-service.php` (nuevos), CLI `migrate convert`,
  `includes/events.php` (`cdd_core_past_events`), `includes/authors-guard.php`
  (`cdd_core_posts_by_blog_author`), `includes/taxonomies.php`
  (`cdd_core_album_attachments`), `includes/meta.php` (modalidad texto libre), bootstrap.
- `.stylelintrc.json` (`custom-property-pattern` acepta `--wp--*`)
- `docs/migracion-static-wordpress.md`, `docs/matriz-migracion-static-wordpress.md`,
  READMEs de plugin y theme, CLAUDE.md, AGENTS.md, `CHANGELOG.md`,
  `.audit/fase3-validation-matrix.md`, este archivo

## Último commit verificado

`6dffb0d` — implementación de WU-07 (QA local verde). Baseline visual del theme:
`d3b30f5` (docs/12 §8). Historial en `fase3-wordpress`: `5088e32` (WU-00) →
`bfb6dc0`/`54cd09f` (WU-01) → `11237a1` → `b9c9eb8` (WU-02) → `81d7547` → `fe33b96` (WU-03)
→ `36d368b` → `d3b30f5` (WU-04) → `196ef78` → `e8c52c9` (WU-05) → `c270a37` →
`e9f4234` (WU-06) → `044f7d6` → `6dffb0d` (WU-07).

## Estado del entorno local

Contenedores del proyecto `camino-del-dharma` levantados al cierre (`docker compose stop`
para pararlos). WordPress local: `http://localhost:8081`. Plugin `camino-del-dharma-core`
**activo** (v0.5.0, upgrade automático). Theme `camino-del-dharma` **activo** (v0.3.0,
vistas reales + comportamiento). Contenido importado (payload 1.0.35/`bfb6dc0`) **y
convertido** por `wp cdd-core migrate convert --payload=/repo/migration/payload.json --apply`
(inicio con bloques dinámicos y `<picture>` desenvueltos; galeria con 3 galerías nativas;
comunidad con enlaces OWN-016; practica con 2 `core/audio` nativos; copy de compartir
sembrado en el evento vigente y los 2 posts). Permalinks
`/blog/%postname%`; front page `inicio`, posts page `blog`. Contenido demo del install
(«Sample Page», «Hello world!») sigue presente; el staging partirá limpio. El harness
efímero `cdd-wp-phpunit` no deja contenedores ni volúmenes.

## Próxima acción exacta

WU-08A está cerrado (checkpoint alcanzado; sin push ni despliegue).

**WU-08B — SEO, redirects, OWN-015, a11y** (Claude 4.6 Opus, **sesión nueva**): pegar el
resume corto **y** FABLE5 **§9.5 + §10 únicamente** (no el archivo entero). Title/meta/OG/
JSON-LD, `noindex,follow`, redirects del `.htaccess`, «Eliminar huérfanos», pase docs/19.

WU-09 (Contact Form 7 + párrafos del formulario en `/privacidad`) queda después, sin gate
jurídico (ADR 0041 / OWN-018).

## Procedimiento de reanudación

```bash
git status --short
git branch --show-current   # esperar: fase3-wordpress
git log -5 --oneline
```

Leer este archivo, verificarlo contra Git (¿existe el theme con `theme.json`? ¿qué commits
hay en `fase3-wordpress`?), rerun del último gate QA relevante de la matriz, y continuar
desde «Próxima acción exacta». El estado del repositorio prevalece sobre la memoria de chat.
