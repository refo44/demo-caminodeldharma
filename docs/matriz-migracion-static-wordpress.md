# Matriz de migración static → WordPress

Obligación fijada en [ADR 0032](adr/0032-contrato-migracion-static-wordpress.md).
Geografía: [11-arbol-urls-final.md](11-arbol-urls-final.md). Contrato:
[contrato-migracion-static-wordpress.md](contrato-migracion-static-wordpress.md).

**Regla:** una URL no se considera migrada hasta que su fila tenga estrategia para
**contenido + presentación + routing + comportamiento + QA**.

Una **entidad** hardcodeada (card de evento, ítem de galería) tampoco está migrada hasta tener fila
propia o estar contada en [`conteos-reconciliacion-migracion.md`](conteos-reconciliacion-migracion.md).
Inventario: [`inventario-contenido-produccion-static.md`](inventario-contenido-produccion-static.md) (ADR 0034).

Columnas ampliadas cuando el ítem no es una URL: Current URL, Static source, **Content item**, Current type, Future WP object, Future route, Template/FSE, Media, JS, Migration status, QA.

Las columnas WP (objeto, ruta, plantilla, import, QA) se completan al implementar Fase 3. Hoy
registran el inventario estático y el mapeo **previsto** según docs vigentes (ADR 0029, doc 12).
No es implementación.

Plantillas: **FSE** (`templates/*.html`) mapeadas **desde el HTML estático**, no desde un theme PHP clásico.

Política de URL pública: **sin barra final** (ADR 0008). En esta tabla se escribe la forma canónica.

---

## Cómo usar la matriz

| Columna | Significado |
| ------- | ----------- |
| Static URL | URL pública canónica actual |
| Static source | Archivo HTML en el repo (raíz, Fase 2) |
| Content source | HTML/JSON/media de producción que aporta copy o datos |
| WP object | Page / post / CPT / ajuste / N/A |
| WP route | Ruta WordPress prevista |
| Template | Archivo en `templates/` (doc 12) |
| JS | Scripts de la maqueta o sustitución documentada |
| Assets | Imágenes, audio, PDF, `.ics`, fuentes relevantes |
| Import strategy | Implementada en WU-06 (ADR 0033): payload `migration/payload.json` + `wp cdd-core migrate validate|plan|import|verify` y `seed` de medios; create-missing-only, dry-run por defecto |
| QA | Qué prueba cierra la fila |

Estados de fila (al implementar): `Inventario` → `En migración` → `Migrada` | `No aplica` | `Excepción ADR`.

---

## Páginas institucionales y home

