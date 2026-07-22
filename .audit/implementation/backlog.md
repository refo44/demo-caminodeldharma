# Implementation backlog

> **Actualización 2026-07-20:** el propietario confirmó despliegue en producción (v1.0.14) de todas las tareas que estaban READY más TASK-0001. Etapa 2 (implementación) cerrada para el sitio estático; quedan 6 tareas BLOCKED por decisiones humanas o aplazamiento post-WordPress.

| Priority | Task ID | Status | Wave | Findings | Owner | Effort | Risk | Dependencies | Conflict group |
|---:|---|---|---|---|---|---|---|---|---|
| — | TASK-0001 | **COMPLETED** | WAVE-1 | FUNC-002 | Frontend | LT_30_MIN | LOW | — | CG-HTACCESS |
| — | TASK-0002 | **COMPLETED** | WAVE-1 | FUNC-001 | Frontend | 30_MIN_TO_2_HOURS | LOW | — | CG-CONTACTO |
| — | TASK-0013 | **COMPLETED** | WAVE-1 | SEO-EXT-001, SEO-EXT-002 | DevOps | LT_30_MIN | LOW | — | CG-HTACCESS |
| — | TASK-0015 | **COMPLETED** | WAVE-1 | SEO-EXT-001, SEO-EXT-002 | DevOps/Marketing (humano) | LT_30_MIN | LOW | TASK-0013 | NONE |
| — | TASK-0007 | **COMPLETED** (v1.0.19) | WAVE-2 | PERF-001 | Frontend | 30_MIN_TO_2_HOURS | LOW | — | CG-HTML-GLOBAL |
| — | TASK-0014 | **COMPLETED** | WAVE-2 | SEO-EXT-001 | Marketing/Comunidad (humano) | HALF_DAY | LOW | TASK-0013 | NONE |
| — | TASK-0017 | **COMPLETED** | WAVE-2 | ASO-002 | Contenido/Frontend | 30_MIN_TO_2_HOURS | LOW | TASK-0013 | CG-HTML-GLOBAL |
| — | TASK-0019 | **COMPLETED** | WAVE-2 | SEO-EXT-001 | Marketing/Comunidad (humano) | LT_30_MIN | LOW | — | NONE |
| — | TASK-0018 | **COMPLETED** | WAVE-3 | ASO-001 | Contenido/Comunidad | HALF_DAY | LOW | TASK-0013 | CG-HTML-GLOBAL |
| — | TASK-0006 | **COMPLETED** | WAVE-4 | PRIV-001 | Product | ONE_DAY | MEDIUM | — | CG-HTML-GLOBAL |
| — | TASK-0008 | **COMPLETED** | WAVE-4 | SEC-002 | DevOps | HALF_DAY | MEDIUM | — | CG-HTACCESS |
| — | TASK-0009 | **COMPLETED** | WAVE-4 | SEC-003 | DevOps | LT_30_MIN | LOW | — | NONE |
| — | TASK-0011 | **NOT DONE** | WAVE-5 | PERF-002 | Frontend | 30_MIN_TO_2_HOURS | LOW | TASK-0007 | CG-HTML-GLOBAL |
| — | TASK-0010 | **NOT DONE** | WAVE-6 | AEO-001 | Frontend | 30_MIN_TO_2_HOURS | LOW | TASK-0011 | CG-HTML-GLOBAL |
| 1 | TASK-0004 | BLOCKED | WAVE-1 | SEC-001 | DevOps | LT_30_MIN | LOW | — | CG-HTACCESS |
| 1 | TASK-0005 | BLOCKED | WAVE-1 | SEC-001 | Security | LT_30_MIN | LOW | TASK-0004 | NONE |
| 6 | TASK-0003 | BLOCKED | WAVE-1 | FUNC-001 | Full-stack | ONE_DAY | MEDIUM | TASK-0002 | CG-CONTACTO |
| 11 | TASK-0012 | BLOCKED | WAVE-4 | SEC-001 | Security | HALF_DAY | MEDIUM | TASK-0004, TASK-0005 | CG-HTACCESS |
| 4 | TASK-0016 | BLOCKED | WAVE-3 | SEO-EXT-001 | Contenido/Comunidad | ONE_DAY | LOW | TASK-0013 | NONE |
| 5 | TASK-0020 | BLOCKED | WAVE-3 | SEO-EXT-001 | Contenido/Comunidad | ONE_DAY | MEDIUM | TASK-0014 | CG-HTML-GLOBAL |

**Resumen:** 14 COMPLETED · 6 BLOCKED · 0 READY · 0 IMPLEMENTED_PENDING_VALIDATION

**Deploy confirmado:** v1.0.14 (2026-07-20). Ledger canónico: `tasks.jsonl`.

**Siguiente acción:** decisiones humanas pendientes (TASK-0003, 0016, 0020) y HSTS post-WordPress (TASK-0004/0005, ADR 0020).
