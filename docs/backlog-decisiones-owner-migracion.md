# Backlog — decisiones del propietario (migración static → FSE)

Preguntas abiertas de la auditoría 2026-08-19 (ADR 0034). **No son ADR.**

Hasta que el propietario las cierre, el trabajo de documentación y (más adelante) de extracción
sigue con los **defaults** de la columna «Mientras tanto». Nada de esto bloquea guardar el
inventario ni construir FSE en staging.

---

## ADR vs backlog (este proyecto)

| | Backlog (este archivo) | ADR (`docs/adr/`) |
| --- | --- | --- |
| Qué es | Pregunta abierta o ítem UNCLEAR | Decisión ya tomada (o **Propuesta** si está en discusión arquitectónica activa) |
| Cuándo | Contenido puntual, media, timing, copy | Arquitectura, URLs públicas nuevas, modelo CMS, seguridad, corte |
| Estado | `Abierta` → `Decidida` (anotar aquí + actualizar inventario/conteos/ledger) | Aceptada / Rechazada / … |

**No crear un ADR por cada UNCLEAR.** Si más adelante una respuesta cambia arquitectura o URLs
(p. ej. publicar singles nuevos para los 7 eventos solo-listado), **entonces** se escribe un ADR.

El backlog de la auditoría 2026-07-19 (`.audit/implementation/backlog.md`, TASK-NNNN) es **otro**
listado. No mezclar.

Ya resuelto por ADR (no repetir aquí como pregunta):

- `/privacidad` aplazada, copy no inventado — ADR 0028
- CPT `sangha` fuera del corte inicial — ADR 0024
- Freeze por defecto: ledger durante el build + freeze corto al corte — ADR 0034
- Todo evento tiene single; pasados sin inscripción — ADR 0035 (OWN-004)

---

## Abiertas

| ID | Pregunta | Tipo | Mientras tanto (default) | ¿Podría volverse ADR? |
| -- | -------- | ---- | ------------------------ | --------------------- |
| OWN-007 | Divergencias `content-source/` vs HTML publicado (copy institucional), campo a campo | Editorial | UNCLEAR; no pisar el live en silencio. | No. Es revisión editorial, no arquitectura. |
| OWN-008 | Álbumes de galería (3): ¿taxonomía, padre/hijo u otro? | Modelo CMS | 3 colecciones reales; no hardcodear en patterns. Elegir al implementar el plugin. | **Sí**, si el modelo no queda cubierto por docs 03/16 al implementar. |
| OWN-009 | Audio y `.ics`: ¿Media Library o path de descarga? | Media | Imágenes ya decididas (seed → Media Library). Audio/`.ics` siguen abiertos. | No. |
| OWN-010 | Autores en cards (Comunidad / Zheng Gong): ¿seguir como copy o Users de WP? | Modelo CMS | Atribución en copy; no forzar CPT author ni `/author/`. | **Sí**, solo si se crean rutas `/author/` (entonces KEEP/301). |

## Decididas

| ID | Fecha | Decisión |
| -- | ----- | -------- |
| OWN-001 | 2026-08-28 | **Foto de página ≠ ítem de galería.** Una imagen usada como ilustración en otra página no entra en `/galeria`. `galeria-04.jpg` se importa como media de `/practica`, no al álbum. Conteos: 35 galería + 1 media de página. El preview del inicio (`galeria-01`–`03`) es teaser de la galería, no ilustración de otra sección: esas tres **sí** siguen en `#gallery-data`. |
| OWN-006 | 2026-08-28 | Extraer **lo más reciente del repo** (`VERSION` vigente, hoy 1.0.34), no el ZIP más viejo que aún esté en Hostinger. Indicar tag/commit en el payload. Si producción se atrasó, el delta es «desplegar o reconciliar», no extraer el artefacto viejo. |
| OWN-009-img | 2026-08-28 | **Imágenes → Media Library vía seed.** Todas las imágenes referenciadas (galería, carteles, páginas, hero, fundador, etc.) se suben como attachments. El seed es **contenido real** (ADR 0033): idempotente, create-missing-only, sin `_cdd_fixture`, **sin teardown**. Thumbs estáticos: WordPress regenera tamaños. No hardcodear fotos en `templates/` ni en el theme. |
| OWN-002 | 2026-08-28 | **RETIRE.** El PDF de recitación de la comida queda **excluido de la web**. No forma parte de los archivos del sitio. No enlazar, no importar, no seed, no URL. Docs 04/16/23 que lo mencionan son HISTORICAL. |
| OWN-003 | 2026-08-28 | **Huérfanas → seed oculto.** Toda imagen del repo no enlazada en HTML (p. ej. `celebracion-vesak-2019` y el resto no usado de `celebraciones/`) se sube a Media Library con el seed, **sin** mostrarse en el sitio: no álbum, no Page, no teaser. Quedan en la biblioteca por si se usan después. `celebracion-diwali.jpg` **sí** está en `/practica` → media de página (OWN-001), no huérfana. El PDF no es imagen (OWN-002). |
| OWN-004 | 2026-08-28 | **Todo evento tiene single.** Los 10 CPT tienen `/eventos/{slug}`. Los 7 que hoy no tienen ficha se publican en el corte (slugs en ADR 0035 / ledger). Eventos **pasados: sin Inscribirme / Preinscribirme**. Vigentes: inscripción solo si es real. |
| OWN-005 | 2026-08-29 | **C+A confirmado.** Producción = estático en `caminodeldharma.org`. WordPress se despliega en **otra instancia Hostinger, sin dominio custom**, hasta el switch. Esa instancia es **STAGING**: noindex; no pisa `public_html` del estático. Ledger durante el build; freeze corto al corte. Delta (B) solo si el freeze no es viable. |

Al cerrar una fila: fecha, decisión en una frase, y actualizar
`inventario-contenido-produccion-static.md`, `conteos-reconciliacion-migracion.md` y, si hay URL,
`redirect-ledger.md`.

---

**Versión:** 1.7 · **Fecha:** 2026-08-29 · **Estado:** 4 abiertas · 7 decididas (Fase 3 no iniciada)
