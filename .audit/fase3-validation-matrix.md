# Fase 3 — Matriz de validación

Evidencia QA por work unit (FABLE5 v2.4 §11). Estados permitidos: `Unverified`,
`Pass (local)`, `Pass`, `Fail`. `Pass (local)` nunca prueba comportamiento
PHP/Apache/HTTPS/mail de Hostinger.

Mapa de niveles: QA 1 = gate barato estático · QA 2 = unit + wp-phpunit ·
QA 3 = integración local · QA 4 = manual + staging (ver
`docs/guia-pruebas-plugin-theme-fse.md`, ADR 0038).

## WU-00 — Preflight y harness durable

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Árbol limpio en baseline | `git status --short` vacío en `main@d96bcbd` | Limpio | Pass (local) |
| Rama feature creada | `git branch --show-current` = `fase3-wordpress` | OK | Pass (local) |
| Tag rollback anotado | `git tag -l fase3*` = `fase3-pre-reorg-v1.0.35` → `d96bcbd` | OK | Pass (local) |
| Artefactos durables presentes | 4 archivos creados (estado, matriz, 2 runbooks) | OK | Pass (local) |
| Paridad Hostinger | No verificada en esta sesión | — | Unverified |

## WU-01 — Reorganización monorepo (raíz → `static/`)

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Solo superficie desplegable movida | `git ls-files static/` = 238 archivos, exactamente la receta ZIP; PDF OWN-002 excluido a `docs/archive/` | OK | Pass (local) |
| Renames preservados | commit `bfb6dc0`: 240 cambios, todos rename 100% | OK | Pass (local) |
| Nada perdido ni duplicado | 532 archivos trazados antes y después del movimiento | OK | Pass (local) |
| `npm run lint:css` verde con nuevas rutas | ejecutado 2026-08-31, exit 0 | OK | Pass (local) |
| `npm run build:css` regenera `static/assets/css/main.min.css` | ejecutado; md5 idéntico antes/después (build estable, sin drift) | OK | Pass (local) |
| `git diff --check` sin whitespace errors | ejecutado, limpio | OK | Pass (local) |
| Docs/README actualizados a layout Fase 3 | README, CLAUDE.md, AGENTS.md, regla cursor, docs/13, ledger, CHANGELOG | OK | Pass (local) |
| ZIP empaquetable desde `static/` | `zip -sf` dry-run desde `static/`: 277 entradas, sin desplegar | OK | Pass (local) |
| URLs públicas sin cambio | renames de repo; contenido HTML intacto; **sin deploy en esta sesión** | Sin cambio | Pass (local) |
| Comportamiento en Hostinger tras próximo deploy | requiere despliegue real | — | Unverified |

## WU-02 — Entorno Docker local (sesión separada, ADR 0023)

Ejecutado 2026-08-31 sobre `fase3-wordpress` (Docker 29.6.2, Compose v5.3.1).

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun gate WU-01 al reanudar | `npm run lint:css` exit 0; `git diff --check` limpio | OK | Pass (local) |
| Fail-fast sin `.env` | `docker compose config` sin `.env` → exit 1 con mensaje `define MARIADB_DATABASE en .env` | OK | Pass (local) |
| `.env` gitignored, `.env.example` versionado | `git check-ignore -v .env` → regla `/.env`; plantilla creada | OK | Pass (local) |
| 3 servicios levantan; db healthy | `docker compose up -d`; `ps` → db `(healthy)`, wordpress `Up`, wpcli exit 0 (se usa vía `run --rm`) | OK | Pass (local) |
| Puerto solo localhost, parametrizado | binding `127.0.0.1:8081->80` (8080 ocupado por otro proyecto → `WORDPRESS_PORT=8081` en `.env`, default 8080 en compose) | OK | Pass (local) |
| Paridad de versiones Hostinger | local PHP 8.3.33 / MariaDB 11.8.9 vs Hostinger PHP 8.3.30 / MariaDB 11.8.8 — misma serie menor, deriva de patch | OK | Pass (local) |
| Core instalado vía wpcli | `wp core install` OK; WordPress 7.1; admin solo en volumen local + `.env` | OK | Pass (local) |
| `WP_ENVIRONMENT_TYPE=local` en wpcli y web | `wp eval wp_get_environment_type()` = `local`; `WORDPRESS_CONFIG_EXTRA` presente en env del servicio web | OK | Pass (local) |
| Debug activo sin warnings | `WP_DEBUG=true`, `WP_DEBUG_LOG=true`, `WP_DEBUG_DISPLAY=false` (tras corregir gotcha `WORDPRESS_DEBUG`, ver playbook) | OK | Pass (local) |
| `debug.log` vacío tras navegación | GET `/`, `/wp-login.php`, `/?p=1`, ruta inexistente → `debug.log` no existe | OK | Pass (local) |
| Bind-mounts solo código propio | theme y plugin (README-only) visibles en `/var/www/html/wp-content/...`; core y BD en volúmenes | OK | Pass (local) |
| `wpcli` como www-data | `id` en wpcli → `uid=33 gid=33` | OK | Pass (local) |
| Sin cookies en front anónimo | `curl -sI /` sin `Set-Cookie` | OK | Pass (local) |
| Comportamiento PHP/Apache/HTTPS/mail Hostinger | requiere staging real | — | Unverified |

Observación (no fallo de WU-02): `GET /ruta-inexistente` → 301 a la forma con barra final
(redirect canónico por defecto de WordPress con permalinks *plain*). La política canónica sin
barra final (ADR 0008) y las rutas reales se implementan y prueban en WU-05/WU-08.

Gotcha documentado: `define('WP_DEBUG', …)` dentro de `WORDPRESS_CONFIG_EXTRA` es no-op
(la plantilla de la imagen lo define antes desde `WORDPRESS_DEBUG`). Evidencia del primer
intento: warning «Constant WP_DEBUG already defined» + `WP_DEBUG=false`. Corregido con
`WORDPRESS_DEBUG: 1` en ambos servicios PHP; ver `docs/docker-wordpress-playbook.md`.

## WU-03 — Scaffold del plugin y kit de calidad TDD (sesión separada tras WU-02, ADR 0023)

