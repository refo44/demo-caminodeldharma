# Roadmap priorizado

> **Actualización 2026-07-20:** deploy v1.0.14 cerró la mayoría de ítems abajo (TASK-0001, 0002, 0006–0011, 0013–0015, 0017–0018). Pendiente: HSTS post-WordPress (0004/0005), formulario real (0003), DNS (0012), plan editorial (0016), sanghas (0020).

## Críticos
Ninguno.

## Quick wins (<30 min)
| Prio | Finding | Acción | Esfuerzo | Beneficio | Riesgo | Owner | Dep. |
|---:|---|---|---|---|---|---|---|
| 1 | FUNC-002 | TASK-0001: crear los 2 archivos .ics | <30m | ALTO | BAJO | Frontend | — |
| 2 | SEC-001 | TASK-0004: activar HSTS **Fase 1 `max-age=604800`** (.htaccess:103, ADR 0018 — no el año completo mientras el estático temporal siga en producción) + TASK-0005 verificación | <30m + <30m | ALTO | BAJO | DevOps/Security | decisión escalonada registrada |
| 3 | SEC-003 | TASK-0009: publicar security.txt | <30m | BAJO | BAJO | DevOps | — |

## Tareas pequeñas (30 min – 2 h)
| Prio | Finding | Acción | Esfuerzo | Beneficio | Riesgo | Owner | Dep. |
|---:|---|---|---|---|---|---|---|
| 4 | FUNC-001 | TASK-0002: retirar formulario muerto → CTAs funcionales | 30m-2h | MUY ALTO | BAJO | Frontend | — |
| 5 | PERF-001 | TASK-0007: logo + miniaturas + srcset | 30m-2h | MEDIO | BAJO | Frontend | — |
| 6 | PERF-002 | TASK-0011: versionado ?v= de CSS/JS | 30m-2h | MEDIO | BAJO | Frontend | TASK-0007 |
| 7 | AEO-001 | TASK-0010: pre-render galería sin JS | 30m-2h | BAJO | BAJO | Frontend | TASK-0011 |

## Tareas medianas (½–1 día)
| Prio | Finding | Acción | Esfuerzo | Beneficio | Riesgo | Owner | Dep. |
|---:|---|---|---|---|---|---|---|
| 8 | SEC-002 | TASK-0008: CSP Report-Only → enforce | ½ día | MEDIO | MEDIO | DevOps | TASK-0004 |
| 9 | PRIV-001 | TASK-0006: consentimiento + política + nocookie (BLOQUEADA: decisión) | 1 día | ALTO | MEDIO | Product | decisión org. |
| 10 | FUNC-001 | TASK-0003: formulario real (BLOQUEADA: decisión) | 1 día | ALTO | MEDIO | Full-stack | TASK-0002 + decisión |
| 11 | SEC-001 | TASK-0012: evaluación includeSubDomains (BLOQUEADA: DNS + estabilidad) | ½ día | BAJO | MEDIO | Security | TASK-0004/0005 |

## Grandes / refactors estratégicos
Ninguno necesario. (Futuro opcional, fuera de backlog: versión EN real del sitio, AVIF, pipeline de build.)
