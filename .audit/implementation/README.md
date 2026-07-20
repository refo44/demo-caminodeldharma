# Implementation workspace

- `tasks.jsonl` — ledger canónico de estado (el auditor solo asigna DRAFT/READY/BLOCKED).
- `tasks/TASK-XXXX.md` — paquetes autocontenidos para agentes implementadores externos (uno por ejecución).
- `results/` — artefactos de resultado que escribe cada agente implementador.
- `validations/` — artefactos de validación independiente.
- `locks/` — convención de lock: crear `locks/<conflict-group>.lock` con task_id + timestamp al reclamar; eliminarlo al terminar.
- `backlog.md`, `waves.md`, `dependency-graph.md`, `conflict-map.md` — planificación.

**Estado 2026-07-20:** etapa 2 cerrada (deploy v1.0.14). **14 COMPLETED · 6 BLOCKED · 0 READY.** Resumen: `results/DEPLOY-v1.0.14.md`.

Flujo (etapas 2 y 3 — para futuras tareas o post-WordPress):
1. Elegir la siguiente tarea READY con dependencias completas y conflict group libre.
2. Sesión nueva con IMPLEMENTATION_AGENT_TEMPLATE.md y `task_path=…/tasks/TASK-XXXX.md`.
3. Tras IMPLEMENTED_PENDING_VALIDATION, sesión nueva con TASK_VALIDATOR_TEMPLATE.md.
4. Solo un lead autorizado cierra tareas validadas.
