# camino-del-dharma-core

Plugin de dominio (ADR 0024). Scaffold creado en Fase 3 WU-03 con TDD desde la primera
línea (ADR 0038): `camino-del-dharma-core.php` nació después de un test en rojo
(`tests/Unit/Plugin_BootstrapTest.php`, `tests/WordPress/Plugin_LoadedTest.php`).

- Prefijo de primer partido: `cdd_core` (constantes `CDD_CORE_*`; WPCS rechaza prefijos de
  3 caracteres, ver `phpcs.xml.dist`). Text domain: `camino-del-dharma-core`.
- Dominio desde WU-05 (v0.2.0): CPT `event` + taxonomías no públicas
  `event_type`/`event_city` (ADR 0022/0035), `gallery_album` (ADR 0036), CPT
  `blog_author` + relación `authors` con guard de publicación (ADR 0037), estado del
  evento a tiempo de request en `America/Bogota` (OWN-013), datos del calendario mensual y
  ruta `.ics` generada `/eventos/ical/{slug}.ics` (OWN-009/OWN-012). Clases puras en
  `includes/class-cdd-core-*.php`; registro/hooks en los demás `includes/*.php`.
- Migración desde WU-06 (v0.3.0, ADR 0032/0033): extractores puros en
  `includes/migration/`, payload versionado `migration/payload.json`
  (`tools/extract-payload.sh`) e importador WP-CLI `wp cdd-core migrate
  validate|plan|import|verify` + `wp cdd-core seed` — dry-run por defecto, `--apply`
  explícito, idempotente, create-missing-only, guard de producción.
- Conversión desde WU-07 (v0.4.0): `wp cdd-core migrate convert` — edición field-scoped
  del contenido importado (dry-run por defecto, `--apply`, idempotente, guard de
  producción): inicio (aside destacado y cards del blog → bloques dinámicos del theme;
  `<picture>`/miniaturas hechas a mano → biblioteca), galeria (galerías Gutenberg por
  álbum, ADR 0021/0036), comunidad (enlaces a fichas de autor, OWN-016). Tras convertir,
  el hash del contenido deja de coincidir con `_cdd_source_hash`: así se marca lo editado
  y el importador nunca lo pisa. Queries de presentación para el theme:
  `cdd_core_past_events`, `cdd_core_posts_by_blog_author`, `cdd_core_album_attachments`.
- Comportamiento desde WU-08A (v0.5.0): meta editable de compartir
  `share_whatsapp`/`share_x`/`share_threads` en `event` y `post` (texto plano con el
  placeholder `{{SHARE_URL}}`; extraída del estático por `Cdd_Core_Share_Extractor`),
  `cdd_core_event_calendar_payload()` como **fuente única** del diálogo «Añadir al
  calendario» y del `.ics` generado, y dos pasos nuevos de `migrate convert`: `practica`
  (reproductores de mantras → bloques `core/audio` nativos) y la siembra add-only de la
  meta de compartir cuando se pasa `--payload=<path>`.
- SEO y mantenimiento desde WU-08B (v0.6.0): `includes/seo/` (clases puras
  `Cdd_Core_Seo_Document` y `Cdd_Core_Json_Ld`) e `includes/seo.php` resuelven la cabeza de
  cada petición — título, description, keywords, canonical, Open Graph, Twitter, breadcrumbs y
  JSON-LD `Event`/`BlogPosting`/`Thing` — sin suite de terceros (ADR 0025/0030, doc 15 §12). El
  copy publicado es meta editable (`seo_title`, `seo_description`, `seo_keywords`, `og_title`,
  `og_description`) en `page`, `post` y `event`, más `event_attendance_mode`, `seo_jsonld_extra`
  y `seo_related_url` en `event` y `cdd_region` en los términos `event_city`. Política
  `noindex, follow` para `/author`, álbumes, tags y 404; sitemap nativo recortado al árbol de
  docs/11; `cdd_core_default_locale()` declara `es_CO`. `includes/admin.php` añade la pantalla
  de Herramientas **«Eliminar huérfanos»** (OWN-015): solo `.ics`, dry-run primero, borrado con
  nonce y capacidad de edición de eventos.