Ejecutado 2026-08-31 sobre `fase3-wordpress`. Sin PHP/Composer nativos en el host: todos los
comandos PHP corren vía Docker (`composer:2` para resolver, `wordpress:cli-php8.3` para
ejecutar), como prevé la guía (ADR 0038).

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun gate WU-02 al reanudar | `docker compose --env-file /dev/null config` → error de interpolación fail-fast; con `.env`: `up -d`, db `(healthy)`, `wp eval wp_get_environment_type()` = `local`, `wp core version` = 7.1 | OK | Pass (local) |
| TDD honesto: RED antes del primer PHP | Suite unitaria ejecutada **antes** de crear `camino-del-dharma-core.php` → fatal `Failed to open stream` en el bootstrap (evidencia de rojo) | OK | Pass (local) |
| Kit Composer resuelve pineado a PHP 8.3 | `composer install` (imagen `composer:2`, `platform.php` 8.3.30): PHPUnit 9.6.36, **wp-phpunit 7.1.0 = WordPress del compose**, polyfills 4.0.0, WPCS 3.4.1; `composer validate` OK | OK | Pass (local) |
| Nivel 1 verde | `php vendor/bin/phpunit -c phpunit.xml.dist` → OK (2 tests, 3 assertions), sin boot de WordPress | OK | Pass (local) |
| Nivel 2 verde en harness efímero | `tools/run-phpunit-wp.sh`: compose `-p cdd-wp-phpunit` (env desechable `tools/wp-tests.env`, puerto 8083), instala WP fresco en tablas `wptests_`, OK (2 tests, 3 assertions), `trap down -v` al salir; `docker compose -p cdd-wp-phpunit ps -a` vacío después | OK | Pass (local) |
| Harness no toca el volumen del desarrollador | Proyecto compose separado con volúmenes propios (`cdd-wp-phpunit_*`), destruidos con `-v`; entorno `camino-del-dharma` intacto | OK | Pass (local) |
| QA 1: `php -l` | `tools/php-lint.sh` (fallback Docker) sobre plugin + tests, guard anti-vacuo si no hay `.php` | OK | Pass (local) |
| QA 1: PHPCS/WPCS (ADR 0027) | `vendor/bin/phpcs` (WordPress-Extra, prefijo `cdd_core`, text domains propios) → 6 archivos, 0 errores tras ignores justificados en bootstraps de test | OK | Pass (local) |
| QA 1: audit de dependencias | `composer audit --locked` → sin advisories | OK | Pass (local) |
| QA 1: `git diff --check` | Limpio | OK | Pass (local) |
| Plugin activa sin warnings/fatals | `wp plugin activate camino-del-dharma-core` → activo v0.1.0; `debug.log` no existe tras GET `/` | OK | Pass (local) |
| Override compose del harness parsea | `docker compose -f docker-compose.yml -f docker-compose.wp-tests.yml --env-file tools/wp-tests.env config --quiet` | OK | Pass (local) |
| `test.yml` corre en GitHub Actions | Requiere push (esta rama es local, sin push) | — | Unverified |
| Sonar analiza el plugin nuevo | Automatic Analysis lee alcance desde el default branch; sin push no hay análisis | — | Unverified |

Decisión registrada: el prefijo corto `cdd` (ADR 0027 daba `cdd_` o `camino_del_dharma_`)
lo rechaza el sniff de WPCS por tener 3 caracteres; el plugin usa `cdd_core`
(`CDD_CORE_*`) y el theme usará `camino_del_dharma`. Ver `phpcs.xml.dist`.

Gotcha documentado: con `working_dir: /repo` en el override, `wp core version` debe
invocarse con `--path=/var/www/html` (WP-CLI busca la instalación desde el cwd); el primer
intento del harness falló por eso y quedó corregido en `tools/run-phpunit-wp.sh`.

## WU-04 — Scaffold del theme FSE y baseline de tokens visuales (sesión separada tras WU-03)

Ejecutado 2026-08-31 sobre `fase3-wordpress` (reanudación: preflight + gates WU-02 y WU-03
rerun en esta sesión antes de tocar nada). Sin PHP/Composer nativos: comandos PHP vía Docker.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun preflight al reanudar | `git status --short` vacío; rama `fase3-wordpress`; historial = estado durable (`36d368b`); `VERSION` 1.0.35 | OK | Pass (local) |
| Rerun gate WU-02 | compose fail-fast sin `.env` (error interpolación); `ps` → db `(healthy)`; `wp eval wp_get_environment_type()` = `local` | OK | Pass (local) |
| Rerun gate WU-03 | `tools/php-lint.sh` OK; unit suite OK (2 tests); `vendor/bin/phpcs` limpio (vía Docker) | OK | Pass (local) |
| TDD honesto: RED antes del primer archivo del theme | Unit: 15 tests, **12 failures** (theme.json/style.css/templates ausentes). wp-phpunit: 4 tests, **2 failures** (`theme_no_stylesheet`, `is_block_theme()` false) — ambos ejecutados antes de crear archivo alguno del theme | OK | Pass (local) |
| Nivel 1 verde tras scaffold | `phpunit -c phpunit.xml.dist` → OK (15 tests, 117 assertions) | OK | Pass (local) |
| Nivel 2 verde en harness efímero | `tools/run-phpunit-wp.sh` → OK (4 tests, 7 assertions): `wp_get_theme()` sin errores, `is_block_theme()` true, text domain correcto | OK | Pass (local) |
| Tokens = estático (extracción programática) | `Theme_TokensTest` parsea el `:root` de `static/assets/css/main.css` y compara paleta (6 brand + text-on-dark), indirecciones semánticas (11 roles), 3 familias tipográficas, 5 espaciados, `contentSize`/`wideSize`, ritmo y line-heights — igualdad exacta | OK | Pass (local) |
| Política de color paleta-only | `settings.color.custom/customGradient/defaultPalette/defaultGradients` = false, asertado por test (docs/12 §8) | OK | Pass (local) |
| Sin plantillas PHP clásicas (ADR 0029) | Test `test_no_classic_view_templates_exist` (glob `front-page.php`, `page*.php`, `single*.php`, `archive*.php`, `index.php`) → vacío | OK | Pass (local) |
| QA 1: `php -l` | `tools/php-lint.sh` (ahora incluye `functions.php` del theme) | OK | Pass (local) |
| QA 1: PHPCS/WPCS | `vendor/bin/phpcs` → 0 errores (theme con prefijo `camino_del_dharma`, text domain `camino-del-dharma`) | OK | Pass (local) |
| QA 1: Stylelint ambos árboles CSS | `npm run lint:css` con glob ampliado al theme (guía §7) → exit 0 | OK | Pass (local) |
| QA 1: `git diff --check` | Limpio | OK | Pass (local) |
| QA 3: theme activa sin warnings/fatals | `wp theme activate camino-del-dharma` → Success, activo v0.1.0; `wp_is_block_theme()` = true en el entorno local | OK | Pass (local) |
| QA 3: navegación representativa limpia | GET `/` 200 (presets `--wp--preset--color--brand-1: #8c2b3d`, familia body y `main.css` encolado presentes), `/?p=1` 200, `/wp-login.php` 200; `debug.log` no existe; sin `Set-Cookie` anónimo | OK | Pass (local) |
| Ruta inexistente | 301 canónico de permalinks *plain* (observación heredada de WU-02; rutas reales y 404 se implementan en WU-05/WU-08) | — | Unverified |
| Paridad visual con el estático (QA 4) | El scaffold no renderiza aún las vistas reales; comparación visual llega con plantillas (WU-07+) | — | Unverified |
| `test.yml` / Sonar sobre el theme | Requiere push (rama local por diseño) | — | Unverified |

