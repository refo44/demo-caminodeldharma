# Resumen ejecutivo — Auditoría Camino del Dharma (2026-07-19)

**DECISION: ACTIVATE HSTS NOW.** El dominio caminodeldharma.org puede activar hoy la cabecera auditada `Strict-Transport-Security "max-age=31536000"` (solo host): redirecciones HTTP→HTTPS directas y seguras, certificado válido para apex y www (TLS 1.3), cero contenido mixto y producción idéntica al código fuente auditado. `includeSubDomains` y `preload`: rechazados por ahora, como decisiones independientes futuras.

**Estado general: 84/100.** Sitio estático pequeño y excepcionalmente bien construido: SEO, datos estructurados, accesibilidad estructural y despliegue impecables. Cero hallazgos críticos.

**Lo que sí está roto (2 hallazgos ALTOS, ambos con arreglo barato):**
1. El **formulario de contacto no entrega mensajes** (envía a `action="#"` sin backend): quien lo usa cree haber escrito a la comunidad y el mensaje se pierde en silencio.
2. Las **descargas de calendario .ics devuelven 404** en los dos eventos (2 de las 4 opciones de "Añadir al calendario").

**Riesgos medios:** HSTS pendiente (ya aprobado), CSP mínima, cookies de Google Analytics sin consentimiento ni política de privacidad publicada, e imágenes sobredimensionadas (logo de 1000 px (46 KB servidos) para un hueco de 44 px).

**Entregable:** 12 tareas atómicas para agentes implementadores externos (9 listas, 3 bloqueadas por decisiones humanas: backend del formulario, enfoque de consentimiento, inventario de subdominios). Arranque recomendado: activar HSTS (30 min, reversible) → crear los 2 .ics (<30 min) → sustituir el formulario muerto por CTAs de WhatsApp/correo.

**Decisiones que solo la comunidad puede tomar:** (1) ¿formulario real con backend o solo WhatsApp/correo?; (2) enfoque de consentimiento/privacidad y texto de la política.
