# Fase 3 — Estado de ejecución durable

Artefacto de continuidad exigido por FABLE5 v2.6 §12. Otra sesión debe poder retomar el trabajo
leyendo este archivo y verificándolo contra Git, sin historial de chat.

| | |
| --- | --- |
| **Última actualización** | 2026-08-31 (WU-10: QA local completa y runbook de staging) |
| **Fase** | Fase 3 — WordPress (iniciada) |
| **Work unit activo** | Ninguno — WU-00…**WU-10 cerrados** (WU-09 y BUG-001 incluidos); checkpoint de WU-10 alcanzado |
| **Rama** | `fase3-wordpress` — al abrir WU-10, `HEAD` = `origin/fase3-wordpress` = `e377c46`, **0 ahead / 0 behind**: los commits de BUG-001 **ya estaban publicados**, al contrario de lo que decía esta tabla. Los 4 commits de WU-10 quedan **solo en local**, sin push, por instrucción |
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

- **WU-08B — SEO first-party, noindex, redirects, OWN-015 y a11y** (sesión 2026-08-31, FABLE5
  §9.5 + §10 únicamente; ADR 0025/0030/0031/0034/0036/0037/0038, OWN-007/013/014/015; TDD
  honesto: RED documentado en unit (17 E + 12 F) **y** wp-phpunit (17 E + 3 F) antes del primer
  archivo de SEO):
  - Reanudación verificada: preflight limpio + rerun de los gates (unit 119, phpcs, wp-phpunit
    75, plugin 0.5.0 y theme 0.3.0 activos).
  - Plugin **0.6.0**: `includes/seo/class-cdd-core-seo-document.php` y `class-cdd-core-json-ld.php`
    (puras: la cabeza y el grafo como datos, «nunca inventar un campo opcional»);
    `includes/seo.php` (contexto por request, `noindex,follow` de `/author`/álbumes/tags/404,
    `rel=alternate` del `.ics` solo vigente, rebase de toda URL guardada a `home_url()`,
    `cdd_core_default_locale()`); `includes/seo/class-cdd-core-sitemap-posts.php` (el archivo
    `/eventos` en el sitemap; el núcleo no expone filtro sobre la lista terminada);
    `includes/admin.php` («Eliminar huérfanos», OWN-015, solo `.ics`, dry-run + nonce +
    capacidad); meta editable `seo_*`/`og_*`/`event_attendance_mode`/`seo_jsonld_extra`/
    `seo_related_url` y term meta `cdd_region`;
    `includes/migration/class-cdd-core-seo-extractor.php` + payload (`seo` por objeto y sección
    **no contada** `site`), importador (meta, opción `cdd_core_seo_site`, regiones, `tag_base`,
    zona horaria) y `migrate convert` (siembra add-only de la cabeza).
  - Theme **0.4.0**: `inc/seo.php` imprime y escapa el documento y retira el `<title>`, el
    `canonical`, el `robots` y el skip link duplicado del núcleo; `templates/archive.html` y
    `templates/archive-blog_author.html` dan su `h1` a `/blog/tag/{slug}` y a `/author`;
    `focusable="false"` en los 4 SVG decorativos que producción publica sin él.
  - `wordpress/.htaccess`: ledger portado **encima** del bloque que WordPress reescribe,
    verificado con `curl` sobre el Apache real del contenedor (un salto por regla, 410 reales,
    sin cadenas ni loops). No portan las reglas solo-estáticas; la condición HTTPS pasa de `[OR]`
    a AND para no heredar un bucle latente. El estático no se toca.
  - Cinco defectos cazados por la verificación HTTP real (no por la suite): `<title>` y `robots`
    duplicados del núcleo (el block theme registra su printer de título **dentro** de
    `locate_block_template()`, después de `wp` y de `template_redirect`; y el núcleo engancha
    ambos en prioridad 1, no 10); JSON escapado perdiendo las barras invertidas al sembrarse sin
    `wp_slash()` («Círculos» → «Cu00edrculos»); imagen social por defecto apuntando a producción;
    `<html lang="en-US">` (WCAG 3.1.1); y el bucle latente del `[OR]`.
  - QA: unit 156/156 (808 asserts), wp-phpunit 106/106 (649 asserts), phpcs 0/0 sobre 81
    archivos, stylelint verde, `php -l` OK, `debug.log` sin entradas propias. Pipeline aplicado
    en el entorno local (16 objetos sembrados; 2.º apply = 0). Pase docs/19 en navegador real
    sobre 13 rutas, 320px y 640px. Matriz § WU-08B. QA 4 con lector de pantalla y staging siguen
    `Unverified`.

