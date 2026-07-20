# Cronograma de auditorías — Camino del Dharma

Definido el 2026-07-20 con dos condicionantes: el sitio actual tiene **2 días de vida**, y el corte a
WordPress se estima en **10–15 días**. Ambos determinan cuándo tiene sentido medir y cuándo no.

## Principio que ordena el calendario

**No medir eficacia antes de que haya podido actuar, y no medir estabilidad en mitad de un cambio.**
Una auditoría SEO completa a las 4 semanas sería inútil si WordPress entra en la semana 2: la
migración invalida las mediciones. Por eso el calendario se organiza **alrededor del corte**, no en
intervalos fijos.

---

## ⚠️ Riesgo de calendario detectado

| Fecha | Hecho |
|---|---|
| 2026-07-30 → 08-04 | Corte a WordPress (estimado) |
| **2026-08-07 → 08-09** | **7.º Encuentro Nacional Buddhista** |

**Entre 3 y 8 días de margen.** Se estaría migrando el sitio justo antes del evento más importante del
año — precisamente cuando más gente buscará información, horarios y ubicación.

Si la migración sale mal, sale mal en el peor momento posible. Tres opciones, por orden de prudencia:

1. **Aplazar el corte a después del 10 de agosto.** El estático funciona; no hay urgencia técnica que
   justifique el riesgo.
2. **Adelantarlo a antes del 28 de julio**, dejando ≥10 días de margen para detectar y corregir.
3. Mantener el plan **con un rollback ensayado y decidido de antemano** (ZIP del estático listo para
   restaurar en minutos).

Recomendación: la **opción 1**. Es una decisión de la comunidad, no técnica, pero conviene tomarla a
conciencia y no por inercia.

---

## Calendario

### Hito 0 — Antes del corte · no es auditoría, es prerrequisito
**Cuándo:** esta semana · **Esfuerzo:** ver `manual-inputs-howto.md`

Desplegar lo que ya está en fuente (TASK-0013, TASK-0001, embeds sin cookies, `localStorage` retirado)
y avanzar los insumos manuales. Todo lo que se despliegue **después** del corte se confunde con los
efectos de la migración.

---

### Hito 1 — Fotografía pre-migración 📸
**Cuándo:** 2–3 días **antes** del corte · **Esfuerzo:** ~1 h · **Criticidad: ALTA**

El hito más importante del calendario, y el más fácil de olvidar. Sin un «antes» no hay forma de
demostrar si la migración rompió algo.

Capturar y archivar en `raw/pre-migracion/`:

- Export completo de Search Console (consultas, páginas, cobertura)
- PSI móvil y escritorio
- Inventario de las 13 URLs con su código de estado y canónica
- Posiciones de la batería de consultas (`working/seo-external.md` §3 y §8)
- DA/DR con la misma herramienta de siempre
- `curl -sS -D -` de la portada (cabeceras completas)
- Copia del `.htaccess` vigente

---

### Hito 2 — Verificación post-corte 🔴
**Cuándo:** 24–48 h **después** del corte · **Esfuerzo:** ~2 h · **Criticidad: MÁXIMA**

El momento de mayor riesgo de todo el proyecto. Qué verificar, en orden:

1. **Paridad de URLs:** las 13 responden 200 y la canónica no cambió (ADR 0008)
2. **`.htaccess`:** WordPress **reescribe su propio bloque** `# BEGIN WordPress`. Confirmar que
   sobrevivieron: redirects legacy, limpieza WordPress, `AddType text/calendar`, cabeceras de
   seguridad, HSTS si ya está activo
3. **Sin cookies:** verificar que WordPress o algún plugin no introduce cookies (ADR 0019)
4. **Datos estructurados:** JSON-LD íntegro tras el cambio de plantillas
5. **`sitemap.xml` y `robots.txt`:** WordPress genera los suyos — no deben duplicar ni contradecir
6. **Rendimiento:** PSI contra la fotografía del Hito 1; PHP y plugins cambian el perfil
7. **`llms.txt`** sigue servido
8. **Los `.ics` y el diálogo de calendario** siguen operativos

**Criterio de rollback:** si algo crítico falla, restaurar el ZIP del estático. Decidirlo antes, no
durante.

---

### Hito 3 — Estabilización post-migración
**Cuándo:** ~2 semanas tras el corte · **Esfuerzo:** ~1 h

- Cobertura en GSC: ¿reindexó WordPress las 13 URLs?
- Posiciones frente al Hito 1 — una caída temporal es normal; una sostenida, no
- ¿Consolidó Google las variantes `https`/`www`/`http`? (pendiente detectado en EVID-0052)
- Errores nuevos en GSC

---

### Hito 4 — Eficacia de las acciones SEO
**Cuándo:** ~2026-09-15 (8 semanas desde las peticiones de enlace) · **Esfuerzo:** ~2 h

Primera medición **legítima** de si el trabajo de posicionamiento funciona:

- ¿Aceptaron el enlace Buddhistdoor, EcoEspiritualidad y budismo.com?
- DA/DR re-medidos con la misma herramienta → ¿se acerca el hito de **DA 8**?
- GSC: ¿aparecen consultas que no son la marca?
- Repetir la batería de consultas, incluidas las de tipo pregunta (ASO-001)
- ¿El perfil de empresa entró en los packs locales?

---

### Hito 5 — Auditoría completa del sitio definitivo
**Cuándo:** ~30 días de WordPress estable (≈ 2026-10) · **Esfuerzo:** auditoría completa

**No es una revisión incremental: es una auditoría nueva.** WordPress es otra pila tecnológica y trae
superficie que hoy no existe — PHP, plugins, panel de administración, base de datos, cuentas de
usuario, formularios reales, posible caché.

Áreas que hay que auditar desde cero: seguridad de la aplicación (no solo transporte), plugins y sus
vulnerabilidades, exposición del `wp-admin`, rendimiento con caché, y todo lo que el modelo de
contenido añada (CPT de sanghas, taxonomías).

Además es la **puerta del HSTS**: por ADR 0020 la activación quedó aplazada por completo durante la
transición, así que aquí se decide si se activa —y con qué `max-age`— tras ≥30 días sin incidencias de
TLS ni redirects, con los datos de tráfico de ese momento.

---

## Vigilancia continua (sin auditoría formal)

| Qué | Cuándo | Por qué |
|---|---|---|
| **Caducidad del certificado TLS** | **antes del 2026-10-12** | Si HSTS está activo, un fallo de certificado deja el sitio inaccesible |
| Cobertura en GSC | mensual, 5 min | Detecta problemas de indexación pronto |
| Re-medición de autoridad | trimestral, misma herramienta | La comparabilidad exige no cambiar de herramienta |

---

## Resumen

| Hito | Cuándo | Esfuerzo | Criticidad |
|---|---|---|---|
| 0 · Desplegar lo pendiente | esta semana | — | Alta |
| 1 · Fotografía pre-migración | 2–3 días antes del corte | ~1 h | **Alta** |
| 2 · Verificación post-corte | 24–48 h después | ~2 h | **Máxima** |
| 3 · Estabilización | +2 semanas | ~1 h | Media |
| 4 · Eficacia SEO | ~2026-09-15 | ~2 h | Media |
| 5 · Auditoría completa de WordPress | ~2026-10 | completa | **Alta** |

**Si solo se hicieran dos, que sean el 1 y el 2.** Son los que protegen contra el único riesgo capaz
de deshacer todo el trabajo acumulado: una migración que rompe algo sin que nadie lo note a tiempo.
