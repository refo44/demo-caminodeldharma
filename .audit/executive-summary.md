# Resumen ejecutivo — Auditoría Camino del Dharma (2026-07-19)

> **Revisión 2026-07-19 (continuación):** la versión original de este resumen presentaba HSTS como decisión titular con `max-age=31536000` inmediato. HSTS es **uno más** de los criterios evaluados (seguridad de transporte), no el objetivo de la auditoría, y el sitio estático actual es **temporal** (será reemplazado por WordPress). La decisión vigente es el **despliegue escalonado** del ADR 0018: **no** configurar un `max-age` de un año en esta etapa.

**Estado general: 84/100.** Sitio estático pequeño y excepcionalmente bien construido: SEO on-page, datos estructurados, accesibilidad estructural y despliegue impecables. Cero hallazgos críticos. Nota: la puntuación SEO 100 refleja solo el **SEO interno/técnico**; la visibilidad externa en buscadores se evalúa en la continuación (ver `working/seo-external.md`).

**Lo que sí está roto (2 hallazgos ALTOS, ambos con arreglo barato):**
1. El **formulario de contacto no entrega mensajes** (envía a `action="#"` sin backend): quien lo usa cree haber escrito a la comunidad y el mensaje se pierde en silencio.
2. Las **descargas de calendario .ics devuelven 404** en los dos eventos (2 de las 4 opciones de "Añadir al calendario").

**Riesgos medios:** HSTS pendiente — activar en **Fase 1 con `max-age=604800` (7 días)**, dejando el año completo para después del corte a WordPress estable (ADR 0018) —, CSP mínima, cookies de Google Analytics sin consentimiento ni política de privacidad publicada, e imágenes sobredimensionadas (logo de 1000 px (46 KB servidos) para un hueco de 44 px).

**Entregable:** 12 tareas atómicas para agentes implementadores externos (9 listas, 3 bloqueadas por decisiones humanas: backend del formulario, enfoque de consentimiento, inventario de subdominios). Arranque recomendado por impacto en usuarios: crear los 2 .ics (<30 min) → sustituir el formulario muerto por CTAs de WhatsApp/correo → activar HSTS Fase 1 (30 min, reversible en días).

**Decisiones que solo la comunidad puede tomar:** (1) ¿formulario real con backend o solo WhatsApp/correo?; (2) enfoque de consentimiento/privacidad y texto de la política.

---

## Adenda de la continuación (2026-07-19) — SEO externo

**El sitio es invisible para búsquedas temáticas.** Búsquedas reales verificadas: para "budismo en colombia", "comunidad/centro budista colombia", "budismo chan colombia", "budismo tierra pura colombia", "budismo cali" y "retiro budista colombia", caminodeldharma.org **no aparece en el top 10** de ninguna. Solo el nombre exacto lo encuentra — y el SERP de marca muestra una página residual de WordPress titulada "prueba" y una URL vieja `?page_id=10`. La puntuación SEO 100 de la auditoría original medía únicamente el SEO interno.

**Causas:** autoridad externa casi nula (el artículo de Buddhistdoor que describe a la comunidad enlaza solo a Facebook; el sitio no está en el directorio budismo.com), índice contaminado por residuos de WordPress, cero señales locales (el sitio no decía que nació en Cali en 2012) y contenido temático mínimo (1 entrada de blog).

**Hecho en esta continuación (en fuente, pendiente de deploy — TASK-0013):** redirects 410/301 para las URLs residuales, datos estructurados con fundación (Cali, 2012) y `knowsAbout`, menciones locales en home y comunidad, títulos temáticos en eventos y blog.

**Pendiente de humanos:** alta en Search Console/Bing (TASK-0015), directorio budismo.com + enlace de Buddhistdoor + URL en perfiles sociales (TASK-0014), plan editorial de contenido temático (TASK-0016 — mayor palanca a medio plazo; la meditación semanal online es un diferenciador único frente a los competidores presenciales).
