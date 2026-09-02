# Fase 3 — Matriz de validación

Evidencia QA por work unit (niveles 1–4; histórico FABLE5 §11). Estados permitidos: `Unverified`,
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
| QA 3: render de las vistas | Home: aside dinámico con «Septiembre – octubre 2026 / Bogotá y Cali» (idéntico a producción) + cards del blog; `/eventos`: título «Septiembre 2026», grid con 7 días de evento + lunes de práctica, secciones Próximos/Realizados, compactas con badge; single vigente con tipo/meta/CTA y finalizado **sin** CTA; byline «Por Zheng Gong» enlazada a la ficha; `/galeria` 3 álbumes nativos con lightbox; término con galería; ficha de autor con sus entradas; `/comunidad` con 2 enlaces de ficha; 404 con copy publicado | OK | Pass (local) |
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
| QA 3: conversión aplicada en el entorno local | `convert --payload=…` dry-run → pending `practica` + 3 claves `share:`; `--apply` → converted los 4; 2.º `--apply` → 0 (idempotente) | OK | Pass (local) |
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
   publicado — `SUMMARY` = título del evento (publicado: «Curso … — sesión de bienvenida») y
   `LOCATION` = `event_place` (publicado: «Virtual (hora de Colombia)»). El tercer delta (`DTEND`
   = fin del rango, 20261025) era **BUG-001**, cerrado en su sesión propia justo antes de WU-10:
   el `.ics` exportado incluye ahora **todas las sesiones**. Ver § BUG-001.
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

---

## WU-08B — SEO first-party, noindex, redirects, OWN-015 y a11y

Ejecutado 2026-08-31 sobre `fase3-wordpress` (sesión separada, FABLE5 §9.5 + §10 únicamente;
reanudación: preflight + rerun de los gates WU-03…WU-08A antes de tocar nada). Sin PHP/Composer
nativos: comandos PHP vía Docker.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun preflight al reanudar | `git status --short` vacío; rama `fase3-wordpress`; historial = estado durable (`d73fecd`, implementación WU-08A en `c94635f`); `VERSION` 1.0.35 | OK | Pass (local) |
| Rerun gates WU-03…WU-08A | php-lint OK; unit 119/119; phpcs limpio; wp-phpunit 75/75; plugin 0.5.0 y theme 0.3.0 activos | OK | Pass (local) |
| TDD honesto: RED antes del primer archivo | Unit: 148 tests, **17 errors + 12 failures** (`Cdd_Core_Seo_Extractor`, `Cdd_Core_Seo_Document`, `Cdd_Core_Json_Ld`, `wordpress/.htaccess`, `inc/seo.php` inexistentes). wp-phpunit: 98 tests, **17 errors + 3 failures** (`cdd_core_seo_context`, `cdd_core_seo_robots`, meta de cabecera, proveedores del sitemap, herramienta de huérfanos). Los preexistentes siguieron verdes | OK | Pass (local) |
| Nivel 1 verde | `phpunit` → OK (**156 tests, 808 assertions**): extracción de la cabecera publicada carácter a carácter (título, description, keywords, OG, `rel=related`, `@graph` del Inicio, `addressRegion` por ciudad, extras JSON-LD del evento); documento de cabeza (valor vacío = etiqueta ausente, `alternate` solo si se pasa); grafo (EventCompleted sin oferta, campos opcionales omitidos, extras nunca pisan lo generado, autores `Thing`, publisher Organization, rebase de la base de producción); `.htaccess` contra el ledger; contrato del theme | OK | Pass (local) |
| Nivel 2 verde | `run-phpunit-wp.sh` → OK (**106 tests, 649 assertions**): meta de cabecera registrada en `page`/`post`/`event` con REST; resolución por tipo de request (Page, portada, evento vigente/finalizado, entrada, ficha de autor, archivo `/author`, término de álbum, tag, 404, archivo `/eventos`); `noindex,follow` sin `nofollow`; `wp_robots` coherente con el contexto; sitemap sin proveedor de usuarios ni taxonomías, con el archivo `/eventos` una sola vez; herramienta de huérfanos (alcance, dry-run, apply, capacidad, pantalla en Herramientas); importador (meta, opción de sitio, regiones, `tag_base`, zona horaria, idioma) y `convert` add-only e idempotente | OK | Pass (local) |
| QA 1 | `php -l` OK; PHPCS **0 errores / 0 warnings** (81 archivos); `npm run lint:css` verde; sin secretos | OK | Pass (local) |
| Payload regenerado | `tools/extract-payload.sh` → mismos `counts` (11/10/2/2/3/35/81/5) y misma `source` (VERSION 1.0.35, commit `bfb6dc0`). Deltas verificados objeto a objeto: `seo` sustituye a `head_title`/`meta_description` en pages y posts, los eventos ganan `seo` + `attendance_mode` + `jsonld_extra`, y aparece la sección **no contada** `site` | OK | Pass (local) |
| QA 3: pipeline en el entorno local | `import --apply` → settings `timezone_string`/`tag_base` + `site_seo` (opción + 3 `cdd_region`); `convert` dry-run → 16 pendientes (11 pages + 3 singles + 2 posts; los 7 eventos solo-tarjeta no tienen copy y se saltan), `--apply` → 16, 2.º apply → 0 | OK | Pass (local) |
| QA 3: cabeza HTTP entrante | `/`, `/eventos`, los 2 singles, `/blog`, entrada, `/author`, ficha, `/galeria/2023`, `/privacidad`: un solo `<title>`, un solo `robots`, un solo `canonical`, todos con el copy publicado y el canónico del entorno (nunca `caminodeldharma.org`) | OK | Pass (local) |
| QA 3: JSON-LD | Portada: `@graph` publicado (Organization, 2 Person, WebSite, WebPage) rebasado, sin segundo `Event`, con `mentions` al evento vigente. Evento vigente: BreadcrumbList + Event con `EventScheduled`, `MixedEventAttendanceMode`, VirtualLocation + 2 Places con `addressRegion`, `offers` = precio publicado + `validFrom` + URL y disponibilidad vivas. Finalizado: `EventCompleted`, **sin** `offers`. Entrada: `BlogPosting` con autores `Thing` de la relación `authors`, publisher Organization, `mainEntityOfPage` sin `url` redundante. Ficha: `Thing` con nombre y URL canónica | OK | Pass (local) |
| QA 3: noindex | `/author`, `/galeria/{album}`, `/blog/tag/{slug}` y el 404 sirven `noindex,follow` (nunca `nofollow`); las fichas de autor y el resto siguen `index,follow,max-image-preview:large` | OK | Pass (local) |
| QA 3: `.ics` | Vigente 200 con `X-Robots-Tag: noindex, nofollow`, `text/calendar` y `Content-Disposition`; finalizado **410** con el mismo header; `rel="alternate" type="text/calendar"` solo en el single vigente (0 ocurrencias en el finalizado) | OK | Pass (local) |
| QA 3: sitemap nativo | `/wp-sitemap.xml` sin proveedor de usuarios ni taxonomías; páginas, entradas, eventos (con `/eventos` primero) y fichas de autor. Las únicas URL fuera del árbol son el contenido demo del install local (`/sample-page`, `/blog/hello-world`); el staging parte limpio | OK | Pass (local) |
| QA 3: `.htaccess` sobre Apache real | Archivo copiado al document root del contenedor y probado con `curl` (Host y `X-Forwarded-Proto` de producción): legacy singles → 301 de un salto con y sin barra final; `/prueba` y `/site.webmanifest` → **410**; `/category`, `/category/general` → `/blog` 301; `?page_id=10` → `/comunidad`, otro `page_id` → `/`; `sitemap.xml` → `/wp-sitemap.xml`; `/comunidad/` → `/comunidad`; `/wp-admin/` y `/wp-json` intactos. Sin cadenas, sin loops, sin 404 blandos. `.htaccess` local restaurado al terminar | OK | Pass (local) |
| QA 3: «Eliminar huérfanos» (OWN-015) | Con un `.ics` de evento finalizado y otro de evento vigente en la biblioteca: lista solo el primero, `--apply` borra 1 y conserva el vigente; fotos y mp3 nunca aparecen. Pantalla de Herramientas renderizada con `h1`, tabla `widefat` con `scope="col"`, nonce y botón de envío | OK | Pass (local) |
| QA 4: a11y docs/19 (navegador real) | 13 rutas auditadas: `lang="es-CO"`, un `h1` por página, jerarquía de encabezados sin saltos, 0 imágenes sin `alt`, 0 enlaces sin nombre accesible, 0 `tabindex` positivos, 0 contenedores `aria-hidden` con foco dentro. 320px y 640px (zoom 200 %): sin scroll horizontal ni elemento desbordado. Skip link único, primero en el orden de tabulación, visible al foco y con destino `#main`; `nav-toggle` con `aria-expanded` dinámico; 11 celdas del calendario con `aria-label` y ninguna con `title`; 9/9 SVG decorativos con `aria-hidden` **y** `focusable="false"`; `:focus-visible` con contraste y `prefers-reduced-motion` respetado | OK | Pass (local) |
| QA 3: higiene | `debug.log` sin entradas de código propio | OK | Pass (local) |
| QA 4 visual completo (lector de pantalla real) | No ejecutado en esta sesión | — | Unverified |
| Staging Hostinger | Sin crear (OWN-005) | — | Unverified |
| CI/Sonar | Requiere push (rama local por diseño) | — | Unverified |

