# Fase 3 — Matriz de validación

Evidencia QA por work unit (FABLE5 v2.3 §11). Estados permitidos: `Unverified`,
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