- **WU-09 — Contact Form 7 y los párrafos del formulario en `/privacidad`** (sesión 2026-08-31,
  FABLE5 §10.3 + §10.4 únicamente; ADR 0025/0026/0038/0041, OWN-007/018; TDD honesto: RED
  documentado en unit (15 E + 2 F) **y** wp-phpunit (5 E + 3 F) antes del primer archivo):
  - Reanudación verificada: preflight limpio (HEAD `f860561`) + rerun de los gates (unit 156,
    wp-phpunit 106, php-lint, plugin 0.6.0 y theme 0.4.0 activos).
  - **CF7 6.1.7** instalado y activado en el entorno local. Su código vive en el volumen
    `wp_data`, **nunca** en el árbol del repositorio (`git status` limpio tras instalarlo);
    versión y procedimiento por entorno en `docs/operations/third-party-plugins.md`.
  - Plugin **0.7.0**: `includes/class-cdd-core-contact-form-template.php` (pura: plantilla del
    formulario = maquetado publicado con `[text*]`/`[email*]`/`[textarea*]`, conservando labels,
    `for`/`id`, `name`, `autocomplete`, los iconos y el `<button>` con el suyo; plantilla del
    correo a `caminodeldharma1@gmail.com` con `Reply-To: [correo]` y texto plano; los 8 mensajes
    en español; decisión de `autop`); `includes/contact.php` (opción `cdd_core_contact_form_id`,
    `cdd_core_contact_form_available/html/recipient`, `cdd_core_privacy_delta_applied`,
    `cdd_core_provision_contact_form` con **todos** los bloqueos a la vez, filtro
    `wpcf7_autop_or_not` acotado a este formulario); CLI `wp cdd-core contact provision [--apply]`;
    `Cdd_Core_Content_Converter::convert_contacto/convert_privacidad` + `privacidad_delta_applied`
    y las dos constantes con el copy aprobado; `Cdd_Core_Spanish_Date::long_form()`; el servicio
    de conversión recorre **`privacidad` antes que `contacto`**.
  - Theme **0.5.0**: bloque `camino-del-dharma/contacto-formulario` (14 en total) que rinde CF7 o,
    con CF7 apagado, los canales WhatsApp/correo publicados; CSS para lo que CF7 añade al
    maquetado (el wrapper de control pasa a `display:block`) y para el aviso degradado.
  - Orden ADR 0041 respetado en el entorno: con CF7 **desactivado**, `contact provision` rehúsa
    con los dos bloqueos → `migrate convert --apply` (privacidad + contacto; 2.º apply = 0) →
    activar CF7 → `contact provision --apply` (2.º = rehúsa). El `diff` del aviso son **4 hunks**,
    ninguno fuera del alcance aprobado; el estático no se toca (`git diff -- static/` vacío).
  - Verificado en navegador real: DOM del formulario = el publicado (labels, ids, `name`,
    `autocomplete`, iconos, botón, `section-gap`, `aria-label`), **0 `<p>` espurios**; los 3
    estados de envío en español (vacío, correo inválido, datos válidos); el fallback con CF7
    apagado sin shortcode en crudo; a11y y 320px sin desbordes; consola limpia.
  - **La entrega de correo no está demostrada**: el contenedor local no tiene MTA y `wp_mail()`
    devuelve `false` (comprobado directamente). La validación sí está probada de extremo a extremo.
  - QA: unit 175/175 (913 asserts), wp-phpunit 114/114 (677 asserts), phpcs 0/0 sobre 85 archivos,
    stylelint verde, `php -l` OK, `debug.log` sin entradas propias. Matriz § WU-09.