| Static URL | Static source | Content source | WP object | WP route | Template | JS | Assets | Import strategy | QA |
|---|---|---|---|---|---|---|---|---|---|
| `/` | `index.html` | HTML live; nota de un evento vigente (datos de `/eventos`) | Ajuste lectura: página de inicio | `/` | `templates/front-page.html` | `main.js` (menú) | Hero WebP/JPEG, preview galería, OG default | Page + front-page settings; create-missing-only | HTTP 200; hero; nota de evento o ausencia; menú teclado; canonical |
| `/comunidad` | `comunidad/index.html` | HTML live (OWN-007) | Page `comunidad` | `/comunidad` | `templates/page-comunidad.html` | `main.js` | Foto fundador | Page; **añadir** enlaces a fichas autor en WP (OWN-016); no editar static ahora | 200; H1; foto; enlace blog; enlaces `/author/zheng-gong` y ficha Comunidad **solo en WP** |
| `/linaje` | `linaje/index.html` | HTML live | Page `linaje` | `/linaje` | `templates/page-linaje.html` | `main.js` | Imágenes Chan / Tierra Pura | Page institucional | 200; secciones tradición |
| `/practica` | `practica/index.html` | HTML live | Page `practica` | `/practica` | `templates/page-practica.html` | `main.js` | Audio mantras; **sin** PDF (OWN-002 RETIRE) | Page institucional | 200; audio; **no** enlace PDF; enlaces a videos y meditación |
| `/practica/videos` | `practica/videos/index.html` | Embeds del HTML live | Page `videos` (hija o slug acordado) | `/practica/videos` | `page-practica` o `page.html` si no hay plantilla propia | `main.js` | Embeds `youtube-nocookie.com` (ya en estático) | Page secundaria; **no** está en navbar | 200; embeds; no 404; slug anidado |
| `/practica/meditacion-semanal-en-linea` | `practica/meditacion-semanal-en-linea/index.html` | HTML live + horario | Page | `/practica/meditacion-semanal-en-linea` | `page.html` o plantilla específica si el diseño lo exige | `main.js` | — | Page secundaria (enlazada desde Inicio, Práctica y calendario) | 200; copy de Zoom/horario |
| `/galeria` | `galeria/index.html` | JSON live (OWN-007) | Page `galeria` | `/galeria` | `templates/page-galeria.html` | `gallery.js` no se migra (ADR 0021); **sin** paginación numerada (OWN-011) | 35 en álbumes | Page + seed; hub SEO (ADR 0036) | 200; General/2023/2021; lightbox; **Page no robada**; álbum entero (lazy-load) |
| `/galeria/2023` *(nueva)* | JSON álbum | término | `gallery_album` | `/galeria/2023` | taxonomía | — | 5 fotos | PLANNED; **noindex** | 200; noindex; mismas fotos que el grupo 2023 |
| `/galeria/2021` *(nueva)* | JSON álbum | término | `gallery_album` | `/galeria/2021` | taxonomía | — | 5 fotos | PLANNED; **noindex** | 200; noindex |
| `/galeria/general` *(nueva)* | JSON álbum | término | `gallery_album` | `/galeria/general` | taxonomía | — | 25 fotos | PLANNED; **noindex** al corte | 200; noindex |
| `/donaciones` | `donaciones/index.html` | HTML live (banco, NIT) | Page `donaciones` | `/donaciones` | `templates/page-donaciones.html` | `main.js` | — | Page institucional | 200; datos bancarios; no hardcode solo en theme |
| `/contacto` | `contacto/index.html` | HTML live + UI copy 09 | Page `contacto` | `/contacto` | `templates/page-contacto.html` | `main.js`; form static `action="#"` **no envía** | Imagen contacto | **WU-09 hecho:** el `<form action="#">` es el bloque `camino-del-dharma/contacto-formulario`, que rinde Contact Form 7 (definición y correo en el plugin) o, con CF7 apagado, los canales WhatsApp/correo | 200; DOM publicado verificado (labels, ids, `name`, `autocomplete`, iconos, botón); validación probada con datos sintéticos; **envío real solo en staging** |
| `/privacidad` | `privacidad/index.html` | HTML live (ADR 0039); aviso **provisional** | Page `privacidad` | `/privacidad` | `templates/page.html` | `main.js` | — | **WU-09 hecho:** delta field-scoped ADR 0041 aplicado solo en WordPress (recuadro, viñeta del resumen, §2.2, disparador de §8, fecha). Disclaimer provisional conservado; el resto del aviso sin tocar; el estático intacto | 200; enlace footer; `diff` de 4 hunks, ninguno fuera de alcance; §2.3–§2.6 idénticas |

`templates/page-*.html` **no** crea la Page. Ver ADR 0032 / 0033.

---

## Eventos (CPT `event`)

No crear Page con slug `eventos`.