Decisiones y deltas registrados (WU-08B):

1. **La cabeza publicada es contenido, no texto generado** (ADR 0034, OWN-007): título,
   description, keywords y copy de Open Graph viajan en el payload (`seo`) y viven como meta
   editable `seo_title`/`seo_description`/`seo_keywords`/`og_title`/`og_description` en `page`,
   `post` y `event`. Un objeto sin copy publicado —los 7 eventos ADR 0035 que solo existen como
   tarjeta— no imprime una cabeza inventada: cae al título real y omite la description.
2. **`/eventos` no es una Page**: su cabeza publicada, los defaults sociales y el `@graph` del
   Inicio viajan en una sección **`site` no contada** del payload y se siembran como la opción
   `cdd_core_seo_site` (add-only). Los `counts` de reconciliación no cambian. La sección no tiene
   UI propia todavía: se edita por WP-CLI hasta que exista una pantalla (fase posterior).
3. **Todo URL almacenado se rebasa a `home_url()`** al renderizar. El payload guarda las URL de
   producción; un staging jamás publica `caminodeldharma.org` como identidad propia. La imagen
   social por defecto además se resuelve contra la biblioteca al sembrar la opción, para no
   hotlinkear producción.
4. **Nunca se inventa un campo opcional**: sin ciudades no hay `location`, sin fin no hay
   `endDate`, sin cartel no hay `image`. La modalidad publicada es texto libre (OWN-007) y no se
   parsea: el `eventAttendanceMode` sale de un campo propio `event_attendance_mode` extraído del
   JSON-LD publicado, y queda vacío —campo omitido— en los eventos sin single.
5. **La riqueza publicada que WordPress no puede re-derivar** (`additionalType`, `alternateName`,
   `audience`, `performer`, `subjectOf`, precio y `validFrom` de la oferta) viaja como
   `seo_jsonld_extra` y se fusiona **por debajo** del nodo generado: un campo generado siempre
   gana, así que nada se queda obsoleto, y un evento finalizado descarta la oferta guardada
   (§10.2). El `addressRegion` de cada ciudad es meta del término `event_city`.
6. **Deltas aceptados frente al JSON-LD publicado**: `startDate`/`endDate` son fechas sin hora ni
   offset (el modelo WU-05 guarda `Y-m-d`); la `description` del Event es la meta description
   publicada (el estático publica una tercera cadena propia que ningún campo del modelo carga);
   `/comunidad` emite BreadcrumbList en vez del `WebPage`+`Organization`+`Person` que publica el
   estático (doc 15 §12.5 pide BreadcrumbList en subpáginas).
7. **`rel="alternate" type="text/calendar"`** usa el título del evento; el estático publica un
   título hand-written («… — sesión de bienvenida») que ningún campo carga. Se emite solo
   mientras el evento es vigente (OWN-014).
8. **`tag_base` = `blog/tag`** (docs/11 §3.2): sin ese ajuste WordPress publicaría `/tag/{slug}`,
   una URL que el árbol no contiene. Lo aplica el importador, como el resto de los settings.
9. **El idioma del documento es un filtro, no un ajuste**: WordPress rechaza guardar en `WPLANG`
   un locale cuyos archivos de traducción no están instalados, así que un entorno recién creado
   servía `<html lang="en-US">` (fallo WCAG 3.1.1). `cdd_core_default_locale()` declara `es_CO`
   y se aparta en cuanto una administradora elige idioma en Ajustes.
10. **El `.htaccess` de WordPress corrige un bucle latente del estático**: la condición HTTPS
    publicada usa `[OR]`, que tras un proxy con TLS terminado (HTTPS != on, `X-Forwarded-Proto` =
    https) redirige una petición que ya es segura a una URL que vuelve a cumplir la condición. En
    producción no se dispara porque Hostinger fija las dos señales. No se porta el bucle (§10.1)
    y **no se toca el estático**.