- **BUG-001 — El `.ics` de Círculos incluye todas las sesiones** (sesión 2026-08-31, commits
  `6c8fdb6` tests → `23c1ae4` plugin → `80d752a` theme; el commit de docs va después):
  - Reanudación verificada: preflight limpio + rerun de los gates previos (unit 175, wp-phpunit
    114, plugin 0.7.0 y theme 0.5.0 activos). Hallazgo: `origin/fase3-wordpress` ya estaba en
    `78db8f7`, no en `f860561` — los 5 commits de WU-09 se habían subido fuera de esta sesión.
  - Tests RED primero: 5 fallos de nivel 1 y 2 errores + 3 fallos de nivel 2 antes de tocar
    ningún archivo de comportamiento.
  - Plugin **0.7.1**: `Cdd_Core_Ics_Generator` emite **un VEVENT por sesión** de
    `event_calendar_dates` (UID propio `slug-Ymd@host`, fin exclusivo de día completo por
    ocurrencia) dentro del sobre VCALENDAR de producción; sin cronograma, el rango
    `event_date`/`event_end` con el UID publicado, como hasta hoy.
    `cdd_core_event_calendar_payload()` gana `occurrences`, `session_count` y `next` y acepta el
    instante de la petición; `cdd_core_ics_occurrence()` traduce la ocurrencia a la forma
    inclusiva que consume el generador.
  - Theme **0.5.1**: el disparador de un curso enlaza la **próxima sesión** en vez del rango, e
    imprime `data-calendar-sessions` y `data-calendar-note`; `calendar-dialog.js` pinta la nota y
    la expone como `aria-describedby` del diálogo; `.calendar-dialog-note` en `main.css`. Un
    evento sin cronograma no imprime nada nuevo.
  - Verificación real: `curl` sobre `/eventos/ical/circulos-de-presencia-consciente.ics` devuelve
    200 con **10 VEVENT**; los eventos finalizados siguen en 410 (OWN-012). En el navegador, el
    diálogo enlaza `dates=20260903/20260904` y muestra la nota.
  - **El estático no se tocó**: sigue publicando su VEVENT único de la bienvenida hasta el corte.
  - QA: unit 183/183 (1023 asserts), wp-phpunit 121/121 (723 asserts), phpcs 0/0 sobre 85
    archivos, stylelint verde, `php -l` OK. Matriz § BUG-001.

- **WU-10 — QA local completa y runbook de staging** (sesión 2026-08-31, commits `e801c4a`,
  `c24023f`, `dfb5055` + este commit de estado). **No es una escritura en Hostinger**: WU-10
  produce evidencia y runbook; no se creó, desplegó ni importó en ninguna instancia (OWN-005).
  - Reanudación verificada contra Git: `HEAD` = `origin/fase3-wordpress` = `e377c46`, árbol
    limpio, 0 ahead / 0 behind. **El archivo estaba desactualizado**: los commits de BUG-001 ya
    estaban publicados. El repositorio manda.
  - **Niveles 1–3 re-ejecutados contra el árbol actual**, no heredados: `php -l` OK; unit
    **183 tests / 1023 assertions**; wp-phpunit **121 tests / 723 assertions**; phpcs **85
    archivos, 0/0**; `composer audit --locked` sin advisories; stylelint exit 0;
    `git diff --check` limpio; JSON/YAML válidos; sin secretos; sin plantillas PHP clásicas.
  - Integración: **41 rutas entrantes** correctas (200/301/404), rutas `.ics` 200/410/404,
    `debug.log` **0 bytes** tras navegación representativa, **sin cookies anónimas** en 11
    superficies, sin analítica, `verify` con `missing: []`, 35 asignaciones de álbum,
    idempotencia de `import`/`seed`/`convert`/`contact provision`.
  - Nivel 4 hasta donde llega un navegador local: 19 rutas a 320 px y a 640 px (= zoom 200 %),
    teclado, foco visible, diálogo modal con foco devuelto, y **diff de copy contra
    `https://caminodeldharma.org`** (OWN-007) en 14 superficies.
  - **12 deltas/hallazgos** registrados en la matriz § WU-10. Los dos que condicionan staging:
    **D-01** `event_modality` vacío en los 9 eventos con modalidad (entorno local importado con
    un payload anterior; el importador es create-missing-only y **no rellena** al reimportar —
    el código está bien: extractor, importador y renderizador verificados), y **D-04** `/practica`
    desborda 4 px a 320 px por el `core/audio` nativo, donde **producción no desborda**.
  - `docs/operations/wordpress-manual-deployment.md` **v1.1 → v2.0**: provisión de staging
    (instalación limpia, `blog_public 0`, paquete `es_CO`, remitente real), separación
    código-ZIP / contenido-WP-CLI, orden `validate → import --apply → seed --apply → verify →
    convert --apply` y después CF7 `plugin install → convert --apply → contact provision
    --apply`, guard de producción, verificación posterior y rollback.

## Riesgos y hallazgos activos

- Paridad repo↔Hostinger **verificada** (WU-06, delta 0). Re-verificar en el freeze pre-corte.
- `descargas/Resumen programa EVF.mp4` en la raíz, no trazado y fuera de la receta ZIP: se
  preserva intacto; clasificar en el inventario si aparece referenciado.
