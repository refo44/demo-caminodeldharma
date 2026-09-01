# Camino del Dharma — Árbol de URLs

**Geografía oficial del sitio**

Define todas las rutas del sitio. Traducción directa de 03 (modelo de contenido), 04 (mapa de pantallas) y 05 (arquitectura de navegación). **Si una URL no está aquí, no existe.**

**Depende de:** `03-wordpress-content-model`, `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`. **Referencia:** `12-theme-file-structure`

---

## 1. Regla base

Idioma: español (Colombia). Sin prefijo de idioma por defecto. Si se añade multilingualismo más adelante, usar `/es/` como prefijo. Confirmar con `POST-003` en `backlog-decisiones-owner-migracion.md` (fases posteriores; no forma parte del corte).

---

## 2. Páginas fijas

| Función | Slug |
|---------|------|
| Inicio | `/` |
| La comunidad | `/comunidad/` |
| El linaje | `/linaje/` |
| Práctica y actividades | `/practica/` |
| Eventos especiales | `/eventos/` *(la ruta existe siempre; contenido: listado o mensaje amable; ítem en menú condicional)* |
| Galería | `/galeria/` |
| Contribuir (donaciones) | `/donaciones/` |
| Contacto | `/contacto/` |
| Blog | `/blog/` |
| Privacidad | `/privacidad/` *(publicada; aviso provisional — ADR 0039)* |

---

## 3. Eventos (CPT)

| Tipo | URL |
|------|-----|
| Listado | `/eventos/` |
| Single | `/eventos/{slug}/` |

El listado enlaza cada evento con ficha propia hacia `/eventos/{slug}/` (título, cartel y «Ver evento →»). No todas las tarjetas tienen single: las que no, viven solo en el listado.

**Sin archivos por ciudad.** La ciudad de cada evento es **taxonomía (`event_city`), no dirección**: no existe `/eventos/cali` ni `/eventos/ciudad/cali`. Los encuentros de una ciudad se muestran dentro de `/sanghas/{ciudad}`; si el listado general crece, se archiva **por año** (`03-wordpress-content-model` §3). Mismo criterio que `event_type`, que también es taxonomía sin archivo público. Ver **ADR 0022**.

### 3.1. Sanghas (si se implementa CPT)

| Tipo | URL |
|------|-----|
| Listado | `/sanghas/` |
| Single | `/sanghas/{slug}/` |

### 3.2. Blog — tags (`post_tag`)

| Tipo | URL |
|------|-----|
| Archivo de tag | `/blog/tag/{slug}/` |

Esta URL **sí existe** (no es 404), a diferencia de `event_city`/`event_type` que no tienen archivo.
Se sirve `noindex, follow` por defecto hasta que el tag tenga volumen suficiente de contenido — ver
**ADR 0031** y `docs/03-wordpress-content-model.md` §4.

Implementado en WU-08B: el importador fija `tag_base` = `blog/tag` (sin ese ajuste WordPress
publicaría `/tag/{slug}`, fuera de este árbol) y la plantilla `templates/archive.html` da al
archivo su propio `h1` (docs/19 §9).

---

## 4. Árbol completo

```
/
/comunidad/
/linaje/
/practica/
/practica/videos/          (secundaria: no en navbar; acceso desde Práctica «Ver más videos»)
/practica/meditacion-semanal-en-linea/   (secundaria: no en navbar; enlazada desde Inicio y Práctica)
/eventos/
/eventos/{slug}/
/galeria/
/galeria/{slug}/            (álbum; existe; noindex hasta volumen — ADR 0036)
/donaciones/
/contacto/
/blog/
/blog/{slug}/
/blog/tag/{slug}/          (existe; noindex hasta volumen suficiente — ADR 0031)
/author/{slug}/            (ficha CPT blog_author; indexable — ADR 0037)
/author/                   (archivo de fichas; noindex hasta volumen — ADR 0037)
/privacidad/               (publicada; enlace en el pie de todas las páginas; ADR 0039)
```
*(Si se implementa CPT sangha: `/sanghas/`, `/sanghas/{slug}/`.)*

---

## 5. Estados sin URL propia

| Estado | Dónde ocurre |
|--------|--------------|
| Sin eventos vigentes | En `/eventos/` se muestra mensaje amable; el ítem Eventos en el menú puede ocultarse. |
| 404 | Cualquier URL fuera del árbol. No existe ruta pública `/404/`; WordPress sirve la plantilla `404.php` para rutas no definidas aquí. Referencia interna de diseño: estado 404, no URL del árbol. |

---

## 6. URL → plantilla

Plantillas de bloques (`templates/*.html`), no PHP — theme de bloques / Full Site Editing, ADR 0029.
Ver `docs/12-theme-file-structure.md` §5–§6 para el árbol completo.

| Ruta | Plantilla |
|------|-----------|
| `/` | `templates/front-page.html` |
| `/comunidad/` | `templates/page-comunidad.html` |
| `/linaje/` | `templates/page-linaje.html` |
| `/practica/` | `templates/page-practica.html` |
| `/eventos/` | `templates/page-eventos.html` o `templates/archive-event.html` |
| `/eventos/{slug}/` | `templates/single-event.html` |
| `/galeria/` | `templates/page-galeria.html` |
| `/galeria/{slug}/` | `taxonomy-gallery_album.html` o jerarquía nativa; **noindex** por defecto (ADR 0036) |
| `/contacto/` | `templates/page-contacto.html` |
| `/blog/` | `templates/home.html` (página de entradas) |
| `/blog/{slug}/` | `templates/single.html` |
| `/blog/tag/{slug}/` | resuelve por la jerarquía nativa de plantillas de WordPress (`templates/taxonomy-post_tag.html` si existe, si no `templates/archive.html`/`templates/index.html`); noindex por defecto (ADR 0031), no requiere plantilla propia |
| `/author/{slug}/` | `templates/single-blog_author.html`; **query_var ≠ `author`** (ADR 0037) |
| `/author/` | archivo CPT; **noindex** hasta volumen |
| `/privacidad/` | `templates/page.html` (fallback; no requiere plantilla propia) |

*(Si se implementa CPT sangha: `/sanghas/` → `templates/archive-sangha.html`; `/sanghas/{slug}/` →
`templates/single-sangha.html`.)*

---

## Cierre

Este documento es la **geografía oficial de rutas** del sitio. Si una URL no está aquí, no existe. Alineado con 03 (modelo de contenido), 04 (mapa de pantallas), 05 (navegación) y 12 (plantillas). Referencia técnica estable para la estructura de URLs en WordPress.

**Migración:** cada URL de este árbol tiene (o debe tener) una fila en
[`matriz-migracion-static-wordpress.md`](matriz-migracion-static-wordpress.md). Una plantilla en el
theme no publica la ruta: hace falta el objeto WordPress y rewrite (ADR 0032). Canonical pública:
**sin barra final** (ADR 0008). No hay buscador.

---

**Versión:** 1.7 — `/privacidad` publicada (ADR 0039). El listado `/eventos/` enlaza a `/eventos/{slug}/` cuando el evento tiene ficha. Matriz: `matriz-migracion-static-wordpress.md`.