- Cronograma en el `.ics` desde BUG-001 (v0.7.1): `Cdd_Core_Ics_Generator` emite **un VEVENT
  por sesión** de `event_calendar_dates` —UID propio `slug-Ymd@host` y fin exclusivo de día
  completo por ocurrencia— dentro del mismo sobre VCALENDAR de producción; un evento sin
  cronograma conserva el rango `event_date`/`event_end` y el UID publicado.
  `cdd_core_event_calendar_payload()` publica `occurrences`, `session_count` y `next`: como un
  enlace profundo de Google/Outlook lleva una sola entrada, el diálogo apunta a la próxima
  sesión —una fecha que el archivo contiene— en vez de a un rango que no aparece en ningún
  VEVENT. `cdd_core_ics_occurrence()` es el único punto de traducción entre la forma inclusiva
  que consume el generador y la compacta con fin exclusivo que consumen los enlaces.
- Formulario de contacto desde WU-09 (v0.7.0): Contact Form 7 es el único plugin de terceros
  aprobado (ADR 0025/0026) y su código **no** viaja en Git, así que lo versionado es la
  *definición* — `Cdd_Core_Contact_Form_Template` guarda la plantilla del formulario (el copy
  publicado de `static/contacto/index.html` con los tres controles como form-tags de CF7), la
  del correo a `caminodeldharma1@gmail.com` (`Reply-To` del visitante, texto plano) y los
  mensajes en español, porque el locale lo fija `cdd_core_default_locale()` y WordPress nunca
  instala el paquete de traducción de CF7. `wp cdd-core contact provision [--apply]` los escribe
  una vez, create-missing-only, y **rehúsa** mientras CF7 esté inactivo o mientras `/privacidad`
  no describa un envío real (ADR 0041 punto 3). `migrate convert` gana dos pasos field-scoped,
  `privacidad` **antes** que `contacto`, para que el aviso sea cierto antes de que el formulario
  llegue a la página. Sin CF7 todo esto es inerte: nada fatal, y el theme rinde los canales
  WhatsApp/correo.