Decisiones registradas: (1) `fontSizes` no se definen en el baseline — el `:root` del
estático no tiene tokens de tamaño tipográfico; se añadirán cuando las plantillas los
necesiten, sin inventar escala. (2) Los woff2 (MarloweEscapade/Fjalla One/Inter) no se
copian aún al theme: los tokens son las pilas de familia; `fontFace` llega con las
plantillas reales (WU-07+). (3) `parts/header|footer.html` son placeholders mínimos
(bloque site-title) para registrar `templateParts`; el markup real es de WU-07+.
(4) `register_nav_menus()` (docs/12 §11.1) se pospone: un block theme gestiona menús con
el bloque Navigation; se revisará al construir `parts/header.html` real.

## WU-05 — Modelos de dominio, routing y datos de calendario/ICS (sesión separada tras WU-04)

Ejecutado 2026-08-31 sobre `fase3-wordpress` (reanudación: preflight + gates WU-03 y WU-04
rerun antes de tocar nada). Sin PHP/Composer nativos: comandos PHP vía Docker.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun preflight al reanudar | `git status --short` vacío; rama `fase3-wordpress`; historial = estado durable (`196ef78`); `VERSION` 1.0.35 | OK | Pass (local) |
| Rerun gate WU-03 | `tools/php-lint.sh` OK; unit suite OK (15 tests, 117 assertions); `vendor/bin/phpcs` limpio (Docker) | OK | Pass (local) |
| Rerun gate WU-04 | `tools/run-phpunit-wp.sh` OK (4 tests); theme activo como block theme (`wp_is_block_theme()` = true), `wp_get_environment_type()` = `local`, `debug.log` inexistente, GET `/` 200 sin warnings | OK | Pass (local) |
| TDD honesto: RED antes del primer archivo de dominio | Unit: 44 tests, **29 errors** (clases de política ausentes). wp-phpunit: 34 tests, **9 errors + 18 failures** (CPTs/meta/rutas/guards ausentes) — ambos ejecutados antes del primer archivo de `includes/` | OK | Pass (local) |
| Nivel 1 verde tras implementación | `phpunit -c phpunit.xml.dist` → OK (**44 tests, 177 assertions**): política de estado OWN-013 (día final vigente, `America/Bogota` no UTC, cancelado inmutable, extensión revierte), generador ICS (paridad con los `.ics` de producción: PRODID, DTEND exclusivo, escaping RFC 5545, CRLF, omisión honesta), datos de calendario (celdas de evento, lunes de práctica, colisión lunes+evento, mes del próximo vigente), selección del destacado del Inicio (5 reglas doc 03 §3), normalización `authors` | OK | Pass (local) |
| Nivel 2 verde en harness efímero | `tools/run-phpunit-wp.sh` → OK (**34 tests, 258 assertions**) | OK | Pass (local) |
| CPT `event` + rutas ADR 0035/0008 | Registro público, archive `eventos`, rewrite sin `with_front`; `/eventos/{slug}` resuelve desde la ruta entrante (`go_to`), permalink **sin barra final**; `/eventos` = archive del CPT | OK | Pass (local) |
| Taxonomías `event_type`/`event_city` sin archivo público (ADR 0022) | `public`/`publicly_queryable` = false, `rewrite` = false, `show_ui`/`show_in_rest` = true, jerárquica/plana según doc 03 §4 | OK | Pass (local) |
| Meta de evento saneado (doc 03 §3) | Fechas Y-m-d reales o vacío; enums modalidad/estado; URL de inscripción saneada; `event_calendar_dates` filtra fechas inválidas; valores válidos sobreviven `wp_insert_post` | OK | Pass (local) |
| Estado a tiempo de request (OWN-013) | `cdd_core_event_status` sobre meta real: fechas mandan, flag guardado solo gana como `cancelado`; split vigentes/pasados ignora flags obsoletos | OK | Pass (local) |
| Destacado del Inicio | `cdd_core_featured_home_event`: destacado pasado ignorado, vigente más próximo como fallback, null sin vigentes | OK | Pass (local) |
| Datos de calendario | `cdd_core_calendar_month_data`: sesiones explícitas (`event_calendar_dates`, contrato del calendario estático) o rango; permalink por celda; lunes de práctica | OK | Pass (local) |
| Ruta y respuesta `.ics` (OWN-009/OWN-012) | `/eventos/ical/{slug}.ics` → query var propia (regla `top`); respuesta: 200 `text/calendar` + `X-Robots-Tag: noindex, nofollow` con VEVENT generado para vigente; **410** finalizado; 404 desconocido | OK | Pass (local) |
| CPT `blog_author` (ADR 0037) | `query_var` = `blog_author` (nunca `author`), rewrite `author` sin front, caps propias `blog_author(s)` + `map_meta_cap`; `/author/{slug}` single y `/author` archive resuelven; grant de caps a administrator/editor verificado | OK | Pass (local) |
| Archivos de usuario WP apagados (ADR 0037 §5) | `/?author={id}` → 404 real; ninguna regla rewrite apunta a `author_name=` | OK | Pass (local) |
| Relación `authors` + guard de publicación (ADR 0037 §6–§7) | Meta ordenado/único/solo fichas publicadas; publicar sin autor → draft (programático) / error 400 (REST); borrador sin autor OK; post publicado no puede quedar en cero (update y delete rechazados); activación no despublica legados; REST publish con meta en el mismo request OK; buscador REST solo fichas publicadas | OK | Pass (local) |
| `gallery_album` (ADR 0036) | Taxonomía plana sobre attachments, rewrite `galeria` sin front; `/galeria` sigue siendo la Page (no robada); `/galeria/{slug}` resuelve el término y lista sus attachments (fix `inherit` en `pre_get_posts`) | OK | Pass (local) |
| QA 1: `php -l` | `tools/php-lint.sh` (plugin con `includes/`, theme, tests) | OK | Pass (local) |
| QA 1: PHPCS/WPCS | `vendor/bin/phpcs` → 0 errores (32 archivos) tras `phpcbf` de alineación | OK | Pass (local) |
| QA 1: audit de dependencias | `composer audit --locked` → sin advisories | OK | Pass (local) |
| QA 1: `git diff --check` | Limpio | OK | Pass (local) |
| QA 3: plugin 0.2.0 en el entorno local | Upgrade versionado (`cdd_core_maybe_upgrade`): `cdd_core_version` = 0.2.0, flush en upgrade (no por request); `wp post-type list` → `event`/`blog_author` públicos; `event_type`/`event_city` no públicos; `gallery_album` público; GET `/` 200 sin warnings; `debug.log` inexistente | OK | Pass (local) |
| QA 3: HTTP real de rutas bonitas (curl 200/404/410, redirects) | El entorno local sigue con permalinks *plain* (la estructura `/blog/%postname%` es ajuste de sitio de WU-06/07); verificación HTTP entrante completa = harness nivel 3 / staging | — | Unverified |
| noindex de `/author`, álbumes y tags; JSON-LD; sitemap | Superficie SEO de WU-08 (los inputs de dominio ya existen) | — | Unverified |
| `test.yml` / Sonar sobre `includes/` | Requiere push (rama local por diseño) | — | Unverified |

