# camino-del-dharma-core

Plugin de dominio (ADR 0024). Scaffold creado en Fase 3 WU-03 con TDD desde la primera
línea (ADR 0038): `camino-del-dharma-core.php` nació después de un test en rojo
(`tests/Unit/Plugin_BootstrapTest.php`, `tests/WordPress/Plugin_LoadedTest.php`).

- Prefijo de primer partido: `cdd_core` (constantes `CDD_CORE_*`; WPCS rechaza prefijos de
  3 caracteres, ver `phpcs.xml.dist`). Text domain: `camino-del-dharma-core`.
- El dominio (CPTs, taxonomías, rutas, importador) aterriza en WU-05+ — siempre test en
  rojo primero. Guía: `docs/guia-pruebas-plugin-theme-fse.md`.
- Tooling de calidad en la raíz del monorepo: `composer test` (gate barato),
  `composer test:wp` (wp-phpunit en harness Docker efímero), `composer lint:phpcs`.