- `_config.yml` y `.nojekyll` son restos de GitHub Pages (desactivado 2026-07-28), fuera del
  ZIP; su retiro es una limpieza separada.
- **`test.yml` no se ha ejecutado nunca** (verificado en WU-10 con `gh run list`: los únicos runs
  del repositorio son `pages-build-deployment` en `main`, de julio 2026). El motivo **no** es que
  falte un push —la rama está publicada— sino que el workflow dispara solo en `push: branches:
  [main]` y `pull_request`, y **no existe PR**. Obtener evidencia de CI exige abrir un PR o
  ampliar los triggers: decisión del propietario. Sonar sigue `Unverified` (no revisado).
- El entorno local ya usa la estructura definitiva `/blog/%postname%` (aplicada por el
  importador). Contenido demo del install local («Sample Page», «Hello world!») convive con
  el contenido importado; el staging partirá limpio. En staging: `import --apply` →
  `seed` → `convert --payload=<path> --apply` (la conversión es parte del pipeline
  documentado; el `--payload` solo hace falta si algún objeto se importó antes de que el
  copy de compartir viajara — en una importación limpia la meta ya viene del importador).
- **QA 4 ejecutado en WU-10** hasta donde llega un navegador local: 320 px, 640 px (= zoom 200 %),
  teclado, foco visible y diff de copy contra producción publicada en 14 superficies. Siguen
  `Unverified`: **lector de pantalla real**, PHP/Apache/HTTPS de staging, no indexabilidad de
  staging y entrega real de CF7.
- **D-01 (WU-10) — el entorno local está desalineado con el payload vigente.** `event_modality`
  está vacío en los 9 eventos que tienen modalidad: ese contenido se importó con un payload
  anterior al campo y el importador es **create-missing-only**, así que reimportar no lo rellena.
  El código está bien (extractor, importador y renderizador verificados uno a uno). Implica que
  **el QA local subestima la fidelidad** en esa fila y que **staging debe importarse desde cero**
  (runbook §4b).
- **D-04 (WU-10) — única regresión visual encontrada:** `/practica` desborda 4 px a 320 px por el
  `<audio>` del bloque nativo `core/audio`; producción no desborda. Registrada, **no arreglada**
  (fuera del alcance de WU-10).
- **D-03 (WU-10)** — `/feed`, `/blog/feed` y `/comments/feed` responden 200 en WordPress y 404 en
  producción, y no están en `docs/11-arbol-urls-final.md`. Superficie nueva indexable pendiente
  de decisión del propietario.
- **OWN-019 / ADR 0042 (2026-09-01):** `META-001`–`META-005` **no** son cola de pre-staging ni
  defectos de corte. Restricciones para UI wp-admin futura. Staging no construye metaboxes
  clásicos. El guard de autores no se relaja.
- **D-05 (WU-10)** — el lightbox nativo rotula en inglés porque el contenedor no puede instalar
  `es_CO`. Ambiental; el runbook lo cubre y en staging queda `Unverified` hasta verificarlo.
- **WU-08B cerrado.** Queda `Unverified` el pase con lector de pantalla real y todo lo que
  exige staging. La sección `site` del payload (defaults sociales, cabeza de `/eventos`, `@graph`
  del Inicio) **no tiene UI en wp-admin**: se edita por WP-CLI (`wp option get/update
  cdd_core_seo_site`) hasta que una fase posterior le dé pantalla.
- **Commit ajeno en el historial:** `3c3d513` («feat: enhance SEO functionality and improve event
  handling») se creó fuera de esta sesión y capturó una instantánea a medias del árbol de trabajo
  de WU-08B; el resto de esos mismos arreglos quedó en `9a00f97`. El árbol final es correcto y
  todos los gates están verdes, pero el mensaje no sigue la convención del repositorio. La rama
  ya está **publicada** en `origin` hasta `f860561`, así que fundir `3c3d513` en `9a00f97`
  exigiría un force-push: decisión del propietario, no una limpieza silenciosa.
- **BUG-001 (Círculos `.ics`) — cerrado.** El exportado de WordPress incluye ahora **las diez
  sesiones** de `event_calendar_dates`, un VEVENT con UID propio cada una; el diálogo enlaza la
  próxima sesión (una fecha que el archivo contiene) y lo dice. El **estático sigue publicando
  su VEVENT único de la bienvenida**: no se toca hasta el corte, y ese delta queda registrado en
  la matriz § BUG-001, no arreglado en `static/`.
