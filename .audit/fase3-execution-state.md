# Fase 3 — Estado de ejecución durable

Artefacto de continuidad exigido por FABLE5 v2.3 §12. Otra sesión debe poder retomar el trabajo
leyendo este archivo y verificándolo contra Git, sin historial de chat.

| | |
| --- | --- |
| **Última actualización** | 2026-08-31 (sesión WU-03) |
| **Fase** | Fase 3 — WordPress (iniciada) |
| **Work unit activo** | Ninguno — WU-00…WU-03 **cerrados**; checkpoint antes de WU-04 |
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
- **WU-03 — Scaffold del plugin y kit de calidad TDD** (sesión 2026-08-31, ADR 0038/0027,
  TDD honesto: RED documentado antes del primer PHP):
  - Primer PHP propio: `wordpress/wp-content/plugins/camino-del-dharma-core/camino-del-dharma-core.php`
    (guard `ABSPATH`, `CDD_CORE_VERSION` 0.1.0, `CDD_CORE_PLUGIN_FILE`; text domain
    `camino-del-dharma-core`). Activa en el entorno local sin warnings/fatals.
  - Kit raíz: `composer.json` + `composer.lock` (PHPUnit 9.6.36, **wp-phpunit 7.1.0** = WP del
    compose, polyfills 4.0.0, WPCS 3.4.1; `platform.php` 8.3.30), `phpunit.xml.dist` (suite
    `tests/Unit`, bootstrap `tests/Support/bootstrap.php` con `ABSPATH` dummy),
    `phpunit-wp.xml.dist` (suite `tests/WordPress`, bootstrap wp-phpunit + `wp-tests-config.php`).
  - Harness efímero nivel 2: `tools/run-phpunit-wp.sh` → compose `-p cdd-wp-phpunit`
    (`docker-compose.wp-tests.yml` monta el repo en wpcli; `tools/wp-tests.env` desechable,
    puerto 8083, tablas `wptests_`, `trap down -v`). Nunca el volumen del desarrollador.
  - Estilo: `phpcs.xml.dist` (WordPress-Extra; prefijo `cdd_core` — WPCS rechaza `cdd` por
    corto; theme usará `camino_del_dharma`; sniff de prefijos excluido en `tests/`).
  - `tools/php-lint.sh`, `tools/run-phpunit.sh` (fallback Docker sin PHP nativo).
  - CI solo-calidad: `.github/workflows/test.yml` (jobs php: `composer test` + `composer
    lint:phpcs`; css: `npm run lint:css`; sin deploy, sin secretos, sin SonarScanner).
  - Docs: CLAUDE.md, AGENTS.md, guía de pruebas §comandos, READMEs de `wordpress/`, plugin,
    theme y `tests/`, CHANGELOG. QA: matriz WU-03 todo Pass (local); CI/Sonar `Unverified`
    (sin push).

## Riesgos y hallazgos activos

- Paridad repo↔Hostinger no verificada (ver arriba). Registrar el delta al extraer (WU-06).
- `descargas/Resumen programa EVF.mp4` en la raíz, no trazado y fuera de la receta ZIP: se
  preserva intacto; clasificar en el inventario si aparece referenciado.
- `_config.yml` y `.nojekyll` son restos de GitHub Pages (desactivado 2026-07-28), fuera del
  ZIP; su retiro es una limpieza separada.
- `test.yml` y el análisis Sonar del plugin quedan `Unverified` hasta que exista push (la rama
  es local por diseño del master prompt).

## Decisiones/asunciones usadas

- Autorización del propietario para Fase 3: mensaje «Implementar» sobre FABLE5 v2.3
  (2026-08-31); esta sesión ejecuta solo WU-03 por orden explícita del owner.
- Prefijo de primer partido del plugin: `cdd_core` (ADR 0027 proponía `cdd_` o
  `camino_del_dharma_`; el sniff `PrefixAllGlobals` de WPCS 3.x rechaza prefijos de 3
  caracteres). El theme usará `camino_del_dharma`. Registrado en `phpcs.xml.dist` y matriz.
