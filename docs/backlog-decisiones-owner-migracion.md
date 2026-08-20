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
- 7 eventos sin single: importar como CPT; **no inventar slugs** salvo decisión nueva — inventario + doc 03

---

## Abiertas

| ID | Pregunta | Tipo | Mientras tanto (default) | ¿Podría volverse ADR? |
| -- | -------- | ---- | ------------------------ | --------------------- |
| OWN-001 | `galeria-04.jpg` (disco, no está en `#gallery-data`): ¿incluir en la galería pública o dejar oculta? | Media | No borrar. Conteos: 35 públicos / 36 en disco. Mismatch explicado. | No. Cierra en inventario + conteos. |
| OWN-002 | PDF `assets/documents/recitacion-practica-comida.pdf`: ¿enlazar, importar huérfano o retirar? | Media | No borrar disco. No inventar enlace. | No, salvo que se cree una URL pública nueva. |
| OWN-003 | `assets/images/celebraciones/`: ¿están en uso o se retiran? | Media | No borrar hasta verificar referencias. | No. |
| OWN-004 | Los 7 eventos solo-listado: ¿permanecen sin permalink o se publican singles? | URLs | Importar 10 CPT; **sin** slugs nuevos. KEEP las 3 URLs que ya existen. | **Sí**, solo si se deciden URLs públicas nuevas (KEEP/301). |
| OWN-005 | ¿Confirmar freeze C+A o usar delta continuo (B) durante Fase 3? | Operación | ADR 0034 ya eligió C+A como default. | Solo si se **cambia** esa default. |
| OWN-006 | Extraer el artefacto **desplegado** (p. ej. 1.0.33) o el repo actual (1.0.34) + delta? | Timing | Extraer indicando tag/commit. Reconciliar conteos del mismo commit. | No. |
| OWN-007 | Divergencias `content-source/` vs HTML publicado (copy institucional), campo a campo | Editorial | UNCLEAR; no pisar el live en silencio. | No. Es revisión editorial, no arquitectura. |
| OWN-008 | Álbumes de galería (3): ¿taxonomía, padre/hijo u otro? | Modelo CMS | 3 colecciones reales; no hardcodear en patterns. Elegir al implementar el plugin. | **Sí**, si el modelo no queda cubierto por docs 03/16 al implementar. |
| OWN-009 | Audio, `.ics`, imágenes de layout: ¿KEEP path legacy o Media Library? | Media | IMPORT lo referenciado; no 404. Decidir por tipo al extraer. | No. Matriz + inventario. |
| OWN-010 | Autores en cards (Comunidad / Zheng Gong): ¿seguir como copy o Users de WP? | Modelo CMS | Atribución en copy; no forzar CPT author ni `/author/`. | **Sí**, solo si se crean rutas `/author/` (entonces KEEP/301). |

Al cerrar una fila: fecha, decisión en una frase, y actualizar
`inventario-contenido-produccion-static.md`, `conteos-reconciliacion-migracion.md` y, si hay URL,
`redirect-ledger.md`.

---

**Versión:** 1.0 · **Fecha:** 2026-08-19 · **Estado:** abierto (Fase 3 no iniciada)
