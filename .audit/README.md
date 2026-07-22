# Workspace de auditoría — Camino del Dharma (2026-07-19)

Auditoría de solo lectura de https://caminodeldharma.org (fuente inicial: commit be896db2). Estado auditoría: **COMPLETE**. Implementación estática: **COMPLETE** (deploy v1.0.14 confirmado 2026-07-20). HSTS: **APLAZADO** post-WordPress (ADR 0020, ver `hsts-decision.md`).

Puntos de entrada:

- `executive-summary.md` — resumen para dirección (español).
- `report.md` — informe completo (17 secciones).
- `hsts-decision.md` / `hsts-decision.json` — decisión HSTS con evidencia.
- `manual-inputs-pending.md` — insumos manuales aún abiertos (decisiones de comunidad, DNS).
- `manual-inputs-howto.md` — guía paso a paso para insumos manuales.
- `audit-schedule.md` — cronograma de próximas auditorías, organizado alrededor del corte a WordPress.
- `findings.jsonl` (14) + `evidence-ledger.jsonl` (52) — ledgers canónicos.
- `remediation/` — un paquete de remediación por hallazgo accionable.
- `implementation/` — backlog de 20 tareas (**12 COMPLETED**, **2 NOT DONE**, **6 BLOCKED**); ver `implementation/backlog.md`.
- `state.md` — punto de reanudación canónico.
- `verification.md` — registro del verificador de contexto fresco (ACCEPT).

**Etapa 2 cerrada (2026-07-20):** deploy v1.0.14 con todas las tareas READY + TASK-0001. Pendiente lo BLOCKED: formulario real (TASK-0003), HSTS (TASK-0004/0005), DNS/includeSubDomains (TASK-0012), plan editorial (TASK-0016), páginas sangha por ciudad (TASK-0020).

**Corrección de estados (2026-07-21).** Al revisar el cierre de la Etapa 2 se detectó que **tres tareas figuraban como COMPLETED sin cumplir su Definition of Done**. El cierre de v1.0.14 se apoyó en confirmación del propietario (`implementation/results/DEPLOY-v1.0.14.md`) sin artefacto de validación por tarea: `implementation/results/` solo contiene registros de TASK-0001, TASK-0006 y del propio deploy.

| Tarea | Antes | Ahora | Evidencia |
|---|---|---|---|
| TASK-0007 | COMPLETED (v1.0.14) | **COMPLETED (v1.0.19)** | El DoD no se cumplía en v1.0.14: el 2026-07-21 `logo.png` seguía en 1000×1000 / 35,5 KB. Se cumple con los cambios de v1.0.19. |
| TASK-0010 | COMPLETED (v1.0.14) | **NOT DONE** | `curl /galeria \| grep -c '<img'` = **1**; el DoD exige ≥12. Sin `<noscript>` ni imágenes estáticas. AEO-001 abierto. |
| TASK-0011 | COMPLETED (v1.0.14) | **NOT DONE** | Ninguna referencia css/js lleva `?v=`, ni en producción ni en el repo; `git log -S'?v=' --all` no registra ningún commit. PERF-002 abierto. |

Los ficheros de `raw/` **no se han modificado**: son la evidencia congelada del estado auditado el 2026-07-19.
