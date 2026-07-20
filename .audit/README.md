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
- `implementation/` — backlog de 20 tareas (**14 COMPLETED**, **6 BLOCKED**); ver `implementation/backlog.md`.
- `state.md` — punto de reanudación canónico.
- `verification.md` — registro del verificador de contexto fresco (ACCEPT).

**Etapa 2 cerrada (2026-07-20):** deploy v1.0.14 con todas las tareas READY + TASK-0001. Pendiente solo lo BLOCKED: formulario real (TASK-0003), HSTS (TASK-0004/0005), DNS/includeSubDomains (TASK-0012), plan editorial (TASK-0016), páginas sangha por ciudad (TASK-0020).
