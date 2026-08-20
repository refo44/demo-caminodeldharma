# Conteos de reconciliación static → WordPress

Baseline 2026-08-19, repo `VERSION` 1.0.34. ADR [0034](adr/0034-static-live-como-fuente-contenido-produccion.md).
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
| URLs en sitemap | 16 | `sitemap.xml` | sin 404 |
| HTML de páginas | 16 (+ `404.html`) | glob `**/index.html` + 404 | |
| Pages institucionales / secundarias (no CPT, no posts) | 10 | home, comunidad, linaje, practica, videos, meditacion, galeria, donaciones, contacto, blog (archivo) | `/eventos` es archive CPT, no Page |
| Eventos (cards) | **10** | `article.evento-card` en `eventos/index.html` | 1 vigente + 9 finalizados |
| Eventos con URL single | **3** | sitemap `/eventos/{slug}` | las otras 7 solo viven en el listado |
| Posts | **2** | `blog/{slug}` | |
| Álbumes galería | **3** | `#gallery-albums-data` | |
| Imágenes en JSON galería | **35** | `#gallery-data` | |
| Archivos `galeria/*.jpg` | **36** | disco | 1 no está en JSON (`galeria-04`) |
| Thumbs galería | **108** | disco | derivadas |
| Carteles eventos | **10** | `assets/images/eventos/` | |
| Audio mantras | **2** | `assets/audio/` | |
| PDF recitación | **1** en disco | `assets/documents/` | no enlazado en HTML actual |
| `.ics` | **2** | `eventos/ical/` | |
| Embeds videos page | **5** | 4 YouTube + 1 Vimeo | |
| Formulario | **1** | `/contacto` markup | envío no live |
| Fixtures | **0** | no hay marcador `_cdd_fixture` | |

---

## Objetivo WordPress (tras import, mismo commit)

| Colección | Esperado | Cómo verificar |
| --------- | -------- | -------------- |
| Pages (institucionales + secundarias + blog page si aplica) | 10 (±1 si front-page no es Page) | `wp post list --post_type=page --format=count` |
| CPT `event` | **10** | incluye las 7 sin single |
| `event` con permalink público extra | **3** (o 10 si se publican todos los singles) | mismatch permitido **solo** si el ledger dice «7 listing-only» |
| `post` | **2** | |
| Media de galería pública | **35** (o 36 si el owner incluye `galeria-04`) | |
| Álbumes / términos de galería | **3** | |
| Featured images de eventos | **10** | |
| Attachments audio | **2** | |
| PDF | 0 o 1 según UNCLEAR | |
| Fixtures públicos | **0** | |

Cualquier otra cifra exige fila en «Mismatches».

---

## Mismatches conocidos (pre-implementación)

| Static | WP si se importa «a ciegas» | Explicación | Acción |
| ------ | --------------------------- | ----------- | ------ |
| 36 JPG galería vs 35 JSON | 36 media vs 35 en galería | `galeria-04` no publicado | UNCLEAR — owner |
| 10 events vs 3 URLs | 10 CPT vs 3 visibles en permalink | listado-only es válido (doc 03) | importar 10; no inventar slugs |
| 10 posters vs 10 events | OK si el mapeo de filenames es 1:1 | verificar al extraer | |
| PDF en disco, 0 enlaces HTML | 1 attachment huérfano o 0 | posible retirada silenciosa | UNCLEAR — no borrar disco |
| Repo 1.0.34 vs prod 1.0.33 | conteos distintos | CHANGELOG | extraer el artefacto **desplegado** o freeze+delta |
| `content-source/` vs HTML | copy institucional distinto | dos SoT | UNCLEAR por campo |

---

**Versión:** 1.0
