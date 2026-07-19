# PERF-002 — Remediation Package
Versionado de CSS/JS (caché 7 días sin cache busting).

## Resolution objective
Un deploy invalida inmediatamente CSS/JS en visitantes recurrentes.

## Acceptance criteria
1. Todas las referencias a main.css/normalize.css/*.js llevan `?v=` igual al contenido de VERSION (hoy 1.0.11).
2. Cambiar VERSION y redeplegar produce URLs nuevas (fetch fresco verificado).
3. hcdn no ignora la query string en la clave de caché (verificar tras deploy).

## Evidence and root cause addressed
EVID-0027 (max-age=604800 sin fingerprinting), EVID-0008 (.htaccess:84-86). Causa raíz VERIFICADA: sitio artesanal sin build.

## Scope and implementation locations
`<link>`/`<script>` de las 14 páginas HTML.

## Prerequisites and dependencies
Ejecutar después de TASK-0007 (PERF-001) para no editar dos veces el mismo markup. Conflict group CG-HTML-GLOBAL.

## Immediate containment, when required
No aplica.

## Minimal safe fix
Query-string `?v=1.0.11` manual en todas las referencias.

## Preferred durable fix
Igual + paso documentado de release (bump VERSION + sed de referencias, p. ej. script en scripts/).

## Ordered implementation steps
1. sed/edición: `main.css` → `main.css?v=1.0.11` (y normalize.css, main.js, gallery.js, calendar.js, share.js) en las 14 páginas.
2. Documentar el paso de release en CONTRIBUTING.md o scripts/.
3. Deploy; verificar con curl que las páginas referencian URLs versionadas.
4. Verificar comportamiento de caché de hcdn con la query (dos fetches con v distinta).

## Proposed code, configuration, content, schema, or infrastructure changes
[PROPUESTO — NO EJECUTADO]
```html
<link rel="stylesheet" href="/assets/css/main.css?v=1.0.11">
```

## Local validation
`grep -R "main.css?v=" *.html */index.html | wc -l` = nº de páginas.

## Automated and regression tests
Render correcto con caché fría y caliente.

## Deployment sequence
Un solo deploy.

## Production verification and monitoring
curl del HTML publicado; prueba de invalidación en siguiente release.

## Rollback triggers
CDN con query ignorada (riesgo residual) → evaluar alternativa de renombrado de archivo.

## Rollback procedure
git revert de las páginas.

## Trade-offs and residual risks
Disciplina manual de bump; algunos CDN ignoran query strings — verificar.

## Human decisions or approvals required
Ninguna.

## Owner and effort
Frontend — 30 min a 2 h.