- **ADR 0041 / OWN-018 — cerrado en WU-09.** El delta de copy está aplicado en la Page de
  WordPress y el estático sigue intacto. Queda **una sola cosa abierta y es bloqueante para el
  release, no para el corte**: la **entrega real** a `caminodeldharma1@gmail.com` desde staging
  Hostinger. `Pass (local)` no basta (ADR 0026/0041 punto 5). Si allí falla, el corte puede
  seguir con CF7 deshabilitado y WhatsApp/correo — el bloque del theme ya rinde ese estado— y se
  registra en matriz y checklist. **Fallo operativo, no gate jurídico.**
- **La rama se publica fuera de estas sesiones (hallazgo de WU-09, confirmado en BUG-001).**
  Al abrir la sesión de BUG-001, `origin/fase3-wordpress` estaba en **`78db8f7`**, no en
  `f860561`: los 5 commits de WU-09 —que este archivo describía como «solo en local»— ya se
  habían subido. Consecuencia: la nota de que «CI/Sonar quedan `Unverified` hasta que exista
  push» está **obsoleta** para todo lo anterior a BUG-001 — puede haber ejecuciones de
  `test.yml` en el remoto que nadie ha revisado. Los 3 commits de BUG-001 (más el de docs) siguen
  sin subir, por instrucción. Antes del siguiente push conviene mirar el estado de CI en el
  remoto y decidir qué hacer con `3c3d513` (el commit ajeno), que **ya está publicado**:
  reescribirlo obligaría a un force-push.
- **CF7 no está en Git y por eso el harness no lo ejecuta.** La rama «CF7 presente» se prueba
  contra un entorno real, no en la suite; la suite cubre lo propio en ambos estados. Al
  provisionar un entorno nuevo hay que anotar la versión instalada en
  `docs/operations/third-party-plugins.md`.
- Deltas de copy registrados (matriz WU-07): fechas generadas, filas `Hora`/`Aporte` fuera
  del modelo, resumen de card vigente = intro del single, excerpt del listado = deck,
  tiempo de lectura de Sangha 6′ vs 8′, label «Preinscribirme», byline enlazada.

## Decisiones/asunciones usadas

- Autorización del propietario 2026-08-31: el `.ics` de Círculos que solo cubre la bienvenida
  (estático) **y** el VEVENT único de rango (WP) son **BUG-001**. El exportado debe incluir
  todas las sesiones. Sesión propia **justo antes de WU-10**. No se inventa un campo de
  «bienvenida». **Ejecutado y cerrado el 2026-08-31.**
- BUG-001: 7 decisiones/deltas registrados en la matriz § BUG-001. Las tres que condicionan
  trabajo futuro: (1) el UID solo se sufija (`slug-Ymd@host`) cuando hay cronograma, de modo que
  un evento sin sesiones conserva el UID que producción ya publicó y nadie ve duplicados;
  (2) un enlace profundo de Google/Outlook lleva una sola entrada, así que el diálogo nombra la
  **próxima** sesión en lugar de un rango que no existe en ningún VEVENT, y una nota nueva —copy
  propio, no publicado— lo explica; (3) el payload lleva las ocurrencias en dos formas
  (inclusiva para el generador, compacta con fin exclusivo para los enlaces) y
  `cdd_core_ics_occurrence()` es el único punto de traducción: mezclarlas empujaba cada `DTEND`
  un día de más.
- Autorización del propietario 2026-08-31: CF7 en el corte **sin** espera de asesoría legal
  (OWN-018, ADR 0041, FABLE5 v2.4). El disclaimer de `/privacidad` basta para lanzar. Copy
  WordPress del formulario = delta field-scoped; estático intacto. **Ejecutado en WU-09.**
- Autorización del propietario 2026-09-01: `META-001`–`META-005` **no** son defectos de corte
  (OWN-019, ADR 0042). Restricciones para UI wp-admin futura. Staging no construye metaboxes
  clásicos. El guard de autores no se relaja.
- WU-09: 12 decisiones/deltas registrados en la matriz § WU-09. Los cinco que condicionan trabajo
  futuro: (1) el repositorio posee la *definición* de CF7, no su código, y `contact provision` es
  create-missing-only —lo que un editor cambie en wp-admin no se pisa; (2) se conserva el
  `<button>` publicado en vez de `[submit]` (CF7 escucha el evento `submit`), a costa del spinner
  que el DOM publicado tampoco tenía; (3) el formulario es un **bloque del theme**, no un
  shortcode en el contenido, lo que además protege `/contacto` de KSES; (4) los mensajes que lee
  un visitante son propios porque el locale sale de `cdd_core_default_locale()` y WordPress no
  instala paquete de traducción de CF7 —«spam» y «fallo de envío» comparten texto a propósito;
  (5) no se inventa una pareja de colores error/éxito: el estático nunca tuvo un formulario que
  enviara, así que los colores de estado siguen siendo los de CF7.
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

