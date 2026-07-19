# Plan de implementación por fases (para agentes externos)

Este plan describe la secuenciación; la auditoría no implementa nada. Detalle operativo por ola en `implementation/waves.md`; reglas de concurrencia en `implementation/conflict-map.md`.

- **Fase 0 — Bloqueadores de producción:** vacía (no se encontraron).
- **Fase 1 — Estabilización inmediata (WAVE-1):** TASK-0004 → TASK-0005 (HSTS + verificación), TASK-0001 (.ics), TASK-0002 (contacto CTA). Gate: cabecera HSTS exacta verificada; 0 enlaces rotos; contacto sin vía muerta. Rollback por tarea.
- **Fase 2 — CWV y accesibilidad (WAVE-2):** TASK-0007 (imágenes). Gate: ratios ≤2.5, paridad visual.
- **Fase 3 — SEO y contenido (WAVE-3):** sin tareas (área en verde).
- **Fase 4 — Endurecimiento de seguridad (WAVE-4):** TASK-0008 (CSP por etapas), TASK-0009 (security.txt); desbloquear cuando proceda TASK-0006 (privacidad) y TASK-0012 (includeSubDomains). Gate: CSP enforced sin violaciones.
- **Fase 5 — Arquitectura y mantenibilidad (WAVE-5):** TASK-0011 (versionado de assets). Gate: invalidación verificada.
- **Fase 6 — AI search y agéntico (WAVE-6):** TASK-0010 (galería sin JS). Gate: curl muestra imágenes.

Reglas: una tarea por sesión de agente; validación independiente antes de cerrar (TASK_VALIDATOR_TEMPLATE.md); los grupos CG-HTACCESS, CG-CONTACTO y CG-HTML-GLOBAL se ejecutan en serie.