Decisiones registradas (WU-05):

1. **Nombres de meta = doc 03** (`event_date`, `event_end`, `event_place`, `event_modality`,
   `event_status`, `event_featured`, `event_signup_url`, `event_signup_payment`) y meta del
   post `authors` **sin prefijo** — es el contrato literal de ADR 0037 §6 y del modelo de
   contenido; el prefijo `cdd_core` aplica a funciones/clases/hooks/opciones (la opción es
   `cdd_core_version`). `event_name`/`event_description` no existen como meta: título y
   contenido nativos (doc 03 §3).
2. **`event_signup_payment` es boolean** (doc 03 lo dejaba «boolean o url»): el pago siempre
   redirige vía `event_signup_url`; una segunda URL sería redundante.
3. **`event_calendar_dates` (array Y-m-d, opcional)**: el calendario publicado marca días de
   sesión sueltos (Círculos: 3, 10, 15, 17, 22, 24, 29 sep), no un rango contiguo — el rango
   `event_date..event_end` solo es el fallback. Campo derivado del contrato de producción
   (ADR 0034); el extractor de WU-06 debe poblarlo para Círculos.
4. **DESCRIPTION del `.ics` = excerpt editorial** (omitida si no hay): los `.ics` vivos llevan
   copy editorial por evento; el extractor WU-06 trae ese copy, el plugin no lo inventa.
   ORGANIZER = comunidad + `caminodeldharma1@gmail.com` (paridad con producción).
5. **Guard REST del publish** (`rest_pre_insert_post` + stash por request): REST aplica el
   meta después del insert; sin el stash, publicar con autores en el mismo request fallaría.
   Path programático: demote a draft; path REST: error 400 explícito.
6. **Archivos de usuario WP**: filtro `author_rewrite_rules` → vacío + filtro `request`
   (`author`/`author_name` → `error=404`). Sin tocar el query var `blog_author`.
7. **Herramienta «Eliminar huérfanos» (OWN-015) pospuesta a WU-08**: el `.ics` de WordPress
   se genera bajo demanda y no escribe archivos — el único huérfano posible es el legado del
   estático, que se resuelve en el corte; la pantalla wp-admin llega con la capa de admin.
8. Gotcha wp-phpunit documentado en los tests: `tear_down` desregistra **todo** el meta
   registrado (solo sobreviven los hooks de sanitización por el backup de hooks) y
   `register_post_type` solo añade permastructs si hay estructura de permalinks al
   registrarse — los tests de rutas re-registran los objetos tras `set_permalink_structure`.

## WU-06 — Extractor, payload, importador WP-CLI y reconciliación (sesión separada tras WU-05)

