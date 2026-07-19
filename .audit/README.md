# Workspace de auditoría — Camino del Dharma (2026-07-19)

Auditoría de solo lectura de https://caminodeldharma.org (fuente: commit be896db2). Estado: **COMPLETE**. Resultado HSTS: **ACTIVATE NOW — despliegue escalonado** (Fase 1 `max-age=604800`; Fase 2 `31536000` post-WordPress — ADR 0018, ver `hsts-decision.md`).

Puntos de entrada:

- `executive-summary.md` — resumen para dirección (español).
- `report.md` — informe completo (17 secciones).
- `hsts-decision.md` / `hsts-decision.json` — decisión HSTS con evidencia.
- `findings.jsonl` (10) + `evidence-ledger.jsonl` (31) — ledgers canónicos.
- `remediation/` — un paquete de remediación por hallazgo accionable.
- `implementation/` — backlog de 12 tareas atómicas para agentes externos (9 READY, 3 BLOCKED); empezar por `implementation/backlog.md`.
- `state.md` — punto de reanudación canónico.
- `verification.md` — registro del verificador de contexto fresco (ACCEPT).

Siguiente acción (etapa 2 del README raíz): sesión nueva de implementación con `task_path=DOCS/demo-caminodeldharma/.audit/implementation/tasks/TASK-0004.md`.