- `tools/wp-tests.env` versiona credenciales **desechables** del harness efímero (localhost,
  `down -v`); no son secretos. El `.env` del entorno de desarrollo sigue gitignored.
- PHPCS corre como paso propio del job PHP de `test.yml` (la guía lo sitúa en el job de
  estilo; se mantiene en el job PHP por ser tooling Composer, sin cambiar el alcance).

## Evidencia de validación

Ver `.audit/fase3-validation-matrix.md` § WU-03. Estados: `Unverified`, `Pass (local)`,
`Pass`, `Fail`.

## Bloqueos

- Ninguno. WU-04 (theme FSE) es el siguiente work unit; puede arrancar en la próxima sesión
  tras rerun del preflight y de los gates (§ Reanudación).

## Archivos cambiados en la sesión actual (WU-03)

- `wordpress/wp-content/plugins/camino-del-dharma-core/camino-del-dharma-core.php` (nuevo, primer PHP)
- `composer.json`, `composer.lock` (nuevos)
- `phpunit.xml.dist`, `phpunit-wp.xml.dist`, `phpcs.xml.dist` (nuevos)
- `tests/Support/bootstrap.php`, `tests/Unit/Plugin_BootstrapTest.php`,
  `tests/WordPress/bootstrap.php`, `tests/WordPress/wp-tests-config.php`,
  `tests/WordPress/Plugin_LoadedTest.php` (nuevos)
- `tools/php-lint.sh`, `tools/run-phpunit.sh`, `tools/run-phpunit-wp.sh`, `tools/wp-tests.env` (nuevos)
- `docker-compose.wp-tests.yml` (nuevo, override solo del harness efímero)
- `.github/workflows/test.yml` (nuevo, solo calidad)
- CLAUDE.md, AGENTS.md, `docs/guia-pruebas-plugin-theme-fse.md` (§ comandos),
  `wordpress/README.md`, READMEs de plugin/theme, `tests/README.md`, `CHANGELOG.md`,
  `.audit/fase3-validation-matrix.md`, este archivo

## Último commit verificado

Pendiente de registrar tras el commit de cierre de WU-03 (esta sesión). Historial previo en
`fase3-wordpress`: `5088e32` (WU-00) → `bfb6dc0`/`54cd09f` (WU-01) → `11237a1` → `b9c9eb8`
(WU-02) → `81d7547`.

## Estado del entorno local

Contenedores del proyecto `camino-del-dharma` levantados al cierre (`docker compose stop`
para pararlos). WordPress local: `http://localhost:8081` (puerto en `.env`). Plugin
`camino-del-dharma-core` **activo** (v0.1.0) en el entorno local. El harness efímero
`cdd-wp-phpunit` no deja contenedores ni volúmenes (`down -v` verificado).

## Próxima acción exacta

WU-03 está cerrado. **La sesión actual se detiene aquí** (checkpoint FABLE5 §12). La
siguiente sesión: rerun del preflight (§ Reanudación), rerun de los gates WU-02 (compose
fail-fast / db healthy / `wp_get_environment_type()` = `local`) **y WU-03**
(`./tools/php-lint.sh`, unit suite verde, `vendor/bin/phpcs` limpio — con
`composer install` previo si falta `vendor/`), luego implementar y validar **solo WU-04** —
scaffold del theme FSE `camino-del-dharma` y baseline de tokens visuales (`theme.json`
reproduciendo exactamente los tokens del estático, ADR 0029; TDD desde la primera línea,
bloques de dominio en el plugin, no en el theme) — actualizar este archivo y detenerse en el
checkpoint de WU-04.

## Procedimiento de reanudación

```bash
git status --short
git branch --show-current   # esperar: fase3-wordpress
git log -5 --oneline
```

Leer este archivo, verificarlo contra Git (¿existe `composer.lock`? ¿qué commits hay en
`fase3-wordpress`?), rerun del último gate QA relevante de la matriz, y continuar desde
«Próxima acción exacta». El estado del repositorio prevalece sobre la memoria de chat.