Ejecutado 2026-08-31 sobre `fase3-wordpress` (reanudación: preflight + gates WU-03/04/05
rerun antes de tocar nada). Sin PHP/Composer nativos: comandos PHP vía Docker.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun preflight al reanudar | `git status --short` vacío; rama `fase3-wordpress`; historial = estado durable (`c270a37`); `VERSION` 1.0.35 | OK | Pass (local) |
| Rerun gates WU-03/04/05 | php-lint OK; unit 44/44; phpcs limpio; `run-phpunit-wp.sh` 34/34; plugin 0.2.0 y theme activos; front 200 sin warnings | OK | Pass (local) |
| Hallazgo al reanudar: `debug.log` con 1 warning | `wp_update_themes()` sin salida de red a WordPress.org desde el contenedor — ruido del entorno, no código propio; log limpiado | OK (no bloquea) | Pass (local) |
| TDD honesto: RED antes del primer archivo | Unit: 74 tests, **30 errors** (clases de extracción ausentes). wp-phpunit: **8 errors** (`Cdd_Core_Importer` ausente) — ambos antes de `includes/migration/` | OK | Pass (local) |
| Nivel 1 verde (extractores sobre archivos reales) | `phpunit` → OK (**74 tests, 290 assertions**): fechas en español de producción, 10 eventos con slugs ADR 0035 (precedencia JSON-LD → texto), Círculos con cronograma (10 sesiones; subset sep = calendario publicado) y excerpt = descripción `.ics` de producción, blog con bylines/fechas/hero→thumbnail, galería 3 álbumes + 35 imágenes sin `galeria-04` (OWN-001), páginas con URLs raíz-relativas, inventario de medios (thumbs/`.DS_Store`/pdf/ics excluidos; huérfanas ocultas OWN-003), payload con claves/hashes/counts y JSON determinista | OK | Pass (local) |
| Payload real generado y determinista | `tools/extract-payload.sh` (lectura pura de `static/`, commit fuente = último commit que tocó `static/` = `bfb6dc0`, VERSION 1.0.35): dos ejecuciones → MD5 idéntico. Counts: pages 11 · events 10 · posts 2 · blog_authors 2 · albums 3 · gallery_images 35 · media 81 (71 públicas + 10 ocultas; 2 audio) · embeds 5 | OK | Pass (local) |
| Nivel 2 verde (importador) | `run-phpunit-wp.sh` → OK (**43 tests, 313 assertions**): validate rechaza archivos ausentes/autores desconocidos; payload real valida limpio contra `static/`; dry-run no escribe; apply crea fichas/medios (alt de producción, término de álbum, posición)/eventos (meta, términos no públicos, cartel como featured)/posts (relación `authors` ordenada)/páginas (jerarquía + URLs de medios reescritas a la biblioteca); idempotente (2.º apply crea 0); una edición wp-admin sobrevive re-import; evento con fecha futura importa `publish` (no `future`); settings (front page, posts page, permalinks ADR 0008) con flush **con** permastructs re-registrados; guard de producción exige `--confirm-production` + `--backup-evidence`; verify reconcilia | OK | Pass (local) |
| QA 1 | `php -l` OK; PHPCS **0 errores / 0 warnings**; `composer audit --locked` sin advisories; `git diff --check` limpio | OK | Pass (local) |
| QA 2/3: pipeline real contra el entorno local | Mounts RO `migration/` + `static/` en wpcli. `wp cdd-core migrate validate` → válido; `import` (dry) → plan 109 create; `import --apply` → 2+81+3+10+2+11 creados, 35 asignaciones de álbum, settings aplicados; `verify` → 0 missing, 6/6 colecciones reconcilian; 2.º `--apply` → 0 created / 109 skipped | OK | Pass (local) |
| Bugs cazados por el QA (test de regresión + fix) | (1) Evento vigente con `event_date` futura quedaba `future` (invisible) — fix: sin `post_date` de evento; recreado vía create-missing-only (borrado puntual + re-import → created 1/skipped 9). (2) El flush del importador corría sin permastructs de CPT (proceso CLI arrancado con permalinks *plain*) → rutas CPT 404 — fix: re-registro de dominio antes del flush; el env se corrigió con `wp rewrite flush --hard` único (semántica de upgrade) | OK | Pass (local) |
| QA 3: conteos reconcilian (baseline `docs/conteos-reconciliacion-migracion.md`) | pages 12 = 11 + «Sample Page» preexistente del install; events 10/10 (todas publish); posts 3 = 2 + «Hello world!» preexistente; blog_authors 2/2; attachments 81/81; álbumes 3/3; event_type 7 términos / event_city 5 (no públicos). Mismatches explicados = contenido demo del install local, no del payload; no existirá en el staging limpio | OK | Pass (local) |
| QA 3: rutas HTTP entrantes (curl) | 200: `/`, 9 pages, `/eventos`, 10 singles probados (3+2 muestreados), `/galeria/{general,2023,2021}`, `/author` + 2 fichas, `/blog` + 2 posts, `.ics` vigente. **410**: `.ics` finalizado. **404 real**: ruta inexistente y `/?author=1`. **301** `/eventos/` → `/eventos` (sin barra final, ADR 0008) | OK | Pass (local) |
| QA 3: `.ics` generado con paridad | Cabeceras `text/calendar` + `Content-Disposition` + `X-Robots-Tag: noindex, nofollow`; PRODID de producción; DTEND exclusivo (fin 2026-10-24 → 20261025); DESCRIPTION = copy editorial extraído (excerpt); host del UID/URL = entorno local (correcto: URLs del sitio que responde) | OK | Pass (local) |
| QA 3: higiene | `debug.log` inexistente tras el pipeline y la navegación; sin `Set-Cookie` anónimo; front 200 sin warnings | OK | Pass (local) |
| OWN-006/OWN-007: delta repo↔producción publicada | `curl` + `diff` byte a byte contra `https://caminodeldharma.org`: **17/17 superficies idénticas** (10 páginas, 3 singles de evento, 2 posts, 2 páginas secundarias) + `sitemap.xml` + `.ics` de Círculos. El ZIP desplegado corresponde a `VERSION` 1.0.35: **delta = 0**; la extracción usa el mismo contenido que ve el público | OK | Pass (local) |
| Render completo de las vistas | El theme sigue siendo el scaffold WU-04 (query loop mínimo): el contenido importado se almacena íntegro pero no se pinta entero — plantillas reales en WU-07 | — | Unverified |
| CI/Sonar sobre `includes/migration/` | Requiere push (rama local por diseño) | — | Unverified |

Decisiones registradas (WU-06):

1. **Fuente de fechas por precedencia**: JSON-LD ya publicado (single > listado) → texto español
   de la card (`Cdd_Core_Spanish_Date`). Los slugs de cards sin enlace se resuelven por tabla
   cartel→slug (constante del extractor, valores = ADR 0035; nunca se inventan).
2. **`event_calendar_dates` de Círculos = cronograma del single** (10 sesiones sep–oct); el test
   asegura que el subset de septiembre coincide exactamente con el calendario publicado.
3. **Excerpt del evento** = descripción del control de calendario (con `{{EVENT_URL}}` resuelto)
   cuando existe (paridad `.ics`), si no el lead de la card. **Hero del blog → featured image**
   (fuera del contenido). **Contenido**: single filtrado > card filtrada (sin chrome).
4. **Contenido importado como bloque `wp:html`** con URLs de medios reescritas a la biblioteca:
   fiel al copy publicado, editable, y la conversión a bloques reales queda para WU-07 (si exige
   tocar contenido importado, será update con force explícito de campo o edición wp-admin —
   el hash `_cdd_source_hash` delata qué sigue intacto).
