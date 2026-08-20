# Inventario de contenido de producción (sitio estático live)

Auditoría 2026-08-19 sobre el repo en `VERSION` **1.0.34**. No es un extracto ejecutado: es el
baseline documental. ADR [0034](adr/0034-static-live-como-fuente-contenido-produccion.md).

**Live actual:** `https://caminodeldharma.org` (Hostinger). **Raíz estática:** raíz del repo (no hay
`static/` todavía). **Deploy:** ZIP manual (ADR 0015). `CHANGELOG.md`: v1.0.34 puede estar
**pendiente** de ZIP; v1.0.33 figura desplegada. Extraer siempre indicando el tag/commit.

Clases: **REAL PRODUCTION** · **HISTORICAL** (sigue siendo producción) · **STRUCTURAL COPY** ·
**DESIGN / DEMO** · **OBSOLETE** · **UNCLEAR — OWNER REVIEW**.

---

## 1. URLs públicas (sitemap)

16 URLs en `sitemap.xml`. Todas **KEEP** salvo decisión nueva. Ver [`redirect-ledger.md`](redirect-ledger.md).

| URL | Archivo | Tipo | Clase |
| --- | ------- | ---- | ----- |
| `/` | `index.html` | Home | REAL PRODUCTION |
| `/comunidad` | `comunidad/index.html` | Page institucional | REAL PRODUCTION |
| `/linaje` | `linaje/index.html` | Page institucional | REAL PRODUCTION |
| `/practica` | `practica/index.html` | Page institucional + media | REAL PRODUCTION |
| `/practica/videos` | `practica/videos/index.html` | Page + embeds | REAL PRODUCTION |
| `/practica/meditacion-semanal-en-linea` | `practica/meditacion-semanal-en-linea/index.html` | Page secundaria | REAL PRODUCTION |
| `/galeria` | `galeria/index.html` | Page + JSON | REAL PRODUCTION |
| `/contacto` | `contacto/index.html` | Page + form no operativo | REAL PRODUCTION |
| `/donaciones` | `donaciones/index.html` | Page institucional | REAL PRODUCTION |
| `/eventos` | `eventos/index.html` | Listado + calendario | REAL PRODUCTION |
| `/eventos/circulos-de-presencia-consciente` | ficha | Event single | REAL PRODUCTION |
| `/eventos/encuentro-nacional-2026` | ficha | Event single | HISTORICAL (publicado) |
| `/eventos/pausa-profunda-cali` | ficha | Event single | HISTORICAL (publicado) |
| `/blog` | `blog/index.html` | Archivo posts | REAL PRODUCTION |
| `/blog/circulos-de-presencia-consciente` | ficha | Post | REAL PRODUCTION |
| `/blog/sangha-refugio-hiperconexion` | ficha | Post | REAL PRODUCTION |

`404.html` no es URL pública. `/privacidad` **no** está en HTML ni sitemap (ADR 0028). No inventar copy.

Búsqueda: **no existe** (doc 04). No crear.

---

## 2. Eventos (hardcodeados en HTML)

Fuente: `eventos/index.html` (`article.evento-card`). **10 entidades.** SoT = este HTML.

| # | Título en listado | Single | Estado UI | Poster (disco) | Clase | WP previsto |
| - | ----------------- | ------ | --------- | -------------- | ----- | ----------- |
| 1 | Círculos de Presencia Consciente | sí `/eventos/circulos-de-presencia-consciente` | vigente + featured | `evento-circulos-de-presencia-consciente.jpg` | REAL PRODUCTION | CPT `event` |
| 2 | 7.º Encuentro Nacional Buddhista – 2026 | sí `/eventos/encuentro-nacional-2026` | finalizado | `evento-7-encuentro-nacional-2026.jpg` | HISTORICAL | CPT `event` |
| 3 | Meditación Presencial en Barranquilla | **no** (solo card) | finalizado | `evento-meditacion-presencial-barranquilla-jul-2026.jpeg` | HISTORICAL | CPT `event` (listado; sin single a menos que el owner pida URL) |
| 4 | Meditación Budista – Festival Calma en la Ciudad | no | finalizado | `evento-festival-calma-en-la-ciudad-jun-2026.jpeg` | HISTORICAL | CPT `event` |
| 5 | Pausa Profunda – Medellín | no | finalizado | `evento-taller-pausa-profunda-medellin-may-2026.jpeg` | HISTORICAL | CPT `event` |
| 6 | Ansiedad, agotamiento y crisis de atención… | no | finalizado | `evento-uniremington-ansiedad-agotamiento-may-2026.jpeg` | HISTORICAL | CPT `event` |
| 7 | Vesak 2026 – Colombia Cuida la Vida | no | finalizado | `evento-vesak-2026-bogota.jpeg` | HISTORICAL | CPT `event` |
| 8 | Pausa Profunda – Cali | sí `/eventos/pausa-profunda-cali` | finalizado | `evento-taller-pausa-profunda-feb-2026.jpeg` | HISTORICAL | CPT `event` |
| 9 | Buddhismo para tiempos de cansancio | no | finalizado | `evento-buddhismo-tiempos-cansancio.jpeg` | HISTORICAL | CPT `event` |
| 10 | 6.º Encuentro Nacional Buddhista – 2025 | no | finalizado | `evento-6-encuentro-nacional.jpeg` | HISTORICAL | CPT `event` |