11. **Reglas solo-estáticas que no viajan**: `DirectoryIndex`, la reescritura de `index.html` y
    `ErrorDocument 404` sombrearían el front controller de WordPress y fabricarían 404 blandos.
    El ledger lo registra.
12. **El sitemap pierde proveedores enteros**: usuarios (los archivos de autor de WP son 404,
    ADR 0037 §5) y **todas** las taxonomías (categorías fuera del árbol; tags y álbumes
    `noindex`). `/eventos` se añade subclasificando el proveedor de entradas, porque el núcleo no
    expone filtro sobre la lista terminada.
13. **Defectos de accesibilidad heredados del estático y corregidos solo en WordPress**: cuatro
    SVG decorativos sin `focusable="false"`, y los archivos `/author` y `/blog/tag/{slug}` sin
    `h1` (caían a `index.html`). Se añade `templates/archive.html` y
    `templates/archive-blog_author.html`. Además se retira el skip link que el núcleo inyecta en
    los block themes, duplicado del publicado y en el idioma del admin.

## WU-09 — Contact Form 7 y los párrafos del formulario en `/privacidad`

Ejecutado 2026-08-31 sobre `fase3-wordpress` (sesión separada, FABLE5 §10.3 + §10.4 únicamente;
reanudación: preflight + rerun de los gates WU-03…WU-08B antes de tocar nada). Sin PHP/Composer
nativos: comandos PHP vía Docker.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Rerun preflight al reanudar | `git status --short` vacío; rama `fase3-wordpress`; HEAD `f860561` = estado durable | OK | Pass (local) |
| Rerun gates WU-03…WU-08B | php-lint OK; unit 156/156; wp-phpunit 106/106; plugin 0.6.0 y theme 0.4.0 activos | OK | Pass (local) |
| TDD honesto: RED antes del primer archivo | Unit: 173 tests, **15 errors + 2 failures** (`Cdd_Core_Contact_Form_Template`, `convert_contacto`, `convert_privacidad`, `Cdd_Core_Spanish_Date::long_form`, bloque del theme inexistentes). wp-phpunit: 114 tests, **5 errors + 3 failures** (`cdd_core_contact_form_available`, `cdd_core_provision_contact_form`, `cdd_core_privacy_delta_applied`, render degradado, orden del `convert`). Los preexistentes siguieron verdes | OK | Pass (local) |
| Nivel 1 verde | `phpunit` → OK (**175 tests, 913 assertions**): definición CF7 contra el formulario publicado leído de `static/contacto/index.html` (labels, `for`, ids, `name`, `autocomplete`, los 4 `path` de los iconos, los 3 `.form-group`, el `<button>` con su icono); mail al buzón con `Reply-To` del visitante; mensajes en español; autop desactivado solo para este formulario; las 5 sustituciones de `/privacidad` verbatim, el sello provisional conservado, §2.3–§2.6 idénticas byte a byte, sin líneas en blanco residuales, idempotencia y gate `privacidad_delta_applied()` | OK | Pass (local) |
| Nivel 2 verde | `run-phpunit-wp.sh` → OK (**114 tests, 677 assertions**): sin CF7 el formulario no está disponible, el bloque imprime los canales publicados (nunca `[contact-form-7`, nunca un `<form>`), y `provision` **rehúsa** informando todos los bloqueos a la vez; gate de `/privacidad` cerrado sin aviso y sin delta, abierto tras la conversión; `convert` ordena privacidad **antes** que contacto, aplica ambas y el 2.º apply no toca nada; la fecha del aviso es la del día en la zona del sitio | OK | Pass (local) |
| QA 1 | `php -l` OK; PHPCS **0 errores / 0 warnings** (85 archivos); `npm run lint:css` verde; sin secretos; sin código de CF7 en Git (`git status` limpio tras instalarlo) | OK | Pass (local) |
| Orden ADR 0041 respetado en el entorno | Con CF7 **desactivado**: `contact provision` rehúsa con los dos bloqueos. Después `migrate convert --apply` → `privacidad` + `contacto`; 2.º apply → 0. Solo entonces se activa CF7 y se provisiona | OK | Pass (local) |
| Delta de copy en `/privacidad` (WordPress) | `diff` del `post_content` antes/después: **4 hunks, ninguno fuera del alcance** — cláusula del recuadro, fecha, viñeta del resumen, §2.2 (2 párrafos → 1) y el disparador de §8 (línea completa, sin blanco residual). Cookies, analítica, embeds, donaciones, derechos y Ley 1581 sin tocar | OK | Pass (local) |
| Render de `/privacidad` | HTTP entrante: sello «Documento provisional» presente, «Su redacción podrá cambiar tras esa revisión.», «Última actualización: 31 de agosto de 2026.», viñeta y §2.2 aprobadas, 0 ocurrencias de «pase a enviarse/procesarse en un servidor», §8 con sus 4 disparadores restantes incluida la revisión legal | OK | Pass (local) |
| Provisión CF7 idempotente | `contact provision` (dry-run) → sin bloqueos, nada escrito; `--apply` → form id creado; 2.º `--apply` → rehúsa («already exists»). Propiedades almacenadas: `recipient` = `caminodeldharma1@gmail.com`, `Reply-To: [correo]`, `use_html` false, cuerpo con `[nombre]`/`[correo]`/`[mensaje]`, `sender` derivado de `home_url()`, locale `es_CO` | OK | Pass (local) |
| Paridad DOM del formulario publicado | Render real: `class="wpcf7-form init section-gap"`, `aria-label="Formulario de contacto"`, `method="post"`; los 3 `label` con su `for` apuntando a `contact-name`/`contact-email`/`contact-message`; `name` `nombre`/`correo`/`mensaje`; `autocomplete` `name`/`email`; iconos en las etiquetas de correo y mensaje; `<button type="submit" class="btn btn-primary">` con su icono y «Enviar»; **0 `<p>` espurios** (autop desactivado) | OK | Pass (local) |
| Envío con datos sintéticos | 3 estados verificados en navegador real: vacío → `invalid` + «Este campo es obligatorio.» ×3; correo inválido → `invalid` + «Escribe una dirección de correo válida.»; datos válidos → **validación superada**, `data-status="failed"` en `wp_mail()` | Validación OK; entrega no | Pass (local) parcial |
| Entrega real del correo | El contenedor local no tiene MTA: `wp_mail()` devuelve `false` (comprobado directamente, no solo vía CF7). **No prueba nada sobre Hostinger** | No demostrable en local | Unverified |
| Fallback operativo (ADR 0041 punto 5) | Con CF7 desactivado, `/contacto` sirve `<p class="contact-form-unavailable">` con WhatsApp y el correo de la comunidad; 0 ocurrencias de `[contact-form-7`; 0 `<form>` en `main` | OK | Pass (local) |
| Mensajes en español | El locale del sitio viene de `cdd_core_default_locale()` y no de `WPLANG`, así que WordPress no instala paquete de traducción de CF7: los 8 mensajes que este formulario puede producir son propios y se verificaron renderizados en español | OK | Pass (local) |
| QA 4: a11y del formulario (navegador real) | `label`/control asociados por `for`/`id`; `aria-required="true"` en los 3 controles; `.screen-reader-response` presente y recortado (`position:absolute`, 1px); a 320px sin scroll horizontal, controles a 272px de ancho, botón 140×52 px (≥44); sin errores de consola | OK | Pass (local) |
| Estático intacto | `git diff -- static/` vacío; producción sigue sirviendo `action="#"` y su aviso sin tocar (ADR 0041 punto 4) | OK | Pass (local) |
| CF7 fuera de Git | Instalado en el volumen `wp_data`, nunca en el árbol del repo; versión registrada en `docs/operations/third-party-plugins.md` | OK | Pass (local) |
| Sin antispam adicional | Ningún plugin más instalado; Akismet sigue **inactivo** como venía del install | OK | Pass (local) |
| QA 4 visual completo (lector de pantalla real) | No ejecutado en esta sesión | — | Unverified |
| Entrega real en staging Hostinger | Sin crear (OWN-005). `Pass (local)` **no basta** (ADR 0026/0041 punto 5) | — | Unverified |
| CI/Sonar | Requiere push (rama local por diseño) | — | Unverified |