5. **Página `blog` se importa con contenido vacío** (es la posts page; el listado lo hace la query).
6. **`seed`**: `wp cdd-core seed` = solo colección media (nombre aprobado OWN-009-img); `migrate
   import` incluye el mismo paso. Sin marcador de fixture, sin teardown. Huérfanas = attachments
   sin adjuntar ni referenciar (OWN-003). Favicons/OG quedan fuera del seed (Site Icon es ajuste
   de WU-07/08); `og-default.jpg` sí se siembra (referenciada vía meta).
7. **Settings del import**: front page `inicio`, posts page `blog`, permalinks `/blog/%postname%`
   (árbol docs/11, sin barra final). Solo en `--apply`.
8. **Mounts RO** `./migration` y `./static` en el servicio wpcli (docker-compose.yml): el
   importador lee, nunca escribe, la fuente.
9. La sonda de paridad live usó red de solo lectura (GET públicos a `caminodeldharma.org`);
   ninguna suite de tests depende de red.

## WU-07 — Pages, posts, autores, media, plantillas FSE y galería (sesión separada tras WU-06)

Ejecutado 2026-08-31 sobre `fase3-wordpress` (reanudación: preflight + rerun de todos los
gates WU-02…WU-06 antes de tocar nada). Sin PHP/Composer nativos: comandos PHP vía Docker.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun preflight al reanudar | `git status --short` vacío; rama `fase3-wordpress`; historial = estado durable (`044f7d6`); `VERSION` 1.0.35 | OK | Pass (local) |
| Rerun gates WU-03…WU-06 | php-lint OK; unit 74/74; phpcs limpio; `run-phpunit-wp.sh` 43/43; plugin 0.3.0 y theme activos; `wp cdd-core migrate verify` → 0 missing | OK | Pass (local) |
| TDD honesto: RED antes del primer archivo de vistas | Unit: 104 tests, **15 errors + 14 failures** (plantillas/formato/convertidor ausentes). wp-phpunit: 60 tests, **13 errors + 4 failures** (bloques/renderers/servicio ausentes); los 43 preexistentes siguieron verdes con el theme activo en el harness | OK | Pass (local) |
| Nivel 1 verde | `phpunit` → OK (**105 tests, 516 assertions**): contrato de las 16 plantillas (parts, `main#main`, post-content, sin `page-eventos`, sin URLs absolutas), fontFace con archivos presentes, lightbox nativo habilitado, CSS solo presets (sin `:root` ni tokens legados), contrato DOM del nav (ids de main.js), formato español calibrado contra el copy publicado (fechas, «Por A y B», tiempo de lectura), convertidor sobre el payload real (aside→bloque, cards→bloque, `<picture>` unwrap, thumbs remapeadas, galería por álbum, enlaces OWN-016 reversibles, idempotencia) | OK | Pass (local) |
| Nivel 2 verde | `run-phpunit-wp.sh` → OK (**60 tests, 435 assertions**): 11 bloques registrados; **calendario = markup publicado byte a byte** (grid septiembre 2026 de `static/eventos/index.html`, normalizado); listado vigentes/finalizados (agrupación por año desc, badge, tarjeta compacta, sin CTA en finalizados); aside destacado + estado vacío; tipo/meta/CTA de single; byline ADR 0037 enlazada + bio + tiempo de lectura; listados de blog; ficha de autor solo con entradas de la relación `authors`; galería nativa por término; resolución de plantillas; patterns header/footer con URLs generadas; `convert` dry-run/apply/idempotente/guard de producción; `cdd_core_past_events` orden correcto | OK | Pass (local) |
| QA 1 | `php -l` OK; PHPCS **0 errores / 0 warnings**; `npm run lint:css` verde en ambos árboles (`custom-property-pattern` ampliado a nombres `--wp--*`); `git diff --check` limpio; sin secretos | OK | Pass (local) |
| QA 3: conversión aplicada en el entorno local | `wp cdd-core migrate convert` dry-run → pending 3; `--apply` → converted `inicio`,`galeria`,`comunidad`; 2.º `--apply` → 0 (idempotente). Contenido: aside y cards dinámicos, 3 galerías nativas con IDs reales de la biblioteca (25/5/5), 2 enlaces OWN-016 | OK | Pass (local) |
| QA 3: rutas HTTP entrantes (curl) | **200**: `/`, 8 pages, `/practica/videos`, `/practica/meditacion-semanal-en-linea`, `/eventos`, singles muestreados (vigente + 2 finalizados), `/galeria` + 2 términos, `/blog` + single, `/author` + 2 fichas, `.ics` vigente. **410** `.ics` finalizado. **404 real** ruta inexistente. **301** `/eventos/` → `/eventos` | OK | Pass (local) |
| QA 3: render de las vistas | Home: aside dinámico con «Septiembre – octubre 2026<br>Bogotá y Cali» (idéntico a producción) + cards del blog; `/eventos`: título «Septiembre 2026», grid con 7 días de evento + lunes de práctica, secciones Próximos/Realizados, compactas con badge; single vigente con tipo/meta/CTA y finalizado **sin** CTA; byline «Por Zheng Gong» enlazada a la ficha; `/galeria` 3 álbumes nativos con lightbox; término con galería; ficha de autor con sus entradas; `/comunidad` con 2 enlaces de ficha; 404 con copy publicado | OK | Pass (local) |
| QA 3: higiene | `debug.log` inexistente tras navegar todas las vistas; sin `Set-Cookie` anónimo; sin warnings/fatals de código propio (el único warning previo era `wp_update_themes()` sin red, ruido del entorno) | OK | Pass (local) |
| QA 3: comportamiento | Toggle del nav abre/cierra con `aria-expanded` (main.js portado); tooltips del grid con `calendar-tooltips.js` encolado por el bloque; 4 fuentes autohospedadas cargadas (`document.fonts`); sin overflow horizontal | OK | Pass (local) |
| Bugs latentes de WU-05/06 cazados por el QA (regresión + fix) | (1) `event_modality` se saneaba contra el select de doc 03 y **descartaba el copy publicado** («Híbrida — …» quedaba vacío en BD) — fix: texto plano libre (OWN-007), test actualizado. (2) El importador reescribía `<img src>` pero no los `<source srcset>` de `<picture>` ni las thumbs hechas a mano → hero e imágenes del Inicio rotas — fix: conversión WU-07 desenvuelve `<picture>` y remapea thumbs a la biblioteca | OK | Pass (local) |
| QA 4 visual (parcial, local) | Escritorio: home y `/eventos` visualmente equivalentes a `https://caminodeldharma.org` (header, hero, tipografías display/heading/body, calendario). Breakpoint móvil correcto (toggle visible, menús ocultos a 400px). Pase completo (320px, zoom 200%, teclado, lector) pendiente | Parcial | Unverified |
| QA 4: staging / Hostinger | Sin staging aún (OWN-005) | — | Unverified |
| SEO dinámico (head, JSON-LD, noindex de términos/tags/author) | Alcance WU-08 | — | Unverified |
| Share / añadir al calendario / audio (diálogos JS) | Alcance WU-08 (mitad de `calendar.js` + `share.js`) | — | Unverified |
| CI/Sonar | Requiere push (rama local por diseño) | — | Unverified |