| Static URL | Static source | Content source | WP object | WP route | Template | JS | Assets | Import strategy | QA |
|---|---|---|---|---|---|---|---|---|---|
| `/eventos` | `eventos/index.html` | Eventos vigentes + archivo en HTML; calendario del mes | Archive CPT `event` | `/eventos` | `templates/archive-event.html` | `main.js`, `share.js`, `calendar.js` (diálogo + tooltips grid) | Carteles; bloque calendario (datos desde `camino-del-dharma-core`) | Import CPT, no Page; calendario no es plugin de terceros | 200; vigentes vs finalizados; grid + hint táctil; JSON-LD Event; menú condicional |
| `/eventos/circulos-de-presencia-consciente` | `eventos/circulos-de-presencia-consciente/index.html` | Ficha + `eventos/ical/circulos-de-presencia-consciente.ics` (estático: un VEVENT de la bienvenida) | `event` single | mismo slug | `templates/single-event.html` | `main.js`, `share.js`, `calendar.js` | Cartel; `.ics` **generado con las 10 sesiones** (BUG-001) | CPT create-missing-only | 200; share; añadir calendario (enlace profundo = próxima sesión + nota); `.ics` 200 con 10 VEVENT y 10 UID únicos; JSON-LD |
| `/eventos/encuentro-nacional-2026` | `eventos/encuentro-nacional-2026/index.html` | Ficha; `.ics` en disco **RETIRE** (OWN-012) | `event` single | mismo slug | `templates/single-event.html` | `main.js`, `share.js` | Cartel | CPT | 200; share; **sin** calendario; **sin** `.ics`; JSON-LD `EventCompleted` |
| `/eventos/pausa-profunda-cali` | `eventos/pausa-profunda-cali/index.html` | Ficha (sin `.ics` en repo al auditar) | `event` single | mismo slug | `templates/single-event.html` | `main.js`, `share.js` | Cartel | CPT | 200; share |

Singles futuros: misma fila-patrón `/eventos/{slug}`. Sin archivos `/eventos/{ciudad}` (ADR 0022).

### Entidades en el listado (una fila por card — ADR 0034)

SoT = `eventos/index.html`. Las 7 sin URL propia **igual se importan** como CPT. No son demo.

| Current URL | Static source | Content item | Current type | Future WP object | Future route | Template/FSE | Media | JS | Migration status | QA |
|---|---|---|---|---|---|---|---|---|---|---|
| `/eventos` (card) | `eventos/index.html` #circulos… | Círculos de Presencia Consciente | card + single | CPT `event` | `/eventos/circulos-de-presencia-consciente` | `single-event.html` | cartel + `.ics` | share, calendar | Inventario | 1 objeto; featured home no duplica |
| `/eventos/encuentro-nacional-2026` | HTML ficha + card | 7.º Encuentro 2026 | card + single | CPT `event` | KEEP | `single-event.html` | cartel + `.ics` | share | Inventario | finalizado |
| *(nueva en WP)* | listado | Meditación Presencial Barranquilla | card → single | CPT `event` | `/eventos/meditacion-presencial-barranquilla` | `single-event.html` | cartel | — | Inventario | 200; **sin** Inscribirme (ADR 0035) |
| *(nueva en WP)* | listado | Festival Calma en la Ciudad | card → single | CPT `event` | `/eventos/festival-calma-en-la-ciudad` | `single-event.html` | cartel | — | Inventario | 200; sin inscripción |
| *(nueva en WP)* | listado | Pausa Profunda – Medellín | card → single | CPT `event` | `/eventos/pausa-profunda-medellin` | `single-event.html` | cartel | — | Inventario | 200; sin inscripción |
| *(nueva en WP)* | listado | Ansiedad, agotamiento… | card → single | CPT `event` | `/eventos/ansiedad-agotamiento-crisis-de-atencion` | `single-event.html` | cartel | — | Inventario | 200; sin inscripción |
| *(nueva en WP)* | listado | Vesak 2026 | card → single | CPT `event` | `/eventos/vesak-2026` | `single-event.html` | cartel | — | Inventario | 200; sin inscripción |
| `/eventos/pausa-profunda-cali` | ficha + card | Pausa Profunda – Cali | card + single | CPT `event` | KEEP | `single-event.html` | cartel | share | Inventario | |
| *(nueva en WP)* | listado | Buddhismo para tiempos de cansancio | card → single | CPT `event` | `/eventos/buddhismo-tiempos-cansancio` | `single-event.html` | cartel | — | Inventario | 200; sin inscripción |
| *(nueva en WP)* | listado | 6.º Encuentro Nacional 2025 | card → single | CPT `event` | `/eventos/6-encuentro-nacional-2025` | `single-event.html` | cartel | — | Inventario | 200; sin inscripción |
| `/` aside | `index.html` | nota featured = evento #1 | duplicado de presentación | **no** CPT extra | — | `front-page.html` Query | mismo cartel | — | Inventario | conteo eventos sigue en 10 |