Decisiones y deltas registrados (WU-09):

1. **El repositorio posee la *definición*, no el plugin.** CF7 6.1.7 se instala por entorno y su
   código nunca viaja en Git (ADR 0025). Lo versionado es
   `Cdd_Core_Contact_Form_Template`: la plantilla del formulario, la del correo y los mensajes.
   `wp cdd-core contact provision` los escribe una vez, create-missing-only: lo que un editor
   cambie después en wp-admin no se pisa (misma semántica que el importador, ADR 0033).
2. **El botón publicado sobrevive.** `[submit]` de CF7 solo sabe imprimir un `<input>`, y
   producción publica un `<button>` con el icono de envío. CF7 escucha el evento `submit` del
   formulario, así que el `<button>` publicado lo acciona igual y conserva su icono. Coste: sin
   el spinner de CF7 — que el DOM publicado tampoco tenía.
3. **Deltas de DOM aceptados frente al formulario publicado** (inevitables, los imprime CF7):
   `action="#"` pasa a la URL real; el `<form>` gana `novalidate`, `data-status` y las clases
   `wpcf7-form init`; cada control queda envuelto en `<span class="wpcf7-form-control-wrap">` y
   gana `size`, `maxlength` y `aria-required` en lugar del `required` nativo (CF7 valida en
   servidor y en JS); aparecen el contenedor `.wpcf7` y `.screen-reader-response`. Todo lo demás
   —clases, ids, `name`, `autocomplete`, etiquetas, iconos, botón— es el copy publicado.
4. **El formulario es un bloque del theme, no un shortcode en el contenido.** Así la Page no
   guarda un identificador de un plugin de terceros, y con CF7 apagado el visitante lee los
   canales que sí funcionan en vez de la cadena `[contact-form-7 …]` en crudo. Es el fallback
   operativo de ADR 0041 punto 5, implementado, no solo documentado. Efecto lateral útil: tras la
   conversión `/contacto` ya no guarda `<form>`, así que KSES no puede mutilarla si la edita un
   perfil sin `unfiltered_html`.
5. **Los mensajes que lee un visitante son propios.** El locale del sitio lo fija
   `cdd_core_default_locale()`, no `WPLANG`, así que WordPress nunca instala el paquete de
   traducción de CF7 y sus cadenas saldrían en inglés. Se poseen los 8 mensajes que un formulario
   de tres campos de texto puede producir; el resto (ficheros, fechas, números, quiz, captcha)
   conserva los de CF7 porque ningún campo de este formulario puede provocarlos.
   «Spam» y «fallo de envío» comparten texto a propósito: a un falso positivo no se le dice que
   parecía spam.
6. **Sin token de error en el sistema visual.** El maquetado estático nunca tuvo un formulario que
   enviara, así que no hay color publicado para un estado de error. Se alinea el ritmo (márgenes,
   radio) al resto de la página y se dejan los colores de estado de CF7 en vez de inventar una
   pareja error/éxito que producción no especifica.
7. **`autop` desactivado solo para este formulario.** CF7 autoformatea la plantilla y envolvería
   las etiquetas, los `div` y el botón escritos a mano en `<p>` sueltos. El filtro compara con el
   id provisionado: un formulario que un editor cree más adelante conserva el comportamiento por
   defecto de CF7.
8. **El gate de ADR 0041 punto 3 es código, no una nota.** `contact provision` lee la Page
   `/privacidad` publicada y rehúsa mientras el §2.2 no describa un envío real, señalando
   `wp cdd-core migrate convert --apply`. Y `convert` recorre `privacidad` **antes** que
   `contacto`, de modo que el aviso es cierto antes de que el formulario llegue a la página.
9. **La cláusula del recuadro se retira, no se reescribe.** ADR 0041 aprueba quitar «cuando el
   formulario de contacto pase a enviarse a un servidor»; la frase publicada se conserva íntegra
   menos esa cláusula («Su redacción podrá cambiar tras esa revisión.»), que es la lectura más
   fiel de «no reescribir el resto del aviso».
10. **El correo de §2.2 va en texto plano**, sin `mailto:`, porque el copy aprobado es texto: el
    enlace sería marcado añadido, no copy aprobado. §6 sigue enlazándolo como ya lo publicaba.
11. **El harness hermético no ejecuta CF7 y así se declara.** El código de terceros no viaja en
    Git, así que la rama «CF7 presente» no se prueba en la suite: se prueba lo propio en ambos
    estados y la integración real se verifica contra un entorno real. En el harness, además, KSES
    borra `<form>` de cualquier fixture, así que el test de WU-09 retira esos filtros para
    almacenar el contenido publicado tal cual (WP-CLI, que es por donde importan los entornos
    reales, no los instala).
12. **`Pass (local)` ≠ entrega.** La validación del formulario está probada de extremo a extremo;
    `wp_mail()` falla en Docker por falta de MTA. La entrega a `caminodeldharma1@gmail.com` se
    verifica en staging Hostinger antes del release; si allí falla, el corte puede seguir con CF7
    deshabilitado y WhatsApp/correo — fallo operativo, no gate jurídico.

## BUG-001 — El `.ics` de Círculos incluye todas las sesiones