Decisiones y sustituciones registradas (WU-07) — ver también la matriz de migración:

1. **Tarjeta compacta para finalizados** (doc 03 §3 «Densidad»): sustitución deliberada de las
   cards completas del estático (miniatura, tipo, título, ciudad · fecha, «Ver evento →», badge).
2. **Fechas de evento generadas** desde `event_date`/`event_end` con reglas calibradas contra
   el copy publicado (7 de 10 idénticas; enumeración `07, 08 y 09` con cero a la izquierda).
   Las filas `Hora` y `Aporte` de algunas cards estáticas no viven en el modelo doc 03 y no se
   renderizan — remedio editorial: añadirlas al contenido del evento vía wp-admin si se desean.
3. **Card vigente del listado** = intro del single (contenido hasta el primer `h2`) + dl + CTA;
   el resumen manual de la card estática no existe como campo.
4. **CTA de inscripción** con label fijo «Preinscribirme» (coincide con el único evento vigente
   publicado); revisar en WU-08 si un evento futuro necesita «Inscribirme».
5. **Excerpt del listado del blog** = deck editorial (post_excerpt), no el recorte manual del
   primer párrafo del estático. Tiempo de lectura = round(palabras/200): Círculos 5′ = publicado;
   Sangha 6′ vs 8′ publicado (delta registrado, valor manual no derivable).
6. **Byline enlazada** a `/author/{slug}` (ADR 0037; el estático la publica sin enlace).
7. **`<picture>`/WebP/thumbs hechos a mano no migran** (doc 03 §5.1): la biblioteca sirve JPG
   y derivados; conversión documentada.
8. **Encabezados de álbum enlazan al término** (opcional permitido por ADR 0036) y el término
   pinta galería nativa con h1 «Galería · {título}» y volver al hub.
9. **Discrepancia doc 03 resuelta a favor de producción**: `event_modality` pasa de select
   (presencial/virtual/híbrido) a texto libre saneado — el copy publicado es descriptivo.
10. **Copy nuevo mínimo con OWN-016**: «Entradas del Maestro Zheng Gong en el blog» y
    «Entradas de la Comunidad en el blog» (ajustable en wp-admin; conversión reversible).
11. **Header/footer como patterns PHP** referenciados desde las parts (URLs generadas con
    `home_url()`; logo como asset del theme); bloque Navigation nativo reevaluable después del
    corte sin tocar contenido.
12. **Strings estructurales de listados** («Blog», intro, «Eventos», copy 404 de docs/08-09) viven
    en las plantillas/bloques: la posts page y el archive no leen contenido de una Page.
13. Harness wp-phpunit con `WP_DEFAULT_THEME=camino-del-dharma` + `register_theme_directory`
    en el bootstrap: los 60 tests corren con el theme real activo.

## WU-08A — Comportamiento front: compartir, añadir al calendario, audio de mantras

