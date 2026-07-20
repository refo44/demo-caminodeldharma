# SEO-EXT-001 — Remediation Package
Visibilidad orgánica débil en consultas amplias y locales; nicho ya ganado.

## Resolution objective
Que el sitio sea descubrible por consultas temáticas y no solo por el nombre exacto, y que la autoridad del dominio deje de ser el techo del posicionamiento.

## Acceptance criteria
1. GSC muestra impresiones y clics para consultas que no son de marca.
2. Se conserva el #1 en «budismo chan colombia» y «budismo tierra pura colombia».
3. Presencia en pack local o página 1 para «budismo cali».
4. Al menos 2 dominios temáticos externos enlazan a caminodeldharma.org.
5. DA re-medido con la MISMA herramienta alcanza 8 (primer hito).

## Evidence and root cause addressed
EVID-0033 (ausencia en 8 consultas temáticas), EVID-0037 (Google CO real: #1 en nicho, ausente en amplias/locales), EVID-0035/0036/0044 (tres fuentes autorizadas citan sin enlazar), EVID-0043/0045/0046 (DR 0,4 · DA 6 · Spam Score 7 % · antigüedad 7a5m; baseline de competidores DA 8–20).

Causa raíz VERIFICADA y cuantificada: autoridad casi nula sobre un dominio establecido —es decir, ausencia de enlaces, no toxicidad—, agravada por falta de perfil de empresa (los packs locales ocupan el top de todas las consultas locales), contenido temático mínimo (1 entrada de blog) e indexación parcial (4 de 13 URLs).

## Scope and implementation locations
Fuera del código: perfiles externos, directorios, Search Console y plan editorial. En el sitio, solo lo derivado del plan de contenidos.

## Prerequisites and dependencies
Despliegue de los cambios on-page ya aplicados en fuente (TASK-0013).

## Immediate containment, when required
No aplica: no hay degradación activa, sino ausencia de crecimiento.

## Minimal safe fix / Preferred durable fix
**Mínimo:** las tres peticiones de enlace a fuentes que ya citan a la comunidad + alta en directorio + Search Console.
**Duradero:** perfil de empresa con actividad real, plan editorial sostenido y re-medición trimestral de autoridad con la misma herramienta.

## Ordered implementation steps
1. Desplegar TASK-0013 (on-page ya hecho en fuente).
2. Search Console: verificar propiedad, enviar sitemap, retirar URLs residuales, exportar consultas (TASK-0015).
3. Google Business Profile: decidir y, si procede, crear (TASK-0014) — mayor palanca en consultas locales.
4. Tres peticiones de enlace + alta en directorio + URL en perfiles sociales (TASK-0014).
5. Plan editorial temático (TASK-0016) y meditación semanal como entidad citable (TASK-0017).

## Proposed code, configuration, content, schema, or infrastructure changes
[PROPUESTO — NO EJECUTADO] Sin cambios de código propios de este paquete. Los on-page asociados están descritos en `working/seo-external.md` §6 y ya aplicados en fuente a la espera de despliegue.

## Local validation
No aplica (efecto externo).

## Automated and regression tests
Los controles de SEO interno deben seguir en PASS: títulos únicos, canónicas, sitemap.

## Deployment sequence
TASK-0013 → TASK-0015 → TASK-0014 → TASK-0016/0017.

## Production verification and monitoring
Repetir la batería de consultas de `working/seo-external.md` §3 y §8 a las 4–8 semanas. GSC: impresiones no-marca. Autoridad: re-medir cada trimestre con la misma herramienta.

## Rollback triggers / procedure
No aplica: no hay cambio reversible. Si el contenido nuevo canibalizara consultas de marca, revisar títulos y enlazado interno.

## Trade-offs and residual risks
Depende de terceros (medios y directorios) y de plazos de meses. La citación por plataformas de IA no es controlable directamente.

## Human decisions or approvals required
Dirección física para el perfil de empresa; aprobación del plan editorial; envío de las peticiones en nombre de la comunidad.

## Owner and effort
Marketing/Comunidad — medio día de gestión + plan editorial sostenido.