Sesión propia entre WU-09 y WU-10 (backlog de dueño v1.23). Decisión del dueño (2026-08-31): ni
el estático publicado (un VEVENT de la bienvenida, 3–4 sep) ni la salida de WordPress hasta hoy
(un VEVENT del rango 3 sep → 25 oct) son el contrato; el exportado debe incluir **todas** las
sesiones ya extraídas en `event_calendar_dates` / `calendar_dates` del payload.

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| TDD honesto: RED antes del primer archivo de comportamiento | Unit: 183 tests, **5 failures** (VEVENT por sesión, fin de día propio, UID por sesión, sesión multidía, nota del diálogo). wp-phpunit: 121 tests, **2 errors + 3 failures** (`occurrences`/`session_count`/`next` en el payload, enlace profundo a la próxima sesión, atributos del disparador); los 175/114 preexistentes siguieron verdes | OK | Pass (local) |
| Nivel 1 verde | `run-phpunit.sh` → OK (**183 tests, 1023 assertions**): 10 sesiones = 10 VEVENT en un solo sobre VCALENDAR; cada VEVENT cierra al día siguiente de sí mismo (20261024 → 20261025) y ninguno abre el 4 de septiembre; 10 UID distintos `slug-Ymd@host`; identidad compartida (SUMMARY, DESCRIPTION, LOCATION, URL, ATTACH, ORGANIZER, DTSTAMP) repetida en cada uno; evento sin cronograma = un VEVENT de rango con el UID publicado; sesión multidía con su propio fin exclusivo; CRLF sin plegado | OK | Pass (local) |
| Nivel 2 verde | `run-phpunit-wp.sh` → OK (**121 tests, 723 assertions**): `cdd_core_event_calendar_payload()` publica `occurrences` (10), `session_count` y `next` sin tocar el rango `start_date`/`end_date`; la respuesta HTTP trae 10 VEVENT con las 10 fechas del cronograma y 10 UID únicos; el enlace profundo es la **próxima** sesión en `America/Bogota` (20260922/20260923 el 20 sep; 20260903/20260904 antes de empezar) y esa pareja existe como VEVENT en el archivo; evento sin cronograma conserva rango y UID; el disparador imprime `data-calendar-sessions` y `data-calendar-note`, y no los imprime sin cronograma | OK | Pass (local) |
| Fuente única diálogo ↔ archivo | Test dedicado: la pareja compacta que leen Google/Outlook se busca como `DTSTART`/`DTEND` en el cuerpo `.ics` de la misma petición | No hay pareja que el archivo no contenga | Pass (local) |
| OWN-012 intacto | `cdd_core_event_ics_response()` sobre un curso finalizado **con** cronograma | 410, cuerpo vacío, `X-Robots-Tag: noindex, nofollow` | Pass (local) |
| Salida HTTP real | `curl http://localhost:8081/eventos/ical/circulos-de-presencia-consciente.ics` | 200 `text/calendar`, `Content-Disposition` adjunto, **10 VEVENT** (20260903…20261024) con UID `circulos-de-presencia-consciente-Ymd@localhost`; `encuentro-nacional-2026` y `vesak-2026` (finalizados) siguen en 410 | Pass (local) |
| Diálogo real | Single de Círculos en el navegador: se abre «Añadir al calendario» y se leen los enlaces | `dates=20260903/20260904` (Google), `startdt=2026-09-03&enddt=2026-09-03` (Outlook), descarga `circulos-de-presencia-consciente.ics`, nota visible y `aria-describedby="calendar-dialog-note"` | Pass (local) |
| Estático sin tocar | `git status` sobre `static/` | 0 cambios | Pass (local) |
| WPCS | `phpcs` (plugin + theme + tests) | 0 errores, 0 avisos | Pass (local) |
| `php -l` / stylelint | `tools/php-lint.sh`, `stylelint` sobre `assets/css/main.css` | OK / 0 | Pass (local) |
| CI/Sonar | Requiere push (rama local por diseño) | — | Unverified |

Decisiones y deltas registrados (BUG-001):

1. **Un VEVENT por sesión, no una regla de repetición.** El cronograma de Círculos es irregular
   (3, 10, 15, 17, 22, 24, 29 sep; 1, 17, 24 oct): no hay `RRULE` que lo describa sin `RDATE`
   sueltas. Diez VEVENT con UID propio es la forma RFC 5545 que cualquier cliente almacena como
   diez entradas separadas.
2. **El UID solo se sufija cuando hay cronograma.** Un evento sin sesiones conserva
   `slug@host`, el UID que producción ya publicó: un visitante que añadió el Encuentro Nacional
   no ve un duplicado. Las sesiones usan `slug-Ymd@host`.
3. **El archivo es el cronograma completo, también hacia atrás.** Se exportan las sesiones ya
   celebradas mientras el curso siga vigente; un cliente que lo abra a mitad de curso ve el
   proceso entero, no solo lo que queda. Cuando el curso termina manda OWN-012: 410 y nada.
4. **Un enlace profundo lleva una sola entrada.** Google Calendar y Outlook no aceptan diez
   fechas en una URL, así que el diálogo pasa de describir un rango que no existe en ningún
   VEVENT a nombrar **la próxima sesión**, una fecha que el archivo sí contiene. Apple Calendar
   y «Descargar archivo .ics» siguen entregando las diez.
5. **La nota es copy nuevo, no copy publicado.** El estático nunca tuvo esta situación, así que
   no hay frase publicada que respetar (OWN-007 no aplica). Se añade una sola línea, en la voz
   del sitio, y solo cuando hay más de una sesión: «El archivo .ics incluye las 10 sesiones del
   curso. Google Calendar y Outlook añaden la próxima: Jueves 3 de septiembre de 2026.» Se
   expone como `aria-describedby` del diálogo (docs/19): quien usa lector de pantalla la oye al
   abrirlo, no después de elegir.
6. **La pareja compacta y las fechas del archivo tienen forma distinta a propósito.** El payload
   lleva las ocurrencias en forma inclusiva (`start_date`/`end_date`, lo que consume el
   generador) y la pareja compacta con fin exclusivo (`start`/`end`, lo que consumen los enlaces
   profundos). Mezclarlas empujaba cada `DTEND` un día de más; lo detectó el test de nivel 2
   antes que ningún cliente de calendario, y `cdd_core_ics_occurrence()` es hoy el único punto
   de traducción entre ambas.
7. **El estático no se toca** (memoria del proyecto + decisión del dueño):
   `static/eventos/ical/circulos-de-presencia-consciente.ics` sigue publicando su VEVENT único
   de la bienvenida hasta el corte. El delta queda registrado aquí, no arreglado allí.