Ver `.audit/fase3-validation-matrix.md` § BUG-001 (y § WU-09 para la unidad anterior). Estados:
`Unverified`, `Pass (local)`, `Pass`, `Fail`.

## Bloqueos

- Ninguno para el trabajo de repositorio. Siguiente hito: **crear la instancia de staging en
  Hostinger** — exige autorización expresa del propietario (OWN-005). Orden restante:
  staging → corte.

## Archivos cambiados en WU-10

Solo documentación y evidencia. **Sin cambios** en `static/`, en `wordpress/` (plugin y theme
intactos), en `migration/payload.json` ni en `wordpress/.htaccess`. Ningún test nuevo: WU-10 no
implementa comportamiento, lo verifica.

- `docs/operations/wordpress-manual-deployment.md` (v1.1 → **v2.0**, runbook listo para staging)
- `.audit/fase3-validation-matrix.md` (§ WU-10: niveles 1–4, CI, 12 deltas)
- `docs/matriz-migracion-static-wordpress.md` (v1.4 → **v1.5**, los cinco entregables con
  columnas separadas local / staging / producción)
- `.audit/fase3-execution-state.md` (este archivo)

## Archivos cambiados en BUG-001

- Tests ampliados (RED antes de la implementación): `tests/Unit/Ics_GeneratorTest.php`
  (cronograma de 10 sesiones, UID por sesión, día completo, sesión multidía, fallback de rango),
  `tests/Unit/Theme_BehaviorTest.php` (nota del diálogo), `tests/WordPress/Event_QueriesTest.php`
  (`occurrences`/`session_count`/`next`, enlace profundo a la próxima sesión, 410 con cronograma,
  fallback de rango), `tests/WordPress/ThemeRenderTest.php` (atributos del disparador con y sin
  cronograma).
- Plugin `camino-del-dharma-core` 0.7.0 → **0.7.1**:
  `includes/class-cdd-core-ics-generator.php` (un VEVENT por ocurrencia, UID por sesión),
  `includes/events.php` (`cdd_core_event_calendar_occurrences()`,
  `cdd_core_calendar_occurrence()`, `cdd_core_event_calendar_deep_link()`,
  `cdd_core_ics_occurrence()`; payload y ruta `.ics` con el instante de la petición),
  `camino-del-dharma-core.php` (versión).
- Theme `camino-del-dharma` 0.5.0 → **0.5.1**:
  `inc/class-camino-del-dharma-renderers.php` (`calendar_schedule_attributes()`),
  `assets/js/calendar-dialog.js` (nota + `aria-describedby`), `assets/css/main.css`
  (`.calendar-dialog-note`), `style.css` (versión).
- `.audit/fase3-execution-state.md`, `.audit/fase3-validation-matrix.md`,
  `docs/backlog-decisiones-owner-migracion.md`, `docs/migracion-static-wordpress.md`,
  `docs/17-orden-implementacion.md`, `docs/matriz-migracion-static-wordpress.md`, READMEs de
  plugin y theme, `CHANGELOG.md`, `CLAUDE.md`, `AGENTS.md`

## Archivos cambiados en WU-09

- Tests nuevos (RED antes de la implementación): `tests/Unit/Contact_Form_TemplateTest.php`,
  `tests/WordPress/Contact_FormTest.php`; ampliados `tests/Unit/Content_ConverterTest.php`,
  `tests/Unit/Spanish_DateTest.php`, `tests/Unit/Theme_BehaviorTest.php`.
- Plugin `camino-del-dharma-core` 0.6.0 → **0.7.0**:
  `includes/class-cdd-core-contact-form-template.php` e `includes/contact.php` (nuevos);
  `includes/migration/class-cdd-core-content-converter.php`, `class-cdd-core-convert-service.php`,
  `class-cdd-core-cli.php`, `class-cdd-core-spanish-date.php`, bootstrap.
- Theme `camino-del-dharma` 0.4.0 → **0.5.0**: `inc/blocks.php`, `assets/css/main.css`,
  `style.css`.
