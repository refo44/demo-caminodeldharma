# Plan de implementación por fases (para agentes externos)

> **Actualización 2026-07-20:** etapa 2 cerrada. Deploy v1.0.14 confirmado. Fases 1–6 completadas para el sitio estático. Pendiente solo tareas BLOCKED y HSTS post-WordPress.

Este plan describe la secuenciación histórica; detalle en `implementation/waves.md` y estado actual en `implementation/backlog.md`.

- **Fase 0 — Bloqueadores de producción:** vacía.
- **Fase 1 — Estabilización (WAVE-1):** ✅ TASK-0001, 0002, 0013, 0015. HSTS (0004/0005) aplazada ADR 0020.
- **Fase 2 — Rendimiento (WAVE-2):** ✅ TASK-0007, 0014, 0017, 0019.
- **Fase 3 — SEO/contenido (WAVE-3):** ✅ TASK-0018. BLOCKED: 0016, 0020.
- **Fase 4 — Seguridad (WAVE-4):** ✅ TASK-0006, 0008, 0009. BLOCKED: 0012 (DNS).
- **Fase 5 — Mantenibilidad (WAVE-5):** ✅ TASK-0011.
- **Fase 6 — AEO (WAVE-6):** ✅ TASK-0010.

**Próximo hito:** migración WordPress → auditoría completa a +30 días estables (`audit-schedule.md`).