## WU-10 — QA local completa y runbook de staging

Sesión propia tras BUG-001. **No es una escritura en Hostinger**: WU-10 produce evidencia y un
runbook; no crea, despliega ni importa en ninguna instancia (OWN-005). Los niveles 1–3 se
re-ejecutaron **contra el árbol actual** (`e377c46`), no heredados de sesiones anteriores.

Verificación de reanudación: `HEAD` = `origin/fase3-wordpress` = `e377c46`, árbol limpio,
**0 ahead / 0 behind**. Esto **corrige** el encabezado del estado durable, que describía la rama
en `78db8f7` con los commits de BUG-001 «solo en local»: ya estaban publicados. El repositorio
manda sobre el archivo.

### Nivel 1 — Comprobaciones estáticas

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| `php -l` sobre PHP propio | `tools/php-lint.sh` (plugin + theme + tests, sin `vendor/`) | `php-lint: OK` | Pass (local) |
| Suite unit | `tools/run-phpunit.sh` | OK — **183 tests, 1023 assertions** | Pass (local) |
| WPCS | `phpcs` con `phpcs.xml.dist` | **85 archivos, 0 errores, 0 avisos** | Pass (local) |
| Advisories de dependencias | `composer audit --locked` | `No security vulnerability advisories found` | Pass (local) |
| Lint CSS de ambos árboles | `npm run lint:css` (`static/` + theme) | exit 0 | Pass (local) |
| `git diff --check` | ejecutado | limpio | Pass (local) |
| Parseo JSON | `payload.json`, `theme.json`, `composer.json`, `package.json`, `.stylelintrc.json` | todos válidos | Pass (local) |
| Parseo YAML | `test.yml`, `docker-compose.yml`, `docker-compose.wp-tests.yml`, `_config.yml` | todos válidos | Pass (local) |
| Sin secretos | `git grep` de patrones (AWS, claves privadas, tokens `ghp_`/`sk-`/`xox`) sobre archivos versionados; `.env` no versionado | 0 coincidencias; `tools/wp-tests.env` son credenciales desechables documentadas | Pass (local) |
| Sin plantillas PHP clásicas en el theme | `find` de `single.php`/`page.php`/`archive.php`/`header.php`/`footer.php`/`index.php` en la raíz del theme | ninguna (solo `functions.php`, `inc/`, `patterns/`) | Pass (local) |

### Nivel 2 — Comprobaciones de componente

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Suite wp-phpunit | `tools/run-phpunit-wp.sh` (harness efímero) | OK — **121 tests, 723 assertions** | Pass (local) |
| `migrate validate` | contra `migration/payload.json` | `Payload valid` | Pass (local) |
| `migrate verify` | 6 colecciones | pages 11/11, events 10/10, posts 2/2, blog_authors 2/2, gallery_albums 3/3, media 81/81; **`missing: []`** | Pass (local) |
| Idempotencia del importador | `migrate import` sin `--apply` | **0 created**, todo `skipped` en las 6 colecciones | Pass (local) |
| Dry-run por defecto | `import`, `seed`, `convert` sin `--apply` | los tres devuelven `dry_run: true` y no escriben | Pass (local) |
| `seed` idempotente | dry-run | `media: created 0, skipped 81` | Pass (local) |
| `convert` idempotente | dry-run | `pending: []`, `converted: []` | Pass (local) |
| `contact provision` create-missing-only | dry-run | se niega: «The contact form already exists in this environment» (form id 129) | Pass (local) |
| Rutas `.ics` | `curl` sobre las 10 | 200 `text/calendar` en el vigente; **410** en los 9 finalizados; 404 en slug inexistente | Pass (local) |
| BUG-001 en la salida real | `.ics` de Círculos descargado | **10 VEVENT**, 10 UID únicos `…-Ymd@localhost`, cada `DTEND` = `DTSTART`+1 día | Pass (local) |

### Nivel 3 — Integración local

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| Activación sin warnings/fatals | `wp plugin list` / `wp theme list` | plugin `camino-del-dharma-core` **0.7.1** activo; theme `camino-del-dharma` **0.5.1** activo; CF7 **6.1.7** activo; core **7.1** | Pass (local) |
| `debug.log` tras navegación representativa | truncado a 0, luego 41 rutas + `.ics` + sitemap + archivo de tag | **0 bytes** | Pass (local) |
| Rutas entrantes | `curl` sobre 41 rutas | 12 páginas 200 · 10 singles de evento 200 · 2 entradas 200 · 2 fichas de autor + archivo 200 · 3 álbumes 200 · sitemap 200 | Pass (local) |
| Canonicalización sin barra final (ADR 0008) | 5 rutas con barra | **301** a la forma sin barra en todas | Pass (local) |
| 404 real | 5 rutas inexistentes | **404** (no soft-404) | Pass (local) |
| Archivos de usuario WP en 404 (ADR 0037) | `/author/admin`, `/?author=1` | 404 en ambos | Pass (local) |
| Ajustes | `wp option get` | permalinks `/blog/%postname%`, zona `America/Bogota`, front page 100, posts page 109 | Pass (local) |
| Políticas noindex | meta `robots` por ruta | álbum `noindex,follow` (ADR 0036); `/author` archivo `noindex,follow` y ficha `index,follow` (ADR 0037); tag `noindex,follow` (ADR 0031); páginas públicas `index,follow,max-image-preview:large` | Pass (local) |
| Cabeza SEO | canonical + description + OG + JSON-LD | presentes y correctos en las rutas públicas | Pass (local) |
| Cookies anónimas | `Set-Cookie` en 10 rutas + `.ics` | **ninguna** | Pass (local) |
| Ausencia de seguimiento | hosts externos en el HTML anónimo | solo `wa.me`, redes sociales, `youtube-nocookie`, `player.vimeo`, atribución de iconos; **sin analítica** | Pass (local) |
| Conteos y relaciones de medios | `verify` + consulta por término | 81 medios; **35 asignaciones** a álbum (general 25 + 2023 5 + 2021 5) = `gallery_images` del payload | Pass (local) |
| Relación `authors` | meta de las 2 entradas | `[5]` y `[6]`, correctas | Pass (local) |
| Formulario CF7 en `/contacto` | HTML renderizado | `<form action="/contacto#wpcf7-f129-p108-o1" method="post">`, `aria-label` y clase `section-gap` del estático preservados | Pass (local) |

