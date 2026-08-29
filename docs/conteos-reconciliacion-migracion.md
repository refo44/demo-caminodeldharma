# Conteos de reconciliación static → WordPress

Baseline 2026-08-19, repo `VERSION` 1.0.34; actualizado 2026-08-29 a 1.0.35 (`/privacidad`). ADR [0034](adr/0034-static-live-como-fuente-contenido-produccion.md).
Detalle: [`inventario-contenido-produccion-static.md`](inventario-contenido-produccion-static.md).

```text
A migration is NOT complete if counts do not reconcile
(unless every mismatch is explained in this table).
```

Re-ejecutar tras cada import y tras el freeze/delta. Extraer del **mismo commit** que el payload.

---

## Baseline estático (SOURCE)

| Colección | Conteo | Cómo se contó | Notas |
| --------- | ------ | ------------- | ----- |
| URLs en sitemap | 17 | `sitemap.xml` | sin 404; incluye `/privacidad` |
| HTML de páginas | 17 (+ `404.html`) | glob `**/index.html` + 404 | |
| Pages institucionales / secundarias (no CPT, no posts) | 11 | home, comunidad, linaje, practica, videos, meditacion, galeria, donaciones, contacto, blog (archivo), privacidad | `/eventos` es archive CPT, no Page |
| Eventos (cards) | **10** | `article.evento-card` en `eventos/index.html` | 1 vigente + 9 finalizados |
| Eventos con URL single | **3** | sitemap `/eventos/{slug}` | las otras 7 solo viven en el listado |
| Posts | **2** | `blog/{slug}` | |
| Álbumes galería | **3** | `#gallery-albums-data` | |
| Imágenes en JSON galería | **35** | `#gallery-data` | |
| Archivos `galeria/*.jpg` | **36** | disco | 1 no está en JSON (`galeria-04`) |
| Thumbs galería | **108** | disco | derivadas |
| Carteles eventos | **10** | `assets/images/eventos/` | |
| Audio mantras | **2** | `assets/audio/` | |
| PDF recitación | **0** (sitio) | RETIRE OWN-002 | excluido de la web; no es archivo del sitio |
| `.ics` | **2** en disco; destino WP: **0 attachments** (generados) | `eventos/ical/` | OWN-009; Encuentro RETIRE; Círculos KEEP URL mientras vigente (OWN-013) |
| Embeds videos page | **5** | 4 YouTube + 1 Vimeo | |
| Formulario | **1** | `/contacto` markup | envío no live |
| Fixtures | **0** | no hay marcador `_cdd_fixture` | |

---

## Objetivo WordPress (tras import, mismo commit)

| Colección | Esperado | Cómo verificar |
| --------- | -------- | -------------- |
| Pages (institucionales + secundarias + blog page si aplica) | 11 (±1 si front-page no es Page) | `wp post list --post_type=page --format=count` |
| CPT `event` | **10** | incluye las 7 sin single |
| `event` con permalink público | **10** | ADR 0035 / OWN-004; 3 KEEP + 7 PLANNED |
| `post` | **2** | meta `authors` asignado (ADR 0037) |
| CPT `blog_author` | **2** | Zheng Gong + Comunidad Camino del Dharma |
| Media de galería pública | **35** | no incluir ilustraciones ni huérfanas (OWN-001, OWN-003) |
| Media huérfana (oculta) | N = imágenes en disco no referenciadas | seed; **0** URLs públicas; listar en el payload |
| Álbumes / términos de galería | **3** | |
| Featured images de eventos | **10** | |
| Attachments audio | **2** | OWN-009: mantras, no `.ics` |
| PDF | **0** | OWN-002: no importar recitación |
| Fixtures públicos | **0** | |

Cualquier otra cifra exige fila en «Mismatches».

---

## Mismatches conocidos (pre-implementación)

| Static | WP si se importa «a ciegas» | Explicación | Acción |
| ------ | --------------------------- | ----------- | ------ |
| 36 JPG en `galeria/` vs 35 JSON | 35 en álbum + 1 media de `/practica` | `galeria-04` es ilustración de Práctica | **OWN-001:** no es ítem de galería |
| Imágenes en disco sin `<img>`/`src` | attachments ocultos | reserva editorial | **OWN-003:** seed; no se ven en el sitio |
| 10 events vs 3 URLs en el estático | 10 singles en WP | 7 URLs nuevas en el corte | **ADR 0035:** PLANNED KEEP; slugs fijos |
| 10 posters vs 10 events | OK si el mapeo de filenames es 1:1 | verificar al extraer | |
| Docs 16 mencionan PDF | 0 en WP | excluido a propósito | **OWN-002 RETIRE** — no mismatch |
| Repo más nuevo vs ZIP en Hostinger | conteos o HTML distintos | deploy atrasado | **OWN-006:** extraer el repo vigente (`VERSION`); el ZIP viejo no es la fuente |
| `content-source/` vs HTML | copy institucional distinto | live es el correcto | **OWN-007:** extraer HTML; no mismatch si el doc queda atrás |

---

**Versión:** 1.1
