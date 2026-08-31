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
| Solo superficie desplegable movida | `git ls-files static/` vs receta ZIP | pendiente | Unverified |
| Renames preservados | `git log --stat -M` del commit de movimiento | pendiente | Unverified |
| Nada perdido ni duplicado | conteo de archivos trazados antes/después idéntico | pendiente | Unverified |
| `npm run lint:css` verde con nuevas rutas | ejecución local | pendiente | Unverified |
| `npm run build:css` regenera `static/assets/css/main.min.css` | ejecución local | pendiente | Unverified |
| `git diff --check` sin whitespace errors | ejecución local | pendiente | Unverified |
| Docs/README actualizados a layout Fase 3 | revisión de diff | pendiente | Unverified |
| ZIP empaquetable desde `static/` | dry-run del comando ZIP (listado, sin desplegar) | pendiente | Unverified |
| URLs públicas sin cambio | el movimiento no toca contenido ni rutas; sin deploy | N/A (sin despliegue) | Unverified |

## WU-02 — Entorno Docker local (sesión separada, ADR 0023)

Sin filas todavía. Se completan en la sesión dedicada a WU-02.
