# ADR 0006: GitHub Actions para CI/CD

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

Hasta la adopción de este ADR, el despliegue es **manual**: generar ZIP local, subir a Hostinger File Manager y extraer en `public_html` (README). Eso funciona para releases puntuales pero:

- no ejecuta validaciones de forma sistemática en cada PR;
- depende del entorno local del operador;
- no deja registro automático de pipelines fallidos;
- dificulta rollback reproducible.

El proyecto ya usa npm para Stylelint (`npm run lint:css`) y prevé QA explícita (Fase 2.5 en `17-orden-implementacion`).

## Decisión

**GitHub Actions** será el mecanismo de **integración continua y despliegue** del repositorio.

Pipeline mínimo:

| Workflow | Trigger | Propósito |
| -------- | ------- | --------- |
| `lint.yml` | push y PR a `main` | `npm ci` + `npm run lint:css` |
| `deploy.yml` | push a `main` y/o `workflow_dispatch` | Despliegue a producción vía SSH + rsync (ADR 0007) |

Reglas:

- PRs a `main` deben pasar lint antes del merge.
- Producción solo desde `main` (ADR 0004).
- Secrets (`SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`, ruta remota) en GitHub Secrets, nunca en el repo.

**Estado de implementación (2026-07-19):** `lint.yml` operativo; `deploy.yml` definido como objetivo — completar configuración de secrets en Hostinger antes de activar despliegue automático. Mientras tanto, el flujo ZIP del README permanece como respaldo documentado.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Solo despliegue manual permanente | No escala; validación inconsistente. |
| GitLab CI / CircleCI | El código ya vive en GitHub; Actions integrado reduce fricción. |
| Hostinger Git auto-deploy | Menor control de validaciones, exclusión de archivos y rollback. |
| FTP upload en CI | Sin sincronización `--delete`; deja archivos huérfanos (ADR 0007). |

## Consecuencias

**Beneficios:**

- Validación automática en cada cambio de CSS.
- Despliegue repetible y auditable desde Actions.
- Base para añadir tests futuros (enlaces, HTML, a11y).

**Riesgos:**

- Configuración inicial de SSH y secrets.
- Coste de minutos de Actions (bajo para este proyecto).

**Trabajo futuro:**

- Completar `deploy.yml` con rsync y smoke test post-deploy.
- Opcional: job de Lighthouse en CI para releases taggeadas.
- Documentar secrets requeridos en `CONTRIBUTING.md`.

## Referencias

- `.github/workflows/lint.yml`
- `.github/workflows/deploy.yml` (plantilla)
- `docs/17-orden-implementacion` Fase 4
- `CONTRIBUTING.md`
- ADR 0004, ADR 0005, ADR 0007
