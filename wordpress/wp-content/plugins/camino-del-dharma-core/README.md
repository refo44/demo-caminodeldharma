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
- El importador WP-CLI (validate/plan/import/verify + `seed`) aterriza en WU-06; la capa
  wp-admin (metabox de autores, «Eliminar huérfanos») en WU-07/08 — siempre test en rojo
  primero. Guía: `docs/guia-pruebas-plugin-theme-fse.md`.
- Tooling de calidad en la raíz del monorepo: `composer test` (gate barato),
  `composer test:wp` (wp-phpunit en harness Docker efímero), `composer lint:phpcs`.
