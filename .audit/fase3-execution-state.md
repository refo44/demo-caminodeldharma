# Fase 3 — Estado de ejecución durable

Artefacto de continuidad exigido por FABLE5 v2.3 §12. Otra sesión debe poder retomar el trabajo
leyendo este archivo y verificándolo contra Git, sin historial de chat.

| | |
| --- | --- |
| **Última actualización** | 2026-08-31 (sesión WU-05) |
| **Fase** | Fase 3 — WordPress (iniciada) |
| **Work unit activo** | Ninguno — WU-00…WU-05 **cerrados**; checkpoint antes de WU-06 |
| **Rama** | `fase3-wordpress` (local, sin push) |
| **Commit baseline** | `d96bcbd` (`main`, árbol limpio, VERSION `1.0.35`) |
| **Tag de rollback** | `fase3-pre-reorg-v1.0.35` (anotado, local, apunta a `d96bcbd`) |
| **Paridad producción** | `Unverified` — el tag `v1.0.35` apunta a `364ed61`; `d96bcbd` añade 2 commits docs-only posteriores. No se verificó qué ZIP está desplegado en Hostinger (OWN-006: deuda de deploy/delta, no bloquea la extracción, que usará el `VERSION`/commit del repo). |

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

## Riesgos y hallazgos activos

- Paridad repo↔Hostinger no verificada (ver arriba). Registrar el delta al extraer (WU-06).
- `descargas/Resumen programa EVF.mp4` en la raíz, no trazado y fuera de la receta ZIP: se
  preserva intacto; clasificar en el inventario si aparece referenciado.
- `_config.yml` y `.nojekyll` son restos de GitHub Pages (desactivado 2026-07-28), fuera del
  ZIP; su retiro es una limpieza separada.
- `test.yml` y el análisis Sonar de plugin+theme quedan `Unverified` hasta que exista push (la
  rama es local por diseño del master prompt).
- El front local con el theme activo muestra el scaffold sin las vistas reales — esperado
  hasta WU-07+; la comparación visual contra el estático (QA 4) queda `Unverified`.
- El entorno local sigue con permalinks *plain*: la estructura definitiva
  (`/blog/%postname%`, sin barra final, ADR 0008) es ajuste de sitio que llega con el
  importador/settings (WU-06/07). Los tests de rutas la fijan por su cuenta; el QA HTTP
  entrante completo queda para el harness nivel 3 y staging.

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

## Archivos cambiados en la sesión actual (WU-05)

- Tests nuevos (RED antes de la implementación): `tests/Unit/Event_StatusTest.php`,
  `tests/Unit/Ics_GeneratorTest.php`, `tests/Unit/Calendar_DataTest.php`,
  `tests/Unit/Featured_Event_PolicyTest.php`, `tests/Unit/Authors_ListTest.php`,
  `tests/WordPress/Event_ModelTest.php`, `tests/WordPress/Event_RoutingTest.php`,
  `tests/WordPress/Event_QueriesTest.php`, `tests/WordPress/Blog_AuthorTest.php`,
  `tests/WordPress/Post_AuthorsRelationTest.php`, `tests/WordPress/Gallery_AlbumTest.php`
- Plugin `camino-del-dharma-core` 0.1.0 → **0.2.0**: `camino-del-dharma-core.php`
  (requires + wiring), `includes/class-cdd-core-event-status.php`,
  `includes/class-cdd-core-ics-generator.php`, `includes/class-cdd-core-calendar-data.php`,
  `includes/class-cdd-core-featured-event-policy.php`,
  `includes/class-cdd-core-authors-list.php`, `includes/post-types.php`,
  `includes/taxonomies.php`, `includes/meta.php`, `includes/authors-guard.php`,
  `includes/events.php`, `includes/routing.php`, `README.md`
- CLAUDE.md, AGENTS.md, `CHANGELOG.md`, `.audit/fase3-validation-matrix.md`, este archivo

## Último commit verificado

`e8c52c9` — implementación de WU-05 (QA local verde). El baseline de paridad visual
del `theme.json` sigue siendo `d3b30f5` (docs/12 §8). Historial en `fase3-wordpress`:
`5088e32` (WU-00) → `bfb6dc0`/`54cd09f` (WU-01) → `11237a1` → `b9c9eb8` (WU-02) →
`81d7547` → `fe33b96` (WU-03) → `36d368b` → `d3b30f5` (WU-04) → `196ef78` →
`e8c52c9` (WU-05).

## Estado del entorno local

Contenedores del proyecto `camino-del-dharma` levantados al cierre (`docker compose stop`
para pararlos). WordPress local: `http://localhost:8081` (puerto en `.env`). Plugin
`camino-del-dharma-core` **activo** (v0.2.0, upgrade versionado verificado:
`cdd_core_version` = 0.2.0). Theme `camino-del-dharma` **activo** (v0.1.0, block theme
verificado). El harness efímero `cdd-wp-phpunit` no deja contenedores ni volúmenes
(`down -v`).

## Próxima acción exacta

WU-05 está cerrado. **La sesión actual se detiene aquí** (checkpoint FABLE5 §12). La
siguiente sesión: rerun del preflight (§ Reanudación) y de los gates
(`./tools/php-lint.sh`, unit suite verde — ahora 44 tests —, `vendor/bin/phpcs` limpio,
`tools/run-phpunit-wp.sh` verde — ahora 34 tests —, plugin 0.2.0 y theme activos sin
warnings/fatals en el entorno local), luego implementar y validar **solo WU-06** —
extractor determinista de lectura desde `static/` (HTML/JSON/data-*), payload de migración
versionado con commit/`VERSION` de origen y claves `_source_key`/`_source_hash`,
importador WP-CLI en el plugin (`validate`/`plan`/`import`/`verify`, dry-run por defecto,
`--apply` explícito, idempotente, create-missing-only, guard de producción), comando
`seed` de medios reales (OWN-009-img) y reconciliación contra
`docs/conteos-reconciliacion-migracion.md` — actualizar este archivo y detenerse en el
checkpoint de WU-06. Recordatorios WU-06: comparar contra `https://caminodeldharma.org`
(OWN-007) y registrar el delta repo↔Hostinger; poblar `event_calendar_dates` de Círculos
desde el calendario publicado; traer el copy editorial de los `.ics` como excerpt.

## Procedimiento de reanudación

```bash
git status --short
git branch --show-current   # esperar: fase3-wordpress
git log -5 --oneline
```

Leer este archivo, verificarlo contra Git (¿existe el theme con `theme.json`? ¿qué commits
hay en `fase3-wordpress`?), rerun del último gate QA relevante de la matriz, y continuar
desde «Próxima acción exacta». El estado del repositorio prevalece sobre la memoria de chat.
