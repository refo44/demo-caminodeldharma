# Implementation backlog

| Priority | Task ID | Status | Wave | Findings | Owner | Effort | Risk | Dependencies | Conflict group |
|---:|---|---|---|---|---|---|---|---|---|
| 1 | TASK-0004 | READY | WAVE-1 | SEC-001 | DevOps | LT_30_MIN | LOW | — | CG-HTACCESS |
| 1 | TASK-0005 | READY | WAVE-1 | SEC-001 | Security | LT_30_MIN | LOW | TASK-0004 | NONE |
| 2 | TASK-0001 | READY | WAVE-1 | FUNC-002 | Frontend | LT_30_MIN | LOW | — | NONE |
| 3 | TASK-0002 | READY | WAVE-1 | FUNC-001 | Frontend | 30_MIN_TO_2_HOURS | LOW | — | CG-CONTACTO |
| 4 | TASK-0007 | READY | WAVE-2 | PERF-001 | Frontend | 30_MIN_TO_2_HOURS | LOW | — | CG-HTML-GLOBAL |
| 5 | TASK-0006 | BLOCKED | WAVE-4 | PRIV-001 | Product | ONE_DAY | MEDIUM | — | CG-HTML-GLOBAL |
| 6 | TASK-0003 | BLOCKED | WAVE-1 | FUNC-001 | Full-stack | ONE_DAY | MEDIUM | TASK-0002 | CG-CONTACTO |
| 7 | TASK-0008 | READY | WAVE-4 | SEC-002 | DevOps | HALF_DAY | MEDIUM | TASK-0004 | CG-HTACCESS |
| 8 | TASK-0009 | READY | WAVE-4 | SEC-003 | DevOps | LT_30_MIN | LOW | — | NONE |
| 9 | TASK-0011 | READY | WAVE-5 | PERF-002 | Frontend | 30_MIN_TO_2_HOURS | LOW | TASK-0007 | CG-HTML-GLOBAL |
| 10 | TASK-0010 | READY | WAVE-6 | AEO-001 | Frontend | 30_MIN_TO_2_HOURS | LOW | TASK-0011 | CG-HTML-GLOBAL |
| 11 | TASK-0012 | BLOCKED | WAVE-4 | SEC-001 | Security | HALF_DAY | MEDIUM | TASK-0004, TASK-0005 | CG-HTACCESS |
| 2 | TASK-0013 | READY | WAVE-1 | SEO-EXT-001, SEO-EXT-002 | DevOps | LT_30_MIN | LOW | — | CG-HTACCESS |
| 2 | TASK-0015 | READY | WAVE-1 | SEO-EXT-001, SEO-EXT-002 | DevOps/Marketing (humano) | LT_30_MIN | LOW | TASK-0013 | NONE |
| 3 | TASK-0014 | READY | WAVE-2 | SEO-EXT-001 | Marketing/Comunidad (humano) | HALF_DAY | LOW | TASK-0013 | NONE |
| 4 | TASK-0016 | BLOCKED | WAVE-3 | SEO-EXT-001 | Contenido/Comunidad | ONE_DAY | LOW | TASK-0013 | NONE |

Nota (continuación 2026-07-19): TASK-0013–0016 provienen de la auditoría SEO externa (`working/seo-external.md`). Los cambios de código de TASK-0013 ya están hechos en fuente; la tarea es desplegarlos y validarlos.