- Docs: `docs/operations/third-party-plugins.md` (versión instalada + procedimiento por entorno),
  `docs/cutover-checklist-wordpress.md`, `docs/matriz-migracion-static-wordpress.md`,
  READMEs de plugin y theme, `CLAUDE.md`, `AGENTS.md`, `CHANGELOG.md`,
  `.audit/fase3-validation-matrix.md`, este archivo.
- **Sin cambios** en `static/`, en `migration/payload.json` (WU-09 no altera contenido extraído)
  ni en `wordpress/.htaccess`.

## Archivos cambiados en WU-08B

- Tests nuevos (RED antes de la implementación): `tests/Unit/Seo_ExtractorTest.php`,
  `Seo_DocumentTest.php`, `Htaccess_LedgerTest.php`, `Theme_SeoTest.php`,
  `tests/WordPress/Seo_HeadTest.php`, `Seo_SitemapTest.php`, `Orphans_ToolTest.php`; ampliados
  `tests/Unit/Page_ExtractorTest.php`, `Blog_ExtractorTest.php`, `Event_ExtractorTest.php`,
  `Payload_BuilderTest.php`, `Theme_TemplatesTest.php`, `tests/WordPress/ImporterTest.php`,
  `ConvertTest.php`.
- Plugin `camino-del-dharma-core` 0.5.0 → **0.6.0**: `includes/seo.php`, `includes/admin.php`,
  `includes/seo/class-cdd-core-seo-document.php`, `class-cdd-core-json-ld.php`,
  `class-cdd-core-sitemap-posts.php`,
  `includes/migration/class-cdd-core-seo-extractor.php` (nuevos); `includes/meta.php`,
  `class-cdd-core-page-extractor.php`, `class-cdd-core-blog-extractor.php`,
  `class-cdd-core-event-extractor.php`, `class-cdd-core-payload-builder.php`,
  `class-cdd-core-importer.php`, `class-cdd-core-convert-service.php`, bootstrap.
- Theme `camino-del-dharma` 0.3.0 → **0.4.0**: `inc/seo.php`, `templates/archive.html`,
  `templates/archive-blog_author.html` (nuevos); `functions.php`, `patterns/footer.php`,
  `inc/class-camino-del-dharma-renderers.php`, `style.css`.
- `wordpress/.htaccess` (nuevo, artefacto desplegable), `tools/extract-payload.php`,
  `migration/payload.json` regenerado.
- Docs: `docs/15-assets-strategy.md`, `docs/11-arbol-urls-final.md`, `docs/redirect-ledger.md`,
  `docs/operations/wordpress-manual-deployment.md`, READMEs de plugin y theme, `CLAUDE.md`,
  `AGENTS.md`, `CHANGELOG.md`, `.audit/fase3-validation-matrix.md`, este archivo.

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

`e377c46` — cierre de BUG-001 y **punto de partida verificado de WU-10** (QA local re-ejecutada sobre este árbol: unit 183/1023, wp-phpunit 121/723, phpcs 0/0). WU-10 no añade commits de implementación, solo los 4 de documentación/evidencia (`e801c4a`, `c24023f`, `dfb5055` + este). La implementación anterior fue `9793a3c` (WU-09: unit 175, wp-phpunit 114). Baseline visual del theme:
`d3b30f5` (docs/12 §8). Historial en `fase3-wordpress`: `5088e32` (WU-00) →
`bfb6dc0`/`54cd09f` (WU-01) → `11237a1` → `b9c9eb8` (WU-02) → `81d7547` → `fe33b96` (WU-03)
→ `36d368b` → `d3b30f5` (WU-04) → `196ef78` → `e8c52c9` (WU-05) → `c270a37` →
`e9f4234` (WU-06) → `044f7d6` → `6dffb0d` (WU-07) → `2bd733d` → `150d8b8` (gobernanza) →
`c94635f` (WU-08A) → `5354449` → `d73fecd` (estado) → `e5dbab0` → `e14ef8e` → `3c3d513`
(ajeno) → `9a00f97` → `081b2f8` → `f860561` (WU-08B) → `1492d01` → `0ee2e3a` →
`9793a3c` → `78db8f7` (WU-09) → `6c8fdb6` → `23c1ae4` → `80d752a` → `e377c46` (BUG-001) →
`e801c4a` → `c24023f` → `dfb5055` → commit de estado (WU-10, sin push).

## Estado del entorno local