Ejecutado 2026-08-31 sobre `fase3-wordpress` (sesión separada, sin FABLE5 pegado; reanudación:
preflight + rerun de los gates WU-03…WU-07 antes de tocar nada). Sin PHP/Composer nativos:
comandos PHP vía Docker.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun preflight al reanudar | `git status --short` vacío; rama `fase3-wordpress`; historial = estado durable (`150d8b8`); `VERSION` 1.0.35 | OK | Pass (local) |
| Rerun gates WU-03…WU-07 | unit 105/105; phpcs limpio; `run-phpunit-wp.sh` 60/60; plugin 0.4.0 y theme 0.2.0 activos | OK | Pass (local) |
| TDD honesto: RED antes del primer archivo de comportamiento | Unit: 119 tests, **5 errors + 8 failures** (extracción de share, `convert_practica`, scripts y bloques ausentes). wp-phpunit: 75 tests, **2 errors + 11 failures** (meta `share_*`, `cdd_core_event_calendar_payload`, bloques `evento-acciones`/`entrada-compartir`, seeding de share, audio nativo); los 60 preexistentes siguieron verdes | OK | Pass (local) |
| Nivel 1 verde | `phpunit` → OK (**119 tests, 586 assertions**): las 3 plantillas de mensaje de Círculos y de los 2 posts extraídas **carácter a carácter** de `static/` (normalización idéntica a `share.js`, `{{SHARE_URL}}` intacto), eventos sin control de compartir → copy vacío (nada inventado), `convert_practica` (bloque `core/audio` con id/preload/caption, sin `</source>`, idempotente, no-op sin attachment), contrato DOM/copy de los dos scripts portados, orden de bloques en `single-event.html` y `single.html`, no duplicación del tooltip, port completo de `calendar.js` | OK | Pass (local) |
| Nivel 2 verde | `run-phpunit-wp.sh` → OK (**75 tests, 524 assertions**): meta `share_whatsapp`/`share_x`/`share_threads` registrada en `event` y `post` con REST y saneo (saltos de línea y placeholder sobreviven; markup no; no-string → `''`); `cdd_core_event_calendar_payload` alimenta diálogo **y** `.ics` (mismos título/fechas/descripción/lugar; fin exclusivo; evento de un día cierra al día siguiente); bloque `evento-acciones` con ambos disparadores y `<template>` solo de lo que hay guardado, **vacío en finalizados** (OWN-012); `entrada-compartir` en el blog; listado con acciones solo en vigentes; `aria-label` restaurado en `core/audio`; convert siembra share sin pisar ediciones wp-admin e idempotente | OK | Pass (local) |
| QA 1 | `php -l` OK; PHPCS **0 errores / 0 warnings**; `npm run lint:css` verde; sin secretos | OK | Pass (local) |
| Payload regenerado | `tools/extract-payload.sh` → mismos `counts` (11/10/2/2/3/35/81/5) y misma `source` (VERSION 1.0.35, commit `bfb6dc0`); **el único delta es el campo `share`** (+ hashes de events/posts). Diff verificado objeto a objeto | OK | Pass (local) |
| QA 3: conversión aplicada en el entorno local | `convert --payload=… ` dry-run → pending `practica` + 3 claves `share:`; `--apply` → converted los 4; 2.º `--apply` → 0 (idempotente) | OK | Pass (local) |
| QA 3: diálogo Compartir (navegador real) | `/eventos/circulos-…`: título del diálogo «Curso Círculos de Presencia Consciente»; intents WhatsApp/X/Threads con el copy publicado **carácter a carácter** y `{{SHARE_URL}}` sustituido por el permalink; Facebook con `?u=` del permalink; botón «Copiar enlace» presente; foco vuelve al disparador al cerrar. `/blog/sangha-…`: mismo contrato con el copy del post | OK | Pass (local) |
| QA 3: diálogo Añadir al calendario (navegador real) | Google `dates=20260903/20261025`, Outlook `startdt`/`enddt` con fin inclusivo, Apple → `/eventos/ical/{slug}.ics`, descarga con `download="{slug}.ics"`. **Coincide exactamente con el `.ics` que sirve WordPress** (DTSTART/DTEND/SUMMARY/LOCATION) | OK | Pass (local) |
| QA 3: audio de mantras | `/practica`: 2 `<audio>` nativos desde la biblioteca (`audio/mpeg`, HTTP 200, 28,8 MB), `preload="metadata"`, `class="wp-block-audio mantra-audio"`, `figcaption` publicado y `aria-label` restaurado por el filtro del theme | OK | Pass (local) |
| QA 3: editable desde wp-admin (ADR 0029) | Editor de bloques de `/practica`: **0 bloques inválidos**; los 2 `core/audio` con `isValid: true` y atributos `id`/`className`/`preload`/`src` correctos | OK | Pass (local) |
| QA 3: encolado condicional | `share.js` y `calendar-dialog.js` solo en las vistas que renderizan los disparadores (single vigente, `/eventos` con vigentes, single de blog); el single finalizado no encola ninguno de los dos ni pinta controles | OK | Pass (local) |
| QA 3: higiene | `debug.log` sin entradas de código propio (solo el ruido conocido de `wp_update_themes()` sin red) | OK | Pass (local) |
| QA 4 visual (parcial, local) | Sin cambios respecto a WU-07; el pase completo (320px, zoom 200%, teclado, lector de pantalla) sigue pendiente | Parcial | Unverified |
| SEO dinámico, redirects, OWN-015, a11y | Alcance **WU-08B** | — | Unverified |
| CI/Sonar | Requiere push (rama local por diseño) | — | Unverified |

Decisiones y deltas registrados (WU-08A):

1. **El copy de compartir es contenido de producción, no texto generado** (ADR 0034): las 9
   plantillas `<template>` hand-written viajan por el payload (`share.whatsapp|x|threads`) y
   viven como meta editable `share_whatsapp`/`share_x`/`share_threads` en `event` y `post`.
   Nada se regenera desde el título: un objeto sin copy publicado no imprime `<template>`
   alguno y `share.js` cae a su fallback (título + URL).
2. **Ruta de datos doble**: el importador escribe la meta al crear (staging parte con ella) y
   `wp cdd-core migrate convert --payload=<path>` la siembra en objetos ya importados. La
   siembra es **add-only**: una clave existente —incluida una que la editora vació a
   propósito— nunca se reescribe (ADR 0033).
3. **Diálogo de calendario y `.ics` comparten fuente** (`cdd_core_event_calendar_payload`): el
   enlace de Google/Outlook y el archivo descargado no pueden divergir. Consecuencia: el
   diálogo hereda los deltas ya aceptados en WU-06 del `.ics` de WordPress frente al `.ics`
   publicado — `SUMMARY` = título del evento (publicado: «Curso … — sesión de bienvenida»),
   `LOCATION` = `event_place` (publicado: «Virtual (hora de Colombia)») y `DTEND` = fin del
   rango del evento, 20261025 (publicado: 20260904, solo la bienvenida). **Delta abierto para
   el propietario**: si el calendario debe describir la sesión de bienvenida y no el curso
   completo, hace falta un campo editorial propio; no se inventa aquí.
4. **`data-share-description` no se emite**: `share.js` lo lee pero no lo usa en ningún punto
   del diálogo. Su contenido (para el blog) coincide con la meta description, que es superficie
   de **WU-08B**; migrarlo ahora sería duplicar ese trabajo.
5. **Diálogos solo en vigentes** (contrato §4 / OWN-012), como publica producción: el single
   finalizado no ofrece compartir ni calendario. El blog siempre ofrece compartir.
6. **Los mantras pasan a `core/audio` nativo** (mismo criterio que ADR 0021 con la galería). Se
   pierden el texto de respaldo («Tu navegador no permite reproducir este audio.») y el
   `aria-label` del markup guardado; el nombre accesible se restaura en presentación con un
   filtro `render_block` del theme a partir del `figcaption`. Efecto colateral saneado: el
   artefacto `</source>` que dejaba DOMDocument desaparece del contenido.
7. **`calendar.js` queda partido en dos archivos del theme** (`calendar-dialog.js` +
   `calendar-tooltips.js`), encolados por separado y solo cuando el bloque correspondiente
   renderiza. Un test protege que el tooltip no se duplique y que ningún comportamiento del
   original se haya perdido.
8. **Fixture de `/practica` con kses levantado**: el importador corre bajo WP-CLI, donde los
   filtros kses no están activos y el `<source>` publicado sobrevive; `source` no es tag
   permitido por kses, así que el test debe reproducir la ruta real y no una empobrecida.
