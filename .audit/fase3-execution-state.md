# Fase 3 — Estado de ejecución durable

Artefacto de continuidad exigido por FABLE5 v2.3 §12. Otra sesión debe poder retomar el trabajo
leyendo este archivo y verificándolo contra Git, sin historial de chat.

| | |
| --- | --- |
| **Última actualización** | 2026-08-31 |
| **Fase** | Fase 3 — WordPress (iniciada) |
| **Work unit activo** | Ninguno — WU-00 y WU-01 **cerrados**; frontera obligatoria antes de WU-02 (ADR 0023) |
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

- Ninguno para WU-00/WU-01.
- WU-02 (Docker) requiere **sesión separada** (ADR 0023, FABLE5 §12): frontera obligatoria.

## Archivos cambiados en la sesión actual

- `.audit/fase3-execution-state.md` (nuevo)
- `.audit/fase3-validation-matrix.md` (nuevo)
- `docs/operations/wordpress-manual-deployment.md` (nuevo)
- `docs/operations/third-party-plugins.md` (nuevo)
- WU-01: renames raíz → `static/` + tooling (ver commits en `fase3-wordpress`)

## Último commit verificado

Se actualiza al cierre de cada work unit (ver historial de `fase3-wordpress`).

## Próxima acción exacta

WU-01 está cerrado. **La sesión actual se detiene aquí** (frontera obligatoria ADR 0023).
La siguiente sesión: rerun del preflight (§ Reanudación), luego implementar y validar **solo
WU-02** — entorno local Docker según ADR 0023 y `docs/docker-wordpress-playbook.md`
(MariaDB 11.8 + WordPress PHP 8.3 + wpcli; `.env` gitignored + `.env.example`;
`WP_ENVIRONMENT_TYPE=local`) — actualizar este archivo y detenerse de nuevo antes de
código de aplicación (WU-03).

## Procedimiento de reanudación

```bash
git status --short
git branch --show-current   # esperar: fase3-wordpress
git log -5 --oneline
```

Leer este archivo, verificarlo contra Git (¿existe `static/`? ¿qué commits hay en
`fase3-wordpress`?), rerun del último gate QA relevante de la matriz, y continuar desde
«Próxima acción exacta». El estado del repositorio prevalece sobre la memoria de chat.