Home: nota de **un** evento vigente (Círculos) en `index.html`. No es una entidad extra: es la misma
#1 (`event_featured`). Extraer una sola vez.

Calendario de septiembre 2026: celdas hardcodeadas que apuntan al #1 y a meditación semanal.
La meditación semanal **no** es un `event` (`docs/03`). STRUCTURAL + Page `/practica/meditacion-semanal-en-linea`.

`.ics`: 2 archivos (`circulos…`, `encuentro-nacional-2026`). Pausa Cali **sin** `.ics` en repo.

---

## 3. Blog

| Slug | Título visible (home/listado) | Autor en card home | Clase | WP |
| ---- | ----------------------------- | ------------------ | ----- | -- |
| `circulos-de-presencia-consciente` | Círculos de Presencia Consciente | Comunidad Camino del Dharma | REAL PRODUCTION | `post` |
| `sangha-refugio-hiperconexion` | Estamos conectados, pero seguimos solos | Zheng Gong | REAL PRODUCTION | `post` |

Autores: **atribución en copy**, no hay CPT author ni archivo `/author/`. No forzar CPT author.
Tags: no hay en el estático; ADR 0031 aplica en WordPress cuando existan.

---

## 4. Galería

- `#gallery-data`: **35** objetos `{src, alt}` (salta `galeria-04.jpg` en el JSON).
- `#gallery-albums-data`: **3** álbumes (General 0–25, 2023 25–30, 2021 30–35).
- Disco: **36** archivos en `assets/images/galeria/` + **108** thumbs.

| Ítem | Clase | WP |
| ---- | ----- | -- |
| 35 imágenes del JSON + alt | REAL PRODUCTION | Media Library + relación de galería |
| 3 álbumes | REAL PRODUCTION | taxonomía o parent/galería editorial |
| `galeria-04.jpg` en disco, ausente del JSON | **UNCLEAR** | no borrar; owner decide KEEP hidden vs incluir |
| thumbs | GENERATED/derivados | regenerar o importar según estrategia media |

`gallery.js` es comportamiento; en WP se sustituye (ADR 0021). Los **datos** JSON sí se migran.

---

## 5. Páginas institucionales y copy estructural

| Recurso | SoT preferida | Publicado en | Clase |
| ------- | ------------- | ------------ | ----- |
| Comunidad, linaje, práctica (narrativa) | `content-source/` | HTML live | REAL PRODUCTION |
| Home hero, caminos, cómo practicamos | `content-source/` + HTML | `index.html` | REAL PRODUCTION + STRUCTURAL COPY |
| Datos bancarios footer | `content-source/` | todas las páginas | REAL PRODUCTION |
| Nav / skip link / labels | docs 09 | HTML | STRUCTURAL COPY |
| Language switcher (solo UI, sitio en español) | `main.js` | header | STRUCTURAL COPY (no i18n real) |
| Formulario contacto | markup live; envío no opera | `/contacto` | REAL PRODUCTION (campos); backend = ADR 0026 |

Si `content-source/` y HTML divergen: **UNCLEAR**, no pisar el live en silencio.

---

## 6. Media (producción)