- Sin contenido demo del instalador desde D-02 / OWN-024 (v0.7.2,
  [#10](https://github.com/refo44/demo-caminodeldharma/issues/10)): un WordPress recién
  instalado publica «Hello world!» y «Sample Page» y deja un borrador «Privacy Policy», y esa
  entrada demo desplaza contenido real del Inicio y de `/blog`.
  `Cdd_Core_Installer_Demo_Content` los reconoce por tipo + slug por defecto (también los
  traducidos de una instalación en español) + el estado en que los deja el instalador, nunca
  por ID; cualquier objeto con `_cdd_source_key` es contenido importado y queda intacto.
  `cdd_core_activate()` y `cdd_core_maybe_upgrade()` los **despublican** —así un entorno
  provisionado sin el paso manual tampoco los muestra— y `wp cdd-core demo purge [--apply]`
  los **borra**: dry-run por defecto, idempotente, guard de producción, y limpia
  `wp_page_for_privacy_policy` si apuntaba al borrador borrado. Borrar no es nunca un efecto
  secundario de la activación (ADR 0033).
- Sin feeds nativos desde D-03 / OWN-025 (v0.7.3, ADR 0044,
  [#11](https://github.com/refo44/demo-caminodeldharma/issues/11)): producción publicada
  responde 404 en `/feed`, `/blog/feed` y `/comments/feed`, y ninguna de esas URLs está en
  `docs/11-arbol-urls-final.md`. `cdd_core_block_feed_requests()` convierte cualquier petición
  con `feed` en la query en un 404 **real** antes de la consulta principal —una sola guarda
  cubre `feed`/`rdf`/`rss`/`rss2`/`atom`, bonita o `?feed=`, del sitio, de `/blog`, de
  comentarios, de archivo y de CPT— y `cdd_core_disable_feed_autodiscovery()` retira
  `feed_links` y `feed_links_extra` del `head`. Sin 301 a `/blog` y sin 200 con `noindex`. El
  `rel=alternate` `text/calendar` del evento vigente (OWN-014) no se toca. Un RSS público es
  decisión posterior (POST-010): reabrirlo es soltar esos dos hooks, más su fila en el árbol
  de URLs y en el ledger.
- Panel «Autores del blog» en el editor de bloques desde META-001 / OWN-019 (v0.7.4,
  [#18](https://github.com/refo44/demo-caminodeldharma/issues/18)): `includes/editor.php`
  registra `assets/js/authors-panel.js` y lo encola **solo** en `post.php` / `post-new.php`
  cuando el tipo es `post`. Es un `PluginDocumentSettingPanel` nativo (ADR 0025/0042; sin
  metabox clásico, sin ACF, sin bundler): busca fichas **publicadas** en
  `GET /wp/v2/blog_author?status=publish` a partir de dos caracteres —nunca precarga el
  catálogo ni da de alta fichas—, admite varias en el orden del byline, y escribe la
  relación con `dispatch( 'core/editor' ).editPost( { meta } )`, así que Publicar/Actualizar
  lleva `meta.authors` **en el mismo cuerpo REST** que lee el guard. Ese transporte es
  META-001: un picker que solo llena el DOM publica un 400. El guard no se relaja (borrador
  sin ficha sí; publicar sin ficha sigue siendo 400). La otra mitad de ADR 0037 §4: el
  control «Autor» del editor desaparece —WordPress 7.1 lo rinde como fila del panel Resumen,
  no como panel propio, así que no hay `removeEditorPanel()` que quitar y lo que se retira es
  el enlace REST `wp:action-assign-author` del que depende—. `post_author` queda intacto: el
  tipo conserva el soporte `author`, con su columna en el listado, edición rápida y
  revisiones como rastro de quién creó y guardó.
- Paneles de SEO y datos del evento en el editor de bloques desde META-002/003/004/005 /
  OWN-035 (v0.7.5, [#19](https://github.com/refo44/demo-caminodeldharma/issues/19)):
  `includes/editor.php` registra `assets/js/seo-panel.js` y lo encola **solo** en `post.php` /
  `post-new.php` para `post`, `page`, `event` y `blog_author`. Dos `PluginDocumentSettingPanel`
  nativos (ADR 0025/0042; sin metabox clásico, sin ACF, sin Yoast/Rank Math, sin bundler, sin
  `wp-api-fetch`): «SEO y buscadores» edita los seis campos de cabeza (`seo_title`,
  `seo_description`, `seo_keywords`, `og_title`, `og_description`, `seo_related_url`) en los
  cuatro tipos, y «Datos del evento (schema.org)» edita solo en `event` las claves que ya leen
  el JSON-LD y el `.ics` (`event_date`, `event_end`, `event_place`, `event_modality`,
  `event_attendance_mode`, `event_status`, `event_signup_url`, `event_signup_payment`,
  `event_featured`, `event_calendar_dates`) — ninguna clave nueva de dominio. Cada campo escribe
  con `dispatch( 'core/editor' ).editPost( { meta } )`, así que Publicar/Actualizar lleva la
  cabeza y los datos del evento **en el mismo cuerpo REST** que los persiste (META-005). En el
  mismo commit, `blog_author` gana el soporte `custom-fields` y el registro `seo_*`; su JSON-LD
  **sigue siendo `Thing`** (ADR 0037). `cdd_core_seo_backfill_meta()` (hook
  `wp_after_insert_post`) rellena `seo_description` al **publicar** desde el extracto o el
  contenido del propio objeto —nunca el título, nunca copy inventado, nunca bajo WP-CLI— si el
  editor lo dejó vacío; la copia importada por `migrate convert` o escrita por una persona no se
  toca (create-missing-only) y el front no la vuelve a derivar. `seo_jsonld_extra` se difiere del
  panel v1 (la meta y su sanitizador siguen editables por REST).
- Tooling de calidad en la raíz del monorepo: `composer test` (gate barato),
  `composer test:wp` (wp-phpunit en harness Docker efímero), `composer lint:phpcs`.