Contenedores del proyecto `camino-del-dharma` levantados al cierre (`docker compose stop`
para pararlos). WordPress local: `http://localhost:8081`. Plugin `camino-del-dharma-core`
**activo** (v0.7.1, upgrade automático). Theme `camino-del-dharma` **activo** (v0.5.1,
vistas reales + comportamiento + cabeza + formulario + `.ics` por sesión). **Contact Form 7 6.1.7 activo** con el
formulario provisionado (`wp cdd-core contact provision --apply`); `/privacidad` lleva el delta
de ADR 0041 y `/contacto` el bloque del formulario. Akismet sigue inactivo (sin antispam extra). Contenido importado (payload 1.0.35/`bfb6dc0`) **y
convertido** por `wp cdd-core migrate convert --payload=/repo/migration/payload.json --apply`
(inicio con bloques dinámicos y `<picture>` desenvueltos; galeria con 3 galerías nativas;
comunidad con enlaces OWN-016; practica con 2 `core/audio` nativos; copy de compartir
sembrado en el evento vigente y los 2 posts). También sembrado el SEO publicado (16 objetos) y la opción `cdd_core_seo_site`. Permalinks
`/blog/%postname%`; `tag_base` `blog/tag`; zona horaria `America/Bogota`; front page `inicio`,
posts page `blog`. El `.htaccess` del contenedor se restauró al de WordPress por defecto tras
verificar el artefacto desplegable (el de producción redirige a https y rompería el localhost). Contenido demo del install
(«Sample Page», «Hello world!») sigue presente; el staging partirá limpio. El harness
efímero `cdd-wp-phpunit` no deja contenedores ni volúmenes.

## Próxima acción exacta

WU-10 está cerrado (checkpoint alcanzado; sin push ni despliegue). **Los gates de repositorio de
FABLE5 §14 están cubiertos**; lo que falta es, por definición, evidencia de staging.

Siguiente hito: **crear la instancia de staging en Hostinger y ejecutar el runbook v2.0**. Exige
**autorización expresa del propietario en la sesión** (OWN-005); ninguna sesión anterior la
concede. No mezclar con el corte final, que tiene su propio checklist.

**D-08 / OWN-020 (2026-09-01):** cerrado. Fichas `/author/{slug}` indexables con copy corto y
fotos publicados. Implementación **pendiente**
([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)). No es copy sin dueño ni
`noindex`. Git: trabajar desde `main` (ADR 0043); no reanudar en `fase3-wordpress`.

**D-09 / OWN-021 (2026-09-01):** cerrado. Dejar el overflow 339 vs 320 px de
`/blog/sangha-refugio-hiperconexion` en el corte (paridad live). Wrap WordPress-only **después**
de que WP sea producción en `caminodeldharma.org`: POST-008 /
[#7](https://github.com/refo44/demo-caminodeldharma/issues/7).

**D-10 / OWN-022 (2026-09-01):** cerrado (A). `sessionStorage` de `wp-emoji` aceptado; no código.

Antes de abrir esa sesión conviene decidir tres cosas con el propietario:

1. **D-01 / D-04** (matriz § WU-10): si `event_modality` y el desbordamiento de `/practica` a
   320 px se corrigen antes de staging. D-01 se resuelve solo con una importación limpia; D-04
   necesita un arreglo first-party de una línea en el CSS del bloque de audio.
2. **D-03**: qué hacer con `/feed`, `/blog/feed` y `/comments/feed`, que responden 200 y no están
   en `docs/11-arbol-urls-final.md`.
3. **CI**: abrir un PR (o ampliar los triggers de `test.yml`) si se quiere evidencia de CI antes
   del corte; y qué hacer con `3c3d513`, ya publicado.

Pendiente bloqueante del **release** (no del corte): verificar en staging que el formulario
entrega en `caminodeldharma1@gmail.com`. `wp_mail()` falla en local por falta de MTA y por el
remitente `wordpress@localhost`, así que la entrega solo puede probarse allí. Si falla, corte con
CF7 deshabilitado + WhatsApp/correo, registrado en matriz y checklist.

## Procedimiento de reanudación

```bash
git status --short
git checkout main && git pull origin main
git branch --show-current   # esperar: main (no fase3-wordpress; ADR 0043)
git log -5 --oneline
```

Leer este archivo, verificarlo contra Git (¿existe el theme con `theme.json`? ¿`main` tiene
WU-00–WU-10?). Trabajo nuevo: Conventional Branch desde `main`, PR, nunca commit directo al
tronco. Rerun del último gate QA relevante de la matriz y continuar desde «Próxima acción
exacta». El estado del repositorio prevalece sobre la memoria de chat.