| Ubicación | Conteo aprox. (disco) | Uso | Estrategia por defecto |
| --------- | --------------------- | --- | ---------------------- |
| `assets/images/galeria/` | 36 | galería | IMPORT TO MEDIA LIBRARY (JSON manda el conjunto público) |
| `assets/images/galeria/thumbs/` | 108 | derivadas | REGENERATE o IMPORT; no 404 |
| `assets/images/eventos/` | 10 | carteles | IMPORT; featured image del CPT |
| `assets/images/blog/` | 2 | thumbs posts | IMPORT |
| `assets/images/inicio/` | 14 | hero, ambiente, preview galería | IMPORT o KEEP path theme según uso |
| `assets/images/comunidad-linaje/` | 8 | páginas | IMPORT |
| `assets/images/fundador/` | 1 | comunidad | IMPORT |
| `assets/images/contacto/` | 1 | contacto | IMPORT |
| `assets/images/practica/` | 1 | meditación | IMPORT |
| `assets/images/celebraciones/` | 3 | posible uso puntual | **UNCLEAR** si no están enlazadas |
| `assets/audio/` | 2 mp3 | mantras en `/practica` | IMPORT o KEEP download path |
| `assets/documents/recitacion-practica-comida.pdf` | 1 | documentado en doc 16; **no** hay `<a>` al PDF en `practica/index.html` actual | **UNCLEAR** — no borrar |
| `eventos/ical/*.ics` | 2 | descargas calendario | KEEP path o attachment |
| favicon / OG default | varios | SEO | KEEP o Site Icon |

Por cada archivo: **KEEP LEGACY PATH** / **IMPORT TO MEDIA LIBRARY** / **REPLACE** / **RETIRE**.
Default: IMPORT lo referenciado; RETIRE solo con evidencia; UNCLEAR no se borra.

---

## 7. Embeds (no son attachments locales)

| Dónde | Qué | Clase | WP |
| ----- | --- | ----- | -- |
| `/practica/videos` | 4 YouTube nocookie + 1 Vimeo dnt | REAL PRODUCTION | embed en Page o bloque; URLs exactas |
| `/practica` e Inicio | subconjunto de los mismos | REAL PRODUCTION | no duplicar entidades; reutilizar |

---

## 8. SEO / ops estáticas

Presentes en páginas HTML: `<title>`, description, canonical (sin barra), OG, JSON-LD.
`robots.txt` → sitemap manual. `llms.txt`. `.htaccess` (HTTPS, host, legacy 301/410).

Tras corte: sitemap nativo (ADR 0030); portar redirects (ledger).

**Analytics:** no hay cookies de analítica (ADR 0019). No «preservar GA4».

---

## 9. JS / layouts especiales (presentación, no entidades extra)

| Vista | Extra vs `page.html` genérico |
| ----- | --------------------------- |
| Todas | `main.js` menú + lang UI |
| `/eventos` | calendario, `calendar.js`, `share.js`, cards vigentes/archivo |
| singles evento/blog | `share.js`; vigentes también `calendar.js` |
| `/galeria` | JSON + `gallery.js` (datos sí; JS no se porta, ADR 0021) |
| `/contacto` | form |
| `/practica` | `<audio>`, ruby de mantras, iframes |

Contrato: static DOM → FSE template/parts/patterns/blocks → JS. Una Page 200 sin este comportamiento **no** está migrada (ADR 0032).

---

## 10. Navegación (no cutover con destinos rotos)

**Header (home y patrón global):** Inicio, Comunidad, Linaje, Práctica, Eventos, Galería, Blog, Contribuir, Contacto.

**Home CTAs / cards:** comunidad, contacto, evento destacado, WhatsApp meditación, página meditación, 2 posts, blog, videos, galería.

**Footer:** contacto, Facebook, Instagram, mailto, WhatsApp, donaciones, créditos. **Sin** enlace `/privacidad` en el HTML actual (docs lo prevén pendiente).

**Eventos:** título/cartel/«Ver evento» hacia singles cuando existen; anclas `#id` en la misma página; lunes → meditación.

Cada destino interno debe estar en la matriz o el redirect ledger. **NO CUTOVER WITH BROKEN NAVIGATION.**

---

## 11. Qué no hay (no inventar)

- Buscador, área privada, CPT `sangha` en el estático, tags, archivo de autor, page builder, fixtures públicos.

---

**Versión inventario:** 1.0 · **Repo:** 1.0.34 · **Fecha:** 2026-08-19