Galería: 35 media + 3 álbumes (filas de datos, no URLs extra). Posts: 2 filas ya arriba. Conteos: [`conteos-reconciliacion-migracion.md`](conteos-reconciliacion-migracion.md).

---

## Blog (`post` nativo)

| Static URL | Static source | Content source | WP object | WP route | Template | JS | Assets | Import strategy | QA |
|---|---|---|---|---|---|---|---|---|---|
| `/blog` | `blog/index.html` | Listado estático | Página de entradas / `home` | `/blog` | `templates/home.html` | `main.js` | — | Ajuste «página de entradas» + posts | 200; listado; no 404 |
| `/blog/circulos-de-presencia-consciente` | `blog/circulos-de-presencia-consciente/index.html` | Ensayo publicado | `post` | mismo slug | `templates/single.html` | `main.js`, `share.js` | Imagen de entrada si aplica | post + meta `authors` → ficha Comunidad | 200; byline CPT (ADR 0037) |
| `/blog/sangha-refugio-hiperconexion` | `blog/sangha-refugio-hiperconexion/index.html` | Ensayo publicado | `post` | mismo slug | `templates/single.html` | `main.js`, `share.js` | — | post + meta `authors` → ficha Zheng Gong | 200; byline CPT (ADR 0037) |
| `/author/zheng-gong` *(nueva)* | *no existe en static* | ficha | `blog_author` | `/author/zheng-gong` | `single-blog_author.html` | `main.js` | foto si hay | seed CPT | 200; `query_var` ≠ `author`; no es user |
| `/author/comunidad-camino-del-dharma` *(nueva)* | *no existe en static* | ficha | `blog_author` | `/author/comunidad-camino-del-dharma` | `single-blog_author.html` | `main.js` | — | seed CPT | 200; mismo tipo que Zheng Gong |
| `/blog/tag/{slug}` | *no existe en static* | Tags nativos | `post_tag` archive | `/blog/tag/{slug}` | jerarquía nativa; `taxonomy-post_tag.html` opcional | `main.js` | — | N/A en corte inicial si no hay tags | Existe (no 404); `noindex,follow` hasta volumen (ADR 0031) |

---

## 404, búsqueda, CPT aplazados

| Static URL | Static source | Content source | WP object | WP route | Template | JS | Assets | Import strategy | QA |
|---|---|---|---|---|---|---|---|---|---|
| *(cualquier URL fuera del árbol)* | `404.html` | UI copy 09 | Ninguno | no hay `/404` pública | `templates/404.html` | `main.js` | — | N/A | HTTP **404** real + plantilla propia; no 200 disfrazado |
| Búsqueda | *no existe* | — | **No crear** | no hay `/buscar` | — | — | — | Prohibido (doc 04) | Confirmar ausencia de search form/indexable |
| `/sanghas`, `/sanghas/{slug}` | *no existe* | Modelo 03; fuera de alcance inicial Fase 3 (ADR 0024) | CPT `sangha` **solo si** se decide después | según doc 11 | `archive-sangha.html` / `single-sangha.html` | — | — | No implementar en el corte inicial | N/A hasta ADR/contenido |

---

## Estado de implementación WU-08A (2026-08-31)

Las filas de arriba conservan el inventario; este bloque registra el avance por dimensión
tras WU-08A (evidencia: `.audit/fase3-validation-matrix.md` § WU-06/WU-07/WU-08A):

| Dimensión | Estado | Detalle |
|---|---|---|
| CONTENT | **Pass (local)** | Importado WU-06 (verify 0 missing) + conversión WU-07 (`migrate convert`: inicio dinámico, galerías por álbum, enlaces OWN-016) + WU-08A (`practica` con audio nativo; copy de compartir sembrado como meta) |
| PRESENTATION | **Pass (local)** | 16 plantillas FSE + parts/patterns + 13 bloques dinámicos; CSS portado a presets; fontFace autohospedadas; lightbox nativo |
| ROUTING | **Pass (local)** | Rutas entrantes verificadas por curl y wp-phpunit (200/301/404/410); sin barra final (ADR 0008). Redirects del `.htaccess` → WU-08B |
| BEHAVIOR | **Parcial** | Nav móvil, tooltips del calendario, **diálogo Compartir, diálogo Añadir al calendario y audio de mantras nativo** portados (WU-08A); formulario CF7 → WU-09 (elegible en el corte, ADR 0041; no espera legal) |
| OPERATIONS | **Pass (local)** | Pipeline documentado import → seed → convert (idempotente, guard de producción); staging pendiente (OWN-005) |