### Nivel 4 — Visual y accesibilidad (lo que un navegador local alcanza)

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| 320 px sin scroll horizontal | 19 rutas medidas (`scrollWidth` vs `clientWidth`) | 17 limpias; 2 con desbordamiento — ver D-04 y D-09 | Fail (local) *(solo `/practica`)* |
| 640 px (= zoom 200 % sobre 1280) | las mismas 19 rutas | **0 desbordamientos** | Pass (local) |
| Foco visible | 21 reglas `:focus-visible` en las hojas del theme | presentes | Pass (local) |
| Navegación por teclado | single de evento | 32 elementos enfocables, **todos con nombre accesible**; primer tabulable = «Saltar al contenido» | Pass (local) |
| Diálogo de calendario (a11y) | apertura real en el navegador | `<dialog>` **modal** (`:modal`), `aria-labelledby` + `aria-describedby="calendar-dialog-note"`, foco entra al diálogo y `close()` lo devuelve al disparador; el evento `cancel` **no** se previene (la ruta nativa de Escape queda intacta) | Pass (local) |
| Escape cierra el diálogo | pulsación real de Escape | **inconcluso**: el panel de automatización consume la tecla. Por código no hay `preventDefault` sobre `cancel` | Unverified |
| BUG-001 en el diálogo | enlaces del diálogo abierto | Google `dates=20260903/20260904` = **próxima sesión** (jueves 3 sep 2026, verificado); Apple y descarga apuntan al `.ics` de las 10 | Pass (local) |
| Paridad de copy vs producción publicada (OWN-007) | diff de texto visible local ↔ `https://caminodeldharma.org` | `/linaje`, `/donaciones`, `/contacto`, `/practica/videos`, `/practica/meditacion-semanal-en-linea` = **1.000**; el resto explicado en D-01…D-10 | Pass (local) *(con deltas)* |
| Lector de pantalla real | no ejecutable en esta sesión | — | Unverified |
| PHP/Apache/HTTPS de staging | no existe instancia | — | Unverified |
| No indexabilidad del staging | no existe instancia | — | Unverified |
| Entrega real de CF7 a `caminodeldharma1@gmail.com` | `wp_mail()` local | **FALSE** — `Invalid address: (From): wordpress@localhost`, sin MTA en el contenedor. No prueba nada sobre Hostinger | Unverified |

### CI y Sonar

| Check | Método | Resultado | Estado |
| --- | --- | --- | --- |
| `test.yml` sobre esta rama | `gh run list` | **nunca se ha ejecutado**. Los únicos runs del repo son `pages-build-deployment` en `main` (jul 2026) | Unverified |
| Motivo (corregido) | `.github/workflows/test.yml` | dispara solo en `push: branches: [main]` y `pull_request`. La rama **sí está publicada**, pero un push de rama no la dispara y **no existe PR** | — |
| Sonar de plugin + theme | Automatic Analysis vía GitHub App | no revisado en esta sesión | Unverified |

**Corrección de sesiones anteriores:** las filas «CI/Sonar — Requiere push (rama local por
diseño)» de WU-03…BUG-001 son **inexactas**. La rama está publicada desde WU-09; lo que falta es
un disparador, no un push. Para obtener evidencia de CI hace falta un PR (o ampliar los
triggers) — decisión del propietario, no una limpieza silenciosa.

### Deltas y hallazgos registrados (WU-10)

1. **D-01 — `event_modality` vacío en los 9 eventos que tienen modalidad (entorno local).**
   Producción publica una fila «Modalidad» («Híbrida — bienvenida, orientación, seis sesiones
   virtuales y un encuentro presencial», «En línea (Zoom y YouTube)», …); el WordPress local no
   muestra ninguna. **No es un defecto de código:** el payload trae `modality` en 9/10 eventos,
   el extractor la extrae, el importador la escribe (`class-cdd-core-importer.php:399`) y el
   renderizador la pinta — verificado inyectando el valor en una sola petición, la fila
   «Modalidad» aparece correctamente. Lo que falla es el **entorno local**: su contenido se
   importó con un payload anterior a ese campo, y el importador es **create-missing-only**, así
   que `import --apply` **no lo rellena** (dry-run: `created: 0`, todo `skipped`). Comprobado
   además que `event_date` y el `alt` de los carteles **sí** coinciden con el payload en los 10
   eventos: `event_modality` es el único campo desalineado. Consecuencia operativa en el
   runbook §4b: **staging se importa una sola vez, desde cero**. **Cerrado 2026-09-01 (OWN-023).**
