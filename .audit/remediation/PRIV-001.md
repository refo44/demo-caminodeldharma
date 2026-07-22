# PRIV-001 — Remediation Package
Cookies GA4 sin consentimiento y sin política de privacidad publicada.

## Resolution objective
Identificadores persistentes solo tras consentimiento (o configuración exenta documentada) + política de tratamiento de datos publicada y enlazada.

## Acceptance criteria
1. Con perfil limpio, ninguna cookie `_ga*` antes de la acción de consentimiento (si se elige la vía de consentimiento).
2. Página de política publicada (p. ej. /privacidad) enlazada desde el footer de las 14 páginas.
3. Embeds de YouTube en modo privacidad (youtube-nocookie.com).

## Evidence and root cause addressed
EVID-0012 (gtag en línea 16 de cada página), EVID-0018 (cookies inmediatas, sin banner), EVID-0031 (sin política). Causa raíz VERIFICADA: instalación por defecto sin capa de consentimiento.

## Scope and implementation locations
Cabecera HTML de las 14 páginas (snippet gtag), nueva página /privacidad, footer de todas las páginas, iframes de YouTube (index.html:310,316; practica/index.html:261,267; practica/videos/index.html:189-207).

## Prerequisites and dependencies
DECISIÓN ORGANIZATIVA pendiente: (a) Consent Mode v2 con banner por defecto denegado, (b) medición sin cookies, o (c) aceptar riesgo documentado. Texto de política aprobado por la comunidad. → La tarea queda BLOCKED hasta esa decisión.

## Immediate containment, when required
Opcional y de bajo costo sin decisión legal: cambiar ya los embeds a youtube-nocookie.com.

## Minimal safe fix
Publicar la política + embeds nocookie (no resuelve el consentimiento pero reduce superficie y da transparencia).

## Preferred durable fix
Consent Mode v2: `gtag('consent','default',{analytics_storage:'denied'})` antes de `config`, banner accesible propio (sin dependencia externa), `gtag('consent','update',...)` al aceptar.

## Ordered implementation steps
1. Obtener decisión y texto de política (BLOQUEANTE).
2. Crear /privacidad con el texto aprobado (misma plantilla estática).
3. Añadir enlace en footer de las 14 páginas.
4. Insertar default-denied antes del config de gtag en las 14 páginas; añadir banner.
5. Cambiar 7 iframes a youtube-nocookie.com.
6. Verificar con perfil limpio en 3 plantillas.

## Proposed code, configuration, content, schema, or infrastructure changes
[PROPUESTO — NO EJECUTADO]
```html
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('consent', 'default', { analytics_storage: 'denied' });
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-B8FY69RGSS"></script>
```

## Local validation
Abrir con perfil limpio: sin cookies _ga hasta aceptar.

## Automated and regression tests
Comprobación de cookies en home, evento y blog; navegación con banner por teclado (focus visible, cierre accesible).

## Deployment sequence
Política primero, luego capa de consentimiento, luego embeds.

## Production verification and monitoring
Auditoría de cookies post-deploy; caída esperada de tráfico medido en GA4 (registrar baseline antes).

## Rollback triggers
Banner rompe navegación o accesibilidad.

## Rollback procedure
git revert de las páginas afectadas; los datos GA4 no se pierden (solo medición durante el intervalo).

## Trade-offs and residual risks
Menos tráfico medido tras consentimiento. Las conclusiones legales corresponden a asesoría, no a esta auditoría.

## Human decisions or approvals required
Vía (a/b/c) + texto de política. Registradas como bloqueador de TASK-0006.

## Owner and effort
Product + Frontend — 1 día una vez decidido.
