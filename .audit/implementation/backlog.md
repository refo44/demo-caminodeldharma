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
