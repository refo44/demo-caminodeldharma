# Insumos manuales pendientes — Camino del Dharma

Lo que la auditoría **no puede obtener sola** y sigue abierto, ordenado por cuánto cambia las
conclusiones. Procedimiento general y formatos de entrega: `MANUAL_INPUTS.md` (raíz del tool).

> **Instrucciones paso a paso: [`manual-inputs-howto.md`](manual-inputs-howto.md)**

> **Actualización 2026-07-20:** el propietario confirmó deploy v1.0.14 y cierre de las tareas READY + TASK-0001. Los ítems de despliegue, GSC, GBP y peticiones de enlace pasan a **Ya entregados** abajo.

Formato de entrega, por orden de utilidad: **CSV/XLSX exportado** > valores escritos en el chat >
captura **recortada** del panel de resultados.

---

## Ya entregados ✅

| Código | Qué | Entregado | Registro |
|---|---|---|---|
| M-01 | Autoridad del dominio (Dapachecker DA/PA + DR/UR) | XLSX, 2026-07-20 | EVID-0043 |
| M-01 | Autoridad (SEO Review Tools) | CSV, 2026-07-20 | EVID-0045 |
| M-02 | Baseline de 5 competidores | 5 CSV, 2026-07-20 | EVID-0046 — TASK-0019 |
| M-10 | PageSpeed Insights (móvil + escritorio) | URLs, 2026-07-20 | EVID-0047 / EVID-0048 |
| **M-11** | **Deploy producción v1.0.14** | **2026-07-20** | **TASK-0001, 0013 y resto READY — confirmación propietario** |
| **M-12** | **Google Search Console** | **2026-07-20** | **TASK-0015 — confirmación propietario** |
| **M-13** | **GBP + 3 peticiones de enlace + URL en redes** | **2026-07-20** | **TASK-0014 — confirmación propietario** |

---

## Pendientes

### 1. Decisiones de la comunidad — bloquean 4 tareas

| Decisión | Tarea | El intercambio real |
|---|---|---|
| ¿Formulario con backend real o solo WhatsApp/correo? | TASK-0003 | CTAs ya desplegados (TASK-0002); fix duradero opcional |
| Plan editorial y temas de contenido | TASK-0016 | Voz de la comunidad vs. captación de búsquedas |
| ¿En qué ciudades hay sangha real? | TASK-0020 | Sin actividad confirmada = doorway pages |
| Texto de política de privacidad (si aún no publicada) | — | Ley 1581/2012; TASK-0006 marcada COMPLETED por propietario |

**Entregar:** la decisión y su razonamiento (se registra y desbloquea la tarea).

---

### 2. Inventario DNS — TASK-0012 · ~15 min

**Bloqueo:** panel de Hostinger con credenciales.

**Qué hacer:** exportar la zona DNS o la lista de subdominios.

**Qué desbloquea:** evaluación de `includeSubDomains` para HSTS (solo tras TASK-0004/0005 post-WordPress).

---

## No obtenibles (limitaciones aceptadas)

- **Trust Flow / Citation Flow** (Majestic): requiere cuenta.
- **INP**: sin datos CrUX para este origen.
- **Comprobadores de "PageRank"**: descartados. Ver `working/authority-backlinks.md` §6.
