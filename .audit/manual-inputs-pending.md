# Insumos manuales pendientes — Camino del Dharma

Lo que la auditoría **no puede obtener sola** y sigue abierto, ordenado por cuánto cambia las
conclusiones. Procedimiento general y formatos de entrega: `MANUAL_INPUTS.md` (raíz del tool).

> **Instrucciones paso a paso para ejecutarlos: [`manual-inputs-howto.md`](manual-inputs-howto.md)** — incluye borradores de los correos de petición de enlace, los comandos de verificación post-despliegue y el detalle de cada decisión.

Formato de entrega, por orden de utilidad: **CSV/XLSX exportado** > valores escritos en el chat >
captura **recortada** del panel de resultados. Captura de página completa **no sirve** (las cifras
quedan ilegibles). Indica siempre a qué URL corresponde cada resultado.

---

## Ya entregados ✅

| Código | Qué | Entregado | Registro |
|---|---|---|---|
| M-01 | Autoridad del dominio (Dapachecker DA/PA + DR/UR) | XLSX, 2026-07-20 | EVID-0043 |
| M-01 | Autoridad (SEO Review Tools) | CSV, 2026-07-20 | EVID-0045 — confirma la medición del agente |
| **M-02** | **Baseline de 5 competidores** | **5 CSV, 2026-07-20** | **EVID-0046 — TASK-0019 cumplida; limitación 14 cerrada** |
| **M-10** | **PageSpeed Insights (móvil + escritorio)** | **URLs de análisis, 2026-07-20** | **EVID-0047 / EVID-0048 — limitación 1 (LCP) cerrada; área de rendimiento completa** |

---

## Pendientes

### 1. Google Search Console — TASK-0015 · ~20 min

**Bloqueo:** propiedad de la cuenta y verificación del dominio.

**Nota:** Google mostró el anuncio *"¿Eres dueño de caminodeldharma.org? Prueba Search Console"* al
buscar `site:caminodeldharma.org`, lo que sugiere que **no hay propiedad conectada** — aunque el
`CHANGELOG` 1.0.12 menciona usar GSC. Verificar si existe ya una propiedad antes de crear otra.

**Qué hacer:** verificar la propiedad → enviar `sitemap.xml` → solicitar retirada de `/prueba`,
`/category/*` y `?page_id=10` → exportar los informes de consultas y cobertura.

**Entregar:** los informes exportados.

**Qué desbloquea:** sustituye **todas** las aproximaciones de posición de esta auditoría por datos
reales de impresiones y clics, y acelera la limpieza del índice residual.

---

### 2. Google Business Profile — TASK-0014 · decisión + ~30 min

**Bloqueo:** requiere dirección física confirmada y decisión de la comunidad.

**Qué hacer:** decidir con la comunidad si crear/reclamar el perfil. Si sí, completarlo.

**Entregar:** la decisión (y el motivo si es negativa) + URL del perfil si se crea.

**Qué desbloquea:** en **todos** los SERPs amplios y locales que probé, los packs locales ocupan las
primeras posiciones. Es la acción individual de mayor impacto para "budismo en colombia",
"comunidad budista colombia" y "budismo cali". Sin dirección pública no hay GBP — de ahí que sea
una decisión, no solo una tarea.

---

### 3. Tres peticiones de enlace — TASK-0014 · ~30 min

**Bloqueo:** enviar mensajes en nombre de la organización corresponde a la comunidad.

| A quién | Qué pedir | Por qué es viable |
|---|---|---|
| **Buddhistdoor en Español** | Añadir el enlace al dominio en los artículos que ya describen a la comunidad | Ya la citan como la referencia Chan del país; hoy solo enlazan a Facebook |
| **EcoEspiritualidad** | Añadir la URL del sitio a su ficha existente | La ficha es el **#2 de Google para la marca** y no tiene ningún enlace saliente |
| **budismo.com** | Alta en el directorio de centros de Colombia | Listan 11 centros; la comunidad no aparece |

Además: poner `caminodeldharma.org` en la bio de Facebook e Instagram.

**Entregar:** qué se envió, a quién, cuándo y la respuesta.

**Qué desbloquea:** es la causa raíz directa de DR 0,4 y, con el baseline en mano, la palanca
cuantificada: `budismocolombia.org` alcanza **DA 8 con 214 enlaces** frente a nuestros **207** — el
déficit es de calidad, no de volumen. Estas tres fuentes ya escriben sobre la comunidad; convertir
sus menciones en enlaces es el camino más corto al primer hito (**DA 8**).

---

### 4. Decisiones de la comunidad — bloquean 4 tareas

| Decisión | Tarea | El intercambio real |
|---|---|---|
| ¿Formulario con backend real o solo WhatsApp/correo? | TASK-0003 | Hoy el formulario pierde mensajes en silencio |
| Enfoque de consentimiento + texto de política de privacidad | TASK-0006 | Riesgo regulatorio vs. esfuerzo |
| ¿Publicar el enlace de Zoom directo o mantener la puerta por WhatsApp? | TASK-0017 | **Control comunitario vs. accesibilidad para agentes de IA** |
| Dirección editorial y temas del plan de contenidos | TASK-0016 | Voz de la comunidad vs. captación de búsquedas |

**Entregar:** la decisión y su razonamiento (se registra y desbloquea la tarea).

---

### 5. Inventario DNS — TASK-0012 · ~15 min

**Bloqueo:** panel de Hostinger con credenciales.

**Qué hacer:** exportar la zona DNS o la lista de subdominios.

**Qué desbloquea:** `includeSubDomains` de HSTS **nunca** debe deducirse de que el dominio principal
funcione. Sin inventario completo, esa decisión queda bloqueada por diseño.

---

### 6. Despliegue — TASK-0013 y TASK-0001

**Bloqueo:** el despliegue es manual (ZIP a Hostinger); los agentes no despliegan.

**Ya listo en código fuente, esperando deploy:**
- Redirects de limpieza del índice WordPress (410 `/prueba`, 301 `category/*`, 301 `?page_id=10`)
- JSON-LD con fundación (Cali, 2012) y `knowsAbout`
- Menciones locales en portada y comunidad
- Títulos temáticos en eventos y blog

**Entregar:** confirmación + versión/commit desplegado, para poder verificar contra un build conocido.

---

## No obtenibles (limitaciones aceptadas)

- **Trust Flow / Citation Flow / ratio TF-CF** (Majestic): requiere cuenta. Solo si la comunidad ya
  tiene una — no crear una cuenta para la auditoría.
- **INP (Interaction to Next Paint)**: requiere datos de campo, y CrUX responde "No hay datos" para
  este origen (tráfico insuficiente). No es medible en laboratorio. Solo se cerrará con más tráfico
  o instrumentando RUM.
- **Comprobadores de "PageRank"**: descartados deliberadamente. Google no publica PageRank desde
  2016; esas cifras no son auditables. Ver `working/authority-backlinks.md` §6.
