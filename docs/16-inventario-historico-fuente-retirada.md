# Camino del Dharma — Inventario histórico de la fuente retirada

**Estado:** HISTORICAL — no usar como input de contenido, media, migración ni QA  
**Versión:** 2.0  
**Retiro:** 2026-08-29 (OWN-017, ADR 0040)

## Propósito

Este archivo conserva trazabilidad de una etapa anterior del proyecto. El directorio
`content-source/` reunía documentos de copy, ideas, identidad, fotografías, videos y dos audios
usados para construir el sitio estático.

El propietario ordenó eliminarlo permanentemente sin copia porque estaba desactualizado y generaba
ambigüedad. Sus referencias históricas no tienen fuerza operativa.

## Sustitutos vigentes

| Necesidad | Fuente vigente |
| --------- | -------------- |
| Copy, estructura, estilos y comportamiento pre-corte | `https://caminodeldharma.org` |
| Input determinista de extracción | `VERSION` + commit vigente del repo, comparado con Hostinger |
| Inventario de contenido y media | `inventario-contenido-produccion-static.md` |
| Conteos de reconciliación | `conteos-reconciliacion-migracion.md` |
| Identidad extraída | `02-identidad-corporativa.md`, CSS y assets versionados |
| Imágenes y audio estáticos | `assets/images/`, `assets/audio/` |
| Videos publicados | `practica/videos/index.html` |
| Contenido post-corte | WordPress |

## Material histórico retirado

La carpeta contenía:

- documentos de contenido web y lluvia de ideas en DOCX/Markdown;
- documentos y enlaces de videos;
- fotografías, logos y videos organizados por pestañas;
- `Amitabha.mp3` y `NamoGuanShiYinPusa.mp3`.

Los recursos que siguen operativos ya están versionados en el sitio. El logo de alta resolución
requerido por `npm run build:docx` vive en `assets/images/logo-docx-cover.png`.

## Regla

No recrear la carpeta, no buscar copias externas para restaurarla y no validar WordPress contra esos
materiales. Cualquier diferencia static → WordPress se compara con producción publicada y se registra
en `migracion-static-wordpress.md`.

## Referencias

- ADR [0034](adr/0034-static-live-como-fuente-contenido-produccion.md)
- ADR [0040](adr/0040-retirar-content-source-produccion-como-fuente.md)
- `docs/backlog-decisiones-owner-migracion.md` (OWN-006, OWN-007, OWN-017)
