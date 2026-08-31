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
- El metabox de autores en wp-admin sigue pendiente — siempre test en rojo primero. Guía:
  `docs/guia-pruebas-plugin-theme-fse.md`.
- Tooling de calidad en la raíz del monorepo: `composer test` (gate barato),
  `composer test:wp` (wp-phpunit en harness Docker efímero), `composer lint:phpcs`.