Sustituciones static→WordPress registradas en WU-07 (§9.1 del master prompt; detalle y
remedios en `.audit/fase3-validation-matrix.md` § WU-07, «Decisiones»):

1. Eventos finalizados en tarjeta compacta (doc 03 §3 «Densidad») en vez de la card completa.
2. Fecha de evento generada desde `event_date`/`event_end` (reglas calibradas; filas `Hora`
   y `Aporte` de la maqueta no viven en el modelo — remedio: contenido del evento wp-admin).
3. Card vigente del listado = intro del single + meta + CTA (label «Preinscribirme»).
4. Excerpt del listado del blog = deck editorial; tiempo de lectura calculado (round /200).
5. Byline «Por …» enlazada a `/author/{slug}` (ADR 0037; el estático no enlaza).
6. `<picture>`/WebP/miniaturas hechas a mano no migran: la biblioteca sirve JPG + derivados.
7. `event_modality` es texto libre (copy publicado descriptivo; sustituye el select doc 03).
8. Galería: bloque Gutenberg nativo + lightbox, sin paginación (ADR 0021/0036, OWN-011);
   headings de álbum enlazan al término (opcional ADR 0036).
9. Copy nuevo mínimo OWN-016 en `/comunidad`: «Entradas del Maestro Zheng Gong en el blog» /
   «Entradas de la Comunidad en el blog».

---

## Redirects y restos (ROUTING / OPERATIONS)

Portar desde `.htaccess` actual; no son Pages.

| Entrada actual | Destino | Notas |
| -------------- | ------- | ----- |
| `/sangha-refugio-hiperconexion` | `/blog/sangha-refugio-hiperconexion` | 301 |
| `/encuentro-nacional-2026` | `/eventos/encuentro-nacional-2026` | 301 |
| `/pausa-profunda-cali` | `/eventos/pausa-profunda-cali` | 301 |
| `/prueba` | 410 Gone | Página de prueba de un WordPress **histórico** |
| `/category` y subrutas | `/blog` | Restos WP históricos |
| `/?page_id=10` | `/comunidad` | Restos WP históricos |
| otros `/?page_id=` | `/` | Restos WP históricos |
| `site.webmanifest` | 410 | ADR 0003 |
| HTTP / `www` | `https://caminodeldharma.org{URI}` | Canonical host |
| barra final (excepto `/`) | sin barra | ADR 0008 |
| `*/index.html` | URL limpia | — |

Tras el corte, estas reglas deben vivir donde WordPress no las borre al regenerar permalinks (plugin
de dominio, `redirect_canonical`, o bloque `.htaccess` documentado y respaldado).

---

## Assets transversales (no son URLs de contenido)

Incluir en OPERATIONS/QA aunque no tengan fila de Page:

| Recurso | Static | WordPress previsto |
| ------- | ------ | ------------------ |
| CSS | `assets/css/main.css` → `main.min.css` | `theme.json` + hoja complementaria (ADR 0029) |
| JS global | `assets/js/main.js` | Encolar en theme; selectores del menú |
| Fuentes | `assets/fonts/` | Theme assets |
| Favicon | `favicon.ico`, `favicon.svg`, `assets/favicon/` | Theme / Site Icon |
| `robots.txt` | Allow / + sitemap manual | Alinear con `/wp-sitemap.xml` (ADR 0030) |
| `sitemap.xml` | Manual | Nativo WP; retirar o redirigir el XML estático para no duplicar |
| `llms.txt` | Raíz | Conservar o generar; no sustituye sitemap |
| `.htaccess` | Política completa Hostinger | Tratamiento explícito; no desplegar el estático encima |

---

**Versión:** 1.4 · **Fecha:** 2026-08-31 · **Estado de filas:** inventario + avance WU-06/WU-07; CF7 y el delta de `/privacidad` **implementados** en WU-09 (entrega de correo pendiente de staging)
