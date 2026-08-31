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

Sin filas todavía. Se completan en la sesión dedicada a WU-02.
