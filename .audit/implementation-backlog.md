# Backlog de implementación (índice)

Ledger canónico: `implementation/tasks.jsonl` · Tabla priorizada: `implementation/backlog.md` · Paquetes: `implementation/tasks/TASK-00XX.md`.

**Actualización 2026-07-21.** Revisión que corrige dos estados y añade una tarea.

Resumen: **22 tareas** — **12 COMPLETED** · **1 PARTIALLY_COMPLETED** · **2 READY** · **7 BLOCKED**

## Ejecutables ahora

| Tarea | Qué | Esfuerzo |
|---|---|---|
| **TASK-0017** | Meditación semanal como entidad citable: página propia, `EventSeries` en línea, `llms.txt`, sitemap. CTA de WhatsApp — **sin publicar el enlace de Zoom** | ~30 min – 2 h |
| **TASK-0021** | Etiqueta visible de modalidad y ciudad en los eventos, derivada del JSON-LD ya existente | ~30 min – 2 h |

## Correcciones de estado (2026-07-21)

**TASK-0017: COMPLETED → READY.** Quedó marcada como completada en el cierre en bloque del deploy v1.0.14, pero **ninguno de sus cuatro criterios se cumple**: `sitemap.xml` mantiene 13 URLs sin la de la meditación, `practica/index.html` no contiene `EventSeries` ni `OnlineEventAttendanceMode`, `llms.txt` no menciona la sesión semanal y no existe página que enlazar. El prerrequisito que la bloqueaba quedó resuelto: **puerta por WhatsApp**.

**TASK-0014: COMPLETED → PARTIALLY_COMPLETED.** Peticiones de enlace enviadas y URL de la web en Facebook: hechas. **Instagram: pendiente.** **Google Business Profile: descartado** — sin sede ni dirección física la comunidad no es elegible. **Directorio budismo.com: retirado** de las recomendaciones (autoridad nunca medida, sin mantenimiento desde 2010, sin formulario de alta).

## Estado completo

| Estado | Tareas |
|---|---|
| **COMPLETED** (12) | 0001, 0002, 0006, 0007, 0008, 0009, 0010, 0011, 0013, 0015, 0018, 0019 |
| **PARTIALLY_COMPLETED** (1) | 0014 — off-page: falta Instagram; GBP y directorio descartados |
| **READY** (2) | **0017** meditación semanal · **0021** etiquetas de evento |
| **BLOCKED** (7) | 0003 formulario real · 0004/0005 HSTS (ADR 0020) · 0012 DNS · 0016 plan editorial · **0022** historial de encuentros · **0020** páginas por ciudad (depende de 0022) |

## Bloqueadas: qué las desbloquea

| Tarea | Espera |
|---|---|
| **0022** | **Listado de encuentros pasados por ciudad** (fecha, qué fue, fotos). Es un **dato de archivo, no una decisión** — probablemente esté en redes o en la memoria de quien los organizó |
| 0020 | TASK-0022 primero. Después: confirmar por ciudad si hay práctica recurrente o solo encuentros periódicos |
| 0016 | Plan editorial |
| 0003 | Si hace falta formulario con backend; WordPress lo resolverá |
| 0004, 0005 | Corte a WordPress estable + 30 días (ADR 0020) |
| 0012 | Inventario DNS (panel de Hostinger) |

## Estructura real de la comunidad (registrada 2026-07-21)

| | Qué | Dónde |
|---|---|---|
| **Práctica continua** | Meditación semanal | **En línea**, lunes |
| **Encuentros** | **2–3 al año por ciudad** | Presenciales: Cali, Bogotá, Medellín, Barranquilla |

Esto ordena la arquitectura pendiente: la meditación en línea es la **única práctica continua** (TASK-0017, de ahí su prioridad), y la relevancia geográfica viene del **historial de encuentros reales** (TASK-0022), no de páginas por ciudad creadas de antemano.

**Próxima acción:** TASK-0017 y TASK-0021, ambas ejecutables sin esperar a nadie. Calendario de medición y verificación del corte a WordPress en `audit-schedule.md`.
