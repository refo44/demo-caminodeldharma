# Roadmap priorizado

## Críticos
Ninguno.

## Quick wins (<30 min)
| Prio | Finding | Acción | Esfuerzo | Beneficio | Riesgo | Owner | Dep. |
|---:|---|---|---|---|---|---|---|
| 1 | SEC-001 | TASK-0004: activar HSTS (descomentar .htaccess:103) + TASK-0005 verificación | <30m + <30m | ALTO | BAJO | DevOps/Security | decisión ya registrada |
| 2 | FUNC-002 | TASK-0001: crear los 2 archivos .ics | <30m | ALTO | BAJO | Frontend | — |
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
