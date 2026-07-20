# SEO-EXT-002 — Remediation Package
Índice contaminado por URLs residuales de la etapa WordPress.

## Resolution objective
Que el índice contenga solo URLs canónicas del sitemap y que ninguna página de prueba aparezca en el SERP de marca.

## Acceptance criteria
1. `curl https://caminodeldharma.org/prueba` → 410.
2. `curl -L .../category/noticias` → 200 en `/blog`.
3. `curl '.../?page_id=10'` → 301 a `/comunidad`.
4. Tras la ventana de recrawl, `site:caminodeldharma.org` no devuelve `/prueba` ni `/category/*`.

## Evidence and root cause addressed
EVID-0032 (índice con residuos: `/prueba` en posición 2 del SERP de marca en DDG), EVID-0034 (estados verificados: 301→404 y 200 duplicado).

Causa raíz VERIFICADA: el dominio alojó WordPress; el reemplazo estático no incluyó mapa de redirects para las URLs de aquella etapa —solo se mapearon 3 rutas legacy.

## Scope and implementation locations
`.htaccess`, bloque «Rutas antiguas», líneas ~33-43.

## Prerequisites and dependencies
Ninguno.

## Immediate containment, when required
No aplica.

## Minimal safe fix / Preferred durable fix
**Mínimo:** las 4 reglas ya aplicadas en fuente.
**Duradero:** las mismas + solicitudes de retirada en Search Console para acelerar la desindexación, y vigilancia del informe de cobertura.

## Ordered implementation steps
1. Desplegar `.htaccess` (TASK-0013).
2. Ejecutar la matriz de verificación por curl.
3. Solicitar retirada e inspección de URLs en GSC (TASK-0015).

## Proposed code, configuration, content, schema, or infrastructure changes
[APLICADO EN FUENTE — PENDIENTE DE DESPLIEGUE]
```apache
RewriteRule ^prueba/?$ - [G,L]
RewriteRule ^category(/.*)?$ /blog [R=301,L]
RewriteCond %{QUERY_STRING} (^|&)page_id=10(&|$)
RewriteRule ^$ /comunidad? [R=301,L]
RewriteCond %{QUERY_STRING} (^|&)page_id=
RewriteRule ^$ /? [R=301,L]
```

## Local validation
Revisión del diff de `.htaccess`.

## Automated and regression tests
Las 13 páginas canónicas siguen en 200; las 3 rutas legacy previas siguen redirigiendo igual; el 301 de barra final intacto.

## Deployment sequence
Un solo despliegue, junto al resto de TASK-0013.

## Production verification and monitoring
Matriz de curl de los criterios de aceptación. GSC: cobertura, que el recuento de URLs indexadas residuales tienda a cero.

## Rollback triggers / procedure
Si alguna página canónica quedara capturada por las reglas: `git revert` de `.htaccess` y redesplegar el ZIP.

## Trade-offs and residual risks
El 410 de `/prueba` descarta cualquier valor de enlace acumulado — aceptable por ser una página de prueba. Sin solicitudes en GSC, la desindexación tarda semanas.

## Human decisions or approvals required
Ninguna.

## Owner and effort
DevOps — < 30 min.
