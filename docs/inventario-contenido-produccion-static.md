# Inventario de contenido de producción (sitio estático live)

Auditoría 2026-08-19 sobre el repo en `VERSION` **1.0.34**, actualizado a **1.0.35** (`/privacidad`,
ADR 0039). No es un extracto ejecutado: es el baseline documental. ADR
[0034](adr/0034-static-live-como-fuente-contenido-produccion.md) y
[0040](adr/0040-retirar-content-source-produccion-como-fuente.md).

**Live actual:** `https://caminodeldharma.org` (Hostinger). **Raíz estática en el repo:** `static/`
(monorepo ADR 0014). **Deploy:** ZIP manual del contenido de `static/` (ADR 0015). Extraer **lo más
reciente del repo** (`VERSION` vigente; OWN-006, 2026-08-28), indicando tag/commit. Si Hostinger tiene un ZIP anterior,
eso es deuda de deploy, no la fuente del extracto.

Clases: **REAL PRODUCTION** · **HISTORICAL** (sigue siendo producción) · **STRUCTURAL COPY** ·
**DESIGN / DEMO** · **OBSOLETE** · **UNCLEAR — OWNER REVIEW**.

Ítems UNCLEAR de esa auditoría: **cerrados** (backlog Fase 3, 0 abiertas). El archivo
[`backlog-decisiones-owner-migracion.md`](backlog-decisiones-owner-migracion.md) (v1.28) queda
como registro de decisiones (no ADR). OWN-020 / D-08 está **decidido**, implementación
pendiente ([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)). OWN-021 / D-09:
overflow Sangha dejado al corte. OWN-022 / D-10: `wp-emoji` `sessionStorage` aceptado.
Pre-staging: D-02/D-03/D-04 ([#10](https://github.com/refo44/demo-caminodeldharma/issues/10)–[#12](https://github.com/refo44/demo-caminodeldharma/issues/12))
antes de Hostinger (OWN-035). `POST-008`–`010` son posteriores al corte.

---

## 1. URLs públicas (sitemap)

17 URLs en `sitemap.xml`. Todas **KEEP** salvo decisión nueva. Ver [`redirect-ledger.md`](redirect-ledger.md).

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
| `/privacidad` | `privacidad/index.html` | Page institucional (aviso provisional) | REAL PRODUCTION |
| `/eventos` | `eventos/index.html` | Listado + calendario | REAL PRODUCTION |
| `/eventos/circulos-de-presencia-consciente` | ficha | Event single | REAL PRODUCTION |
| `/eventos/encuentro-nacional-2026` | ficha | Event single | HISTORICAL (publicado) |
| `/eventos/pausa-profunda-cali` | ficha | Event single | HISTORICAL (publicado) |
| `/blog` | `blog/index.html` | Archivo posts | REAL PRODUCTION |
| `/blog/circulos-de-presencia-consciente` | ficha | Post | REAL PRODUCTION |
| `/blog/sangha-refugio-hiperconexion` | ficha | Post | REAL PRODUCTION |

`404.html` no es URL pública. `/privacidad` está publicada (ADR 0039, provisional). El importer trae el HTML live. En WordPress, WU-09 aplica el delta del formulario (ADR 0041 / OWN-018); no reescribir el resto. La revisión legal no bloquea el corte.

Búsqueda: **no existe** (doc 04). No crear.

---

## 2. Eventos (hardcodeados en HTML)

Fuente: `eventos/index.html` (`article.evento-card`). **10 entidades.** SoT = este HTML.

| # | Título en listado | Single | Estado UI | Poster (disco) | Clase | WP previsto |
| - | ----------------- | ------ | --------- | -------------- | ----- | ----------- |
| 1 | Círculos de Presencia Consciente | sí `/eventos/circulos-de-presencia-consciente` | vigente + featured | `evento-circulos-de-presencia-consciente.jpg` | REAL PRODUCTION | CPT `event`; inscripción solo si sigue vigente |
| 2 | 7.º Encuentro Nacional Buddhista – 2026 | sí `/eventos/encuentro-nacional-2026` | finalizado | `evento-7-encuentro-nacional-2026.jpg` | HISTORICAL | CPT `event`; **sin** inscripción |
| 3 | Meditación Presencial en Barranquilla | **PLANNED** `/eventos/meditacion-presencial-barranquilla` | finalizado | `evento-meditacion-presencial-barranquilla-jul-2026.jpeg` | HISTORICAL | CPT `event` + single (ADR 0035); **sin** inscripción |
| 4 | Meditación Budista – Festival Calma en la Ciudad | PLANNED `/eventos/festival-calma-en-la-ciudad` | finalizado | `evento-festival-calma-en-la-ciudad-jun-2026.jpeg` | HISTORICAL | CPT + single; sin inscripción |
| 5 | Pausa Profunda – Medellín | PLANNED `/eventos/pausa-profunda-medellin` | finalizado | `evento-taller-pausa-profunda-medellin-may-2026.jpeg` | HISTORICAL | CPT + single; sin inscripción |
| 6 | Ansiedad, agotamiento y crisis de atención… | PLANNED `/eventos/ansiedad-agotamiento-crisis-de-atencion` | finalizado | `evento-uniremington-ansiedad-agotamiento-may-2026.jpeg` | HISTORICAL | CPT + single; sin inscripción |
| 7 | Vesak 2026 – Colombia Cuida la Vida | PLANNED `/eventos/vesak-2026` | finalizado | `evento-vesak-2026-bogota.jpeg` | HISTORICAL | CPT + single; sin inscripción |
| 8 | Pausa Profunda – Cali | sí `/eventos/pausa-profunda-cali` | finalizado | `evento-taller-pausa-profunda-feb-2026.jpeg` | HISTORICAL | CPT `event`; sin inscripción |
| 9 | Buddhismo para tiempos de cansancio | PLANNED `/eventos/buddhismo-tiempos-cansancio` | finalizado | `evento-buddhismo-tiempos-cansancio.jpeg` | HISTORICAL | CPT + single; sin inscripción |
| 10 | 6.º Encuentro Nacional Buddhista – 2025 | PLANNED `/eventos/6-encuentro-nacional-2025` | finalizado | `evento-6-encuentro-nacional.jpeg` | HISTORICAL | CPT + single; sin inscripción |

Home: nota de **un** evento vigente (Círculos) en `index.html`. No es una entidad extra: es la
misma fila `#1` (`event_featured`). Extraer una sola vez.

Calendario de septiembre 2026: celdas hardcodeadas que apuntan al #1 y a meditación semanal.
La meditación semanal **no** es un `event` (`docs/03`). STRUCTURAL + Page `/practica/meditacion-semanal-en-linea`.

`.ics`: 2 archivos en disco. Destino WP: **generados**, no Media Library (OWN-009). Encuentro 2026 **RETIRE** (OWN-012). Círculos KEEP hasta que `hoy >` su fecha de fin; entonces 410 y se borra el huérfano (OWN-013). Pausa Cali **sin** `.ics`.

---

## 3. Blog

| Slug | Título visible (home/listado) | Autor en card home | Clase | WP |
| ---- | ----------------------------- | ------------------ | ----- | -- |
| `circulos-de-presencia-consciente` | Círculos de Presencia Consciente | Comunidad Camino del Dharma | REAL PRODUCTION | `post` |
| `sangha-refugio-hiperconexion` | Estamos conectados, pero seguimos solos | Zheng Gong | REAL PRODUCTION | `post` |

Autores en el **estático:** copy (no hay `/author/`). **Destino WP (ADR 0037):** CPT
`blog_author`; semilla Zheng Gong + Comunidad Camino del Dharma; las 2 entradas se asignan
por meta. No Users, no copy hardcodeado. **OWN-020 / D-08 (pendiente
[#5](https://github.com/refo44/demo-caminodeldharma/issues/5)):** bio corta, `seo` y foto de
cada ficha salen de copy y assets ya publicados (JSON-LD del fundador; meta / «Quiénes somos»
de `/comunidad`); no se inventa el ensayo largo.
Tags: no hay en el estático; ADR 0031 aplica en WordPress cuando existan.

---

## 4. Galería

- `#gallery-data`: **35** objetos `{src, alt}` (salta `galeria-04.jpg` en el JSON).
- `#gallery-albums-data`: **3** álbumes (General 0–25, 2023 25–30, 2021 30–35).
- Disco: **36** archivos en `assets/images/galeria/` + **108** thumbs.

| Ítem | Clase | WP |
| ---- | ----- | -- |
| 35 imágenes del JSON + alt | REAL PRODUCTION | Media Library + relación de galería |
| 3 álbumes | REAL PRODUCTION | Mismos grupos en `/galeria`. URLs `/galeria/general`, `/2023`, `/2021` (ADR 0036); **noindex** hasta volumen. Taxonomía, no Page hija ni CPT. Destino FSE: **sin** paginación de 12 (OWN-011). |
| `galeria-04.jpg` (ilustración de `/practica`) | REAL PRODUCTION (página, **no** galería) | Media de la Page Práctica. **OWN-001:** no añadir al álbum. |
| thumbs | GENERATED/derivados | regenerar o importar según estrategia media |

Regla (OWN-001, 2026-08-28): ilustración de otra página ≠ ítem de `/galeria`. El preview del inicio
de `galeria-01`–`03` es teaser de la propia galería; esas tres sí se importan al álbum.

`gallery.js` es comportamiento; en WP se sustituye (ADR 0021). Los **datos** JSON sí se migran.

---

## 5. Páginas institucionales y copy estructural

| Recurso | SoT preferida | Publicado en | Clase |
| ------- | ------------- | ------------ | ----- |
| Comunidad, linaje, práctica (narrativa) | **HTML live** (OWN-007) | HTML live | REAL PRODUCTION |
| Home hero, caminos, cómo practicamos | **HTML live** | `index.html` | REAL PRODUCTION + STRUCTURAL COPY |
| Datos bancarios footer | **HTML live** | todas las páginas | REAL PRODUCTION |
| Nav / skip link / labels | docs 09 | HTML | STRUCTURAL COPY |
| Language switcher (solo UI, sitio en español) | `main.js` | header | STRUCTURAL COPY (no i18n real; `POST-001`–`POST-004`) |
| Formulario contacto | markup live; envío no opera | `/contacto` | REAL PRODUCTION (campos); backend = ADR 0026 |

Si un material legacy externo y el HTML divergen, **gana el HTML live** (OWN-007/017). No restaurar
fuentes retiradas sobre producción.

---

## 6. Media (producción)

| Ubicación | Conteo aprox. (disco) | Uso | Estrategia por defecto |
| --------- | --------------------- | --- | ---------------------- |
| `assets/images/galeria/` | 36 | galería + 1 ilustración `/practica` | **Seed → Media Library.** 35 al álbum; `galeria-04` a la Page (OWN-001) |
| `assets/images/galeria/thumbs/` | 108 | derivadas | **REGENERAR** en WP; no importar `thumbs/` |
| `assets/images/eventos/` | 10 | carteles | Seed → Media Library; featured del CPT |
| `assets/images/blog/` | 2 | thumbs posts | Seed → Media Library |
| `assets/images/inicio/` | 14 | hero, ambiente | Seed → Media Library (no theme path) |
| `assets/images/comunidad-linaje/` | 8 | páginas | Seed → Media Library |
| `assets/images/fundador/` | 1 | comunidad | Seed → Media Library |
| `assets/images/contacto/` | 1 | contacto | Seed → Media Library |
| `assets/images/practica/` | 1 | meditación | Seed → Media Library |
| `assets/images/celebraciones/` | ~3 | 1 en `/practica` (`diwali`); resto huérfano | **OWN-003:** usadas = media de Page; huérfanas = seed **oculto** (no se ven en el sitio) |
| `assets/audio/` | 2 mp3 | mantras en `/practica` | IMPORT o KEEP download path |
| `assets/documents/recitacion-practica-comida.pdf` | — | **RETIRE (OWN-002).** Excluido de la web; no es archivo del sitio. | No importar; no enlace; no URL |
| `eventos/ical/*.ics` | 2 en disco | Círculos generado mientras vigente; Encuentro RETIRE | OWN-009 + OWN-013: no seed a Media Library |
| favicon / OG default | varios | SEO | KEEP o Site Icon |

**Imágenes (OWN-009-img + OWN-003, 2026-08-28):** seed → Media Library (contenido real, no fixture).
Referenciadas: visibles según uso (álbum, Page, featured). **Huérfanas:** mismo seed, **ocultas**
(no álbum, no Page, no teaser). Thumbs: regenerar. Audio → Media Library (OWN-009). `.ics` generado, no biblioteca; pasados 410 + borrar huérfano (OWN-012, OWN-013). **PDF (OWN-002):** RETIRE.

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

**Footer:** contacto, Facebook, Instagram, mailto, WhatsApp, donaciones, créditos, **Privacidad** (`/privacidad`, ADR 0039).

**Eventos:** título/cartel/«Ver evento» hacia singles cuando existen; anclas `#id` en la misma página; lunes → meditación.

Cada destino interno debe estar en la matriz o el redirect ledger. **NO CUTOVER WITH BROKEN NAVIGATION.**

---

## 11. Qué no hay (no inventar)

- Buscador, área privada, CPT `sangha` en el estático, tags, archivo de **usuario** WP, page builder, fixtures públicos. (Perfil CPT `/author/{slug}` es destino WP, ADR 0037; no está en el estático.)

---

**Versión inventario:** 1.2 · **Repo:** 1.0.35 · **Fecha:** 2026-09-01
