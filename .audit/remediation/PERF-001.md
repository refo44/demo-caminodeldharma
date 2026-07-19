# PERF-001 — Remediation Package
Imágenes sobredimensionadas (logo 1000px (46KB servidos/36KB repo) @44px; miniaturas galería 1000px@159px; sin srcset).

## Resolution objective
Ninguna imagen entregada a más de ~2.5x su tamaño máximo mostrado (DPR2).

## Acceptance criteria
1. Asset de logo ≤10KB y ≤240px (o SVG).
2. Miniaturas de galería en home servidas ≤500px vía srcset.
3. Grid de /galeria usa variantes miniatura.
4. Paridad visual verificada en 390px y 1440px.

## Evidence and root cause addressed
EVID-0021 (ratios medidos), EVID-0027 (logo.png 46.025 B servidos; 36.364 B en el repo — registrar la diferencia al implementar). Causa raíz VERIFICADA: exportes de tamaño único; sin pipeline de variantes (scripts/optimize-images.sh optimiza pero no genera tamaños).

## Scope and implementation locations
`assets/images/logo.png`; `assets/images/galeria/*.jpg` (variantes nuevas -400); sección galería de `index.html`; `renderGrid()` en `assets/js/gallery.js`; JSON `#gallery-data` de `galeria/index.html`.

## Prerequisites and dependencies
Ninguno. Coordinar con PERF-002 (mismo HTML) — mismo conflict group CG-HTML-GLOBAL, ejecutar antes que TASK-0011.

## Immediate containment, when required
No aplica.

## Minimal safe fix
Reemplazar solo logo.png por exporte 240x240 optimizado (sin cambio de markup; mayor ganancia unitaria). Nota: existe favicon.svg como fuente vectorial del emblema.

## Preferred durable fix
Variantes -400 de galería + srcset/sizes en home y gallery.js.

## Ordered implementation steps
1. Generar logo 240x240 (PNG optimizado o SVG) sustituyendo assets/images/logo.png. Resultado: ≤10KB.
2. Generar `galeria-XX-400.jpg` para las imágenes usadas en home (galeria-01/02/03) y las del grid.
3. index.html: añadir `srcset`/`sizes` a las 3 miniaturas de galería.
4. gallery.js `renderGrid()`: usar variante -400 como `src` y original en `srcset` 1000w.
5. Actualizar JSON #gallery-data si contiene rutas.
6. Verificación visual + re-ejecutar el script de auditoría de imágenes (raw/performance/image-audit.txt como referencia).

## Proposed code, configuration, content, schema, or infrastructure changes
[PROPUESTO — NO EJECUTADO]
```html
<img src="/assets/images/galeria/galeria-01-400.jpg"
     srcset="/assets/images/galeria/galeria-01-400.jpg 400w, /assets/images/galeria/galeria-01.jpg 1000w"
     sizes="(max-width: 600px) 45vw, 220px" width="400" height="400" alt="..." loading="lazy">
```

## Local validation
Script de ratios naturalWidth/display ≤2.5 en home y galería.

## Automated and regression tests
Paginación de galería intacta; lazy loading conservado; CLS sigue 0.

## Deployment sequence
Assets primero (compatibles hacia atrás), markup/JS después.

## Production verification and monitoring
content-length del logo ≤10000; peso de home reducido.

## Rollback triggers
Regresión visual (borrosidad en pantallas densas).

## Rollback procedure
Restaurar assets y markup previos desde git.

## Trade-offs and residual risks
Más archivos de asset que mantener; convención -400 debe documentarse.

## Human decisions or approvals required
Ninguna.

## Owner and effort
Frontend — 30 min a 2 h.
