# Fase 3 — Estado de ejecución durable

Artefacto de continuidad exigido por FABLE5 v2.3 §12. Otra sesión debe poder retomar el trabajo
leyendo este archivo y verificándolo contra Git, sin historial de chat.

| | |
| --- | --- |
| **Última actualización** | 2026-08-31 |
| **Fase** | Fase 3 — WordPress (iniciada) |
| **Work unit activo** | Ninguno — WU-00, WU-01 y WU-02 **cerrados**; frontera obligatoria antes de WU-03 (ADR 0023) |
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
  `docs/archive/recitacion-practica-comida/` (nunca en `static/`). Tooling actualizado:
  `package.json` (lint/build CSS), `.stylelintignore`, `scripts/*.sh`, `scripts/build-docx/build.js`,
  README (receta ZIP desde `static/`), CLAUDE.md, AGENTS.md,
  `.cursor/rules/wordpress-migration-safety.mdc`, `docs/13` v2.5, ledger de migración, CHANGELOG
  (Unreleased). QA gate: ver matriz WU-01 — todo Pass (local); sin despliegue.
- **WU-02 — Entorno local Docker** (sesión dedicada 2026-08-31, ADR 0023): `docker-compose.yml`
  raíz (name `camino-del-dharma`; db MariaDB 11.8 + healthcheck; wordpress PHP 8.3 en
  `127.0.0.1:${WORDPRESS_PORT:-8080}`; wpcli `user 33:33`), `.env.example` versionado, `.env`
  gitignored (regla nueva en `.gitignore`), fail-fast `${VAR:?}`, `WP_ENVIRONMENT_TYPE=local` +
  debug log en ambos servicios PHP. Core WordPress 7.1 instalado vía wpcli (admin solo en
  volumen/`.env` locales). Gotcha corregido y documentado: `WP_DEBUG` debe activarse con
  `WORDPRESS_DEBUG: 1`, no en `WORDPRESS_CONFIG_EXTRA` (playbook actualizado). QA gate: matriz
  WU-02 todo Pass (local); paridad local PHP 8.3.33 / MariaDB 11.8.9 vs Hostinger 8.3.30 /
  11.8.8 (misma serie menor).

## Riesgos y hallazgos activos

- Paridad repo↔Hostinger no verificada (ver arriba). Registrar el delta al extraer (WU-06).
- `descargas/Resumen programa EVF.mp4` existe en la raíz, **no trazado** en git (`*.mp4` en
  `.gitignore`) y **fuera** de la receta ZIP de despliegue. No es superficie desplegable: se
  preserva intacto en la raíz, no se mueve a `static/` ni se commitea. Clasificarlo en el
  inventario si aparece referenciado (a la fecha no está en el ZIP ni en sitemap).
- `_config.yml` y `.nojekyll` son restos de GitHub Pages (desactivado 2026-07-28), trazados,
  fuera del ZIP. Se dejan en la raíz sin cambios; su retiro es una limpieza separada, no parte
  de WU-01.

## Decisiones/asunciones usadas

- Autorización del propietario para iniciar WU-00/WU-01: mensaje «Implementar» sobre
  FABLE5 v2.3 (2026-08-31), habilitado por `17-orden-implementacion` v3.9.
- Superficie desplegable = lista exacta de la receta ZIP del README (index.html, 404.html,
  robots.txt, sitemap.xml, sitemap.xsl, llms.txt, .htaccess, favicon.ico, favicon.svg,
  assets/, comunidad/, linaje/, practica/, eventos/, galeria/, contacto/, donaciones/, blog/,
  privacidad/). Todo lo demás permanece en la raíz.
- WU-01 se parte en dos commits para revisión: (a) `git mv` puro que preserva renames,
  (b) actualización de tooling/docs afectados (package.json, .stylelintignore, scripts/,
  README, CLAUDE.md, AGENTS.md, docs/13). Sin implementación no relacionada.

## Evidencia de validación

Ver `.audit/fase3-validation-matrix.md`. Estados permitidos: `Unverified`, `Pass (local)`,
`Pass`, `Fail`.

## Bloqueos

- Ninguno para WU-00/WU-01/WU-02.
- WU-03 (código de aplicación) requiere **sesión separada** tras WU-02 (ADR 0023, FABLE5 §12):
  frontera obligatoria.

## Archivos cambiados en la sesión actual (WU-02)

- `docker-compose.yml` (nuevo)
- `.env.example` (nuevo); `.env` local creado, gitignored, fuera de Git
- `.gitignore` (regla `/.env`)
- `docs/docker-wordpress-playbook.md` (CURRENT STATE + gotcha `WORDPRESS_DEBUG`)
- `.audit/fase3-validation-matrix.md` (filas WU-02)
- `CHANGELOG.md` (Unreleased)
- este archivo

## Último commit verificado

Ver commit de cierre de WU-02 en `fase3-wordpress` (tras `11237a1`). Historial:
`5088e32` (WU-00) → `bfb6dc0` + `54cd09f` (WU-01) → `11237a1` (cierre WU-01) → WU-02.

## Estado del entorno local

Contenedores levantados al cierre de la sesión WU-02 (`docker compose stop` para pararlos;
`up -d` los devuelve). Volúmenes `db_data`/`wp_data` persisten el core y la BD locales;
`docker compose down -v` los destruye (entorno desechable, re-instalable con
`wp core install`). WordPress local: `http://localhost:8081` (puerto en `.env`; 8080 estaba
ocupado por otro proyecto). Credenciales admin: solo en `.env` local y el volumen.

## Próxima acción exacta

WU-02 está cerrado. **La sesión actual se detiene aquí** (frontera obligatoria ADR 0023: el
código de aplicación empieza en otra sesión). La siguiente sesión: rerun del preflight
(§ Reanudación) **incluido el gate WU-02** (`docker compose config` fail-fast sin `.env`;
con `.env`: `up -d`, db healthy, `wp eval wp_get_environment_type()` = `local`), luego
implementar y validar **solo WU-03** — scaffold del plugin `camino-del-dharma-core` y tooling
de calidad (PHPCS/WPCS, kit PHPUnit + wp-phpunit el mismo día que exista PHP propio, ADR 0038,
`docs/guia-pruebas-plugin-theme-fse.md`) — actualizar este archivo y detenerse en el checkpoint
de WU-03.

## Procedimiento de reanudación

```bash
git status --short
git branch --show-current   # esperar: fase3-wordpress
git log -5 --oneline
```

Leer este archivo, verificarlo contra Git (¿existe `static/`? ¿qué commits hay en
`fase3-wordpress`?), rerun del último gate QA relevante de la matriz, y continuar desde
«Próxima acción exacta». El estado del repositorio prevalece sobre la memoria de chat.