2. **D-02 — El contenido demo del install desplaza contenido real.** «Hello world!» (post 1)
   aparece en la sección «Del blog» del Inicio y en `/blog`, y **empuja fuera** a la entrada real
   «Estamos conectados, pero seguimos solos». Es la única diferencia de copy del Inicio frente a
   producción (similitud 0.987). También siguen presentes «Sample Page» (publicada) y «Privacy
   Policy» (borrador). Runbook §2.2 lo convierte en requisito duro de provisión.
   **Cerrado 2026-09-01 (OWN-024).** **Implementado 2026-09-02** en el plugin **0.7.2**
   ([#10](https://github.com/refo44/demo-caminodeldharma/issues/10)):
   `Cdd_Core_Installer_Demo_Content` reconoce los defaults del instalador por tipo + slug +
   estado (no por ID 1/2/3, que no es estable en un sitio con contenido importado) e ignora
   todo objeto con `_cdd_source_key`; `cdd_core_activate()` y `cdd_core_maybe_upgrade()` los
   **despublican**, y `wp cdd-core demo purge [--apply]` los **borra** (dry-run por defecto,
   guard de producción, y limpia `wp_page_for_privacy_policy` si apuntaba al borrador
   borrado). Cubierto por `tests/Unit/Installer_Demo_ContentTest.php` (11 tests) y
   `tests/WordPress/Demo_Content_RemovalTest.php` (8). `Pass (local)`: unit 201/201,
   wp-phpunit 130/130, PHPCS limpio. El **volumen local ya importado** no queda limpio hasta
   que ese WordPress cargue el plugin 0.7.2 (`init` → `maybe_upgrade`).
3. **D-03 — Feeds nativos abiertos.** `/feed`, `/blog/feed` y `/comments/feed` respondían **200**
   en WordPress y **404** en producción publicada. No están en `docs/11-arbol-urls-final.md`, que
   dice «si una URL no está aquí, no existe». **Cerrado 2026-09-01 (OWN-025 / ADR 0044):** 404
   real. **Implementado 2026-09-02** en el plugin **0.7.3**
   ([#11](https://github.com/refo44/demo-caminodeldharma/issues/11)): `cdd_core_block_feed_requests()`
   convierte cualquier petición con `feed` en la query en un 404 **real** antes de la consulta
   principal —una sola guarda cubre `feed`/`rdf`/`rss`/`rss2`/`atom`, bonita o `?feed=`, del
   sitio, de `/blog`, de comentarios, de archivo y de CPT—, y
   `cdd_core_disable_feed_autodiscovery()` retira `feed_links` (prioridad 2) y `feed_links_extra`
   (prioridad 3) del `head`. Sin 301 a `/blog`, sin 200 con `noindex`, sin cuerpo RSS con estado
   404. El `rel=alternate` `text/calendar` del evento vigente (OWN-014) se mantiene. Cubierto por
   `tests/WordPress/Feed_RoutingTest.php` (6 tests). `Pass (local)`: unit 201/201,
   wp-phpunit 137/137, PHPCS limpio. RSS futuro: POST-010.
4. **D-04 — Regresión a 320 px en `/practica`.** `scrollWidth` 324 vs 320: el `<audio>` del
   bloque nativo `core/audio` toma su ancho intrínseco (~300 px) más el relleno del contenedor.
   **Producción no desborda** (272 px de ancho, `scrollWidth` = `clientWidth` = 320). Es la única
   regresión visual encontrada y es **first-party corregible** (falta un ancho al bloque
   convertido); **Cerrado 2026-09-01 (OWN-026):** arreglar **antes** de staging.
   ([#12](https://github.com/refo44/demo-caminodeldharma/issues/12)).
5. **D-05 — Lightbox nativo en inglés.** `/galeria` rotula «Close / Previous / Next» y
   `aria-label="Enlarged images"` sobre una página `lang="es-CO"`. **Causa ambiental, no de
   código:** `get_locale()` ya devuelve `es_CO`, pero el contenedor no alcanza WordPress.org y
   solo tiene instalado `en_US`. Runbook §2.4 añade `wp language core install es_CO --activate`
   y exige volver a verificar estas cadenas. **Cerrado 2026-09-01 (OWN-027, A):** staging
   `es_CO`; Docker local puede seguir en inglés.
6. **D-06 — `wptexturize` cambia las comillas.** En `/practica`, producción publica
   `"Homenaje al Bodhisattva Guān Shì Yīn"` (comillas rectas) y WordPress rinde `«…»`
   tipográficas. **Cerrado 2026-09-01 (OWN-028, A):** delta aceptado.
7. **D-07 — El bloque nativo de audio no rinde texto alternativo.** Producción incluye «Tu
   navegador no permite reproducir este audio.» dentro de cada `<audio>`; `core/audio` no lo
   emite. Dos ocurrencias en `/practica`. **Cerrado 2026-09-01 (OWN-029, A):** se acepta.
8. **D-08 — Fichas de autor indexables sin `meta description`.** `/author/{slug}` sirve
   `index,follow` (ADR 0037) pero el payload no trae objeto `seo` para `blog_authors` ni para
   `gallery_albums` (0/2 y 0/3; páginas 11/11, eventos 10/10, entradas 2/2 sí lo traen). Es
   coherente con OWN-007 —el estático no publica fichas de autor, así que no hay meta propia que
   portar. **Cerrado 2026-09-01 (OWN-020):** no `noindex` en singles; reutilizar copy corto y
   fotos publicados (JSON-LD del fundador / meta de `/comunidad`). Implementación **pendiente**
   ([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)). Los álbumes siguen `noindex`
   y no entran en esa cola.
9. **D-09 — Desbordamiento heredado en `/blog/sangha-refugio-hiperconexion`.** 339 vs 320 px a
   320 px de ancho, por una URL larga sin puntos de corte en el cuerpo del artículo.
   **Producción desborda exactamente igual (339 vs 320)**. **Cerrado 2026-09-01 (OWN-021):**
   dejar en el corte. Wrap post-corte POST-008 / [#7](https://github.com/refo44/demo-caminodeldharma/issues/7).
10. **D-10 — `wp-emoji` escribe en `sessionStorage`.** El cargador de emoji del núcleo guarda
    `wpEmojiSettingsSupports` en visitantes anónimos; el estático no usaba almacenamiento
    alguno. **No hay petición a `s.w.org`** en navegadores modernos (el script sale antes) y no
    hay cookies. **Cerrado 2026-09-01 (OWN-022, A):** delta aceptado. No desactivar `wp-emoji`.
    ADR 0019 (sin cookies de analítica) no cambia.
11. **D-11 — `wp term list gallery_album` muestra `count = 0`.** Cosmético: la taxonomía vive
    sobre adjuntos (`post_status = inherit`) y el contador del núcleo solo cuenta `publish`. Las
    asignaciones reales son 35 y el theme consulta los adjuntos directamente. Se verá un 0 junto
    a cada álbum en wp-admin. **Cerrado 2026-09-01 (OWN-030, B):** dejar el 0 en el corte;
    higiene post-corte POST-009 / [#13](https://github.com/refo44/demo-caminodeldharma/issues/13).
12. **D-12 — `<html lang>`:** WordPress sirve `es-CO`, producción `es`. Delta deliberado (locale
    más específico). **Cerrado 2026-09-01 (OWN-031, A):** conservar `es-CO`.

### Deltas aceptados que WU-10 vuelve a confirmar, no a corregir

- **`.ics` publicado solo con la bienvenida.** `static/eventos/ical/circulos-de-presencia-consciente.ics`
  sigue con **1 VEVENT** (UID `circulos-de-presencia-consciente@caminodeldharma.org`) frente a
  los **10** de WordPress. Confirmado con `curl` contra producción. No se arregla en `static/`
  (BUG-001 §7).
- **Párrafos del formulario en `/privacidad` (ADR 0041).** El diff local↔producción muestra
  exactamente el delta autorizado: WordPress dice que el formulario se procesa con CF7 y entrega
  al correo de la comunidad; el estático mantiene que no envía nada. Fecha de actualización
  31 ago (WP) vs 29 ago (producción). Correcto.
- **Nota del diálogo de calendario (BUG-001).** Copy nuevo sin equivalente estático, presente y
  correcto: «El archivo .ics incluye las 10 sesiones del curso. Google Calendar y Outlook añaden
  la próxima: Jueves 3 de septiembre de 2026.»
- **Tarjeta compacta de eventos pasados (WU-07, doc 03 §3).** `/eventos` mide similitud 0.663
  frente a producción: WordPress rinde `Ciudad · Fecha` + «Ver evento →» donde producción publica
  descripción completa y filas Fecha/Hora/Lugar/Modalidad. Son **134 líneas menos de copy** en
  esa página. Sustitución aceptada en WU-07; se deja constancia de la magnitud medida.
- **Byline enlazada (ADR 0037)** en `/comunidad` y en las entradas, y **tiempo de lectura 6′ vs
  8′** en `sangha-refugio-hiperconexion`: deltas ya registrados en WU-07.
- **Deck como excerpt** en `/blog`: delta ya registrado en WU-07.
