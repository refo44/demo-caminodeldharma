# Changelog

Historial de versiones publicadas en producción. Hostinger no conserva un registro de despliegues cuando se sube un ZIP manualmente; este archivo es la referencia canónica de qué está en vivo.

La versión actual del repositorio está en [`VERSION`](VERSION).

Formato de paquete de despliegue: `camino-del-dharma-vX.Y.Z.zip`

**Antes de incrementar la versión:** actualizar `<lastmod>` en [`sitemap.xml`](sitemap.xml) para cada página HTML modificada (ver checklist en [`README.md`](README.md#despliegue-en-hostinger)).

## [1.0.15] - 2026-07-21

### Meditación semanal en línea y eventos

- Nueva página `/practica/meditacion-semanal-en-linea` (horario, modalidad Zoom, enlace de participación).
- `index.html` y `practica/index.html`: enlace visible a la meditación semanal en línea.
- `eventos/index.html`: fichas de eventos pasados (Barranquilla, Calma en la Ciudad, Medellín, UniRemington, Vesak Bogotá) con imágenes nuevas.
- `eventos/encuentro-nacional-2026`: descripción del calendario `.ics` sin duplicar URL del cartel en el texto.
- `llms.txt`: entrada de la meditación semanal; `assets/css/main.css`: estilos de eventos pasados.
- `sitemap.xml`: `<lastmod>` `2026-07-21` en `/`, `/practica`, `/practica/meditacion-semanal-en-linea`, `/eventos` y `/eventos/encuentro-nacional-2026`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.14] - 2026-07-20

### Privacidad (embeds y almacenamiento local)

- `index.html`, `practica/index.html`, `practica/videos/index.html`: iframes de YouTube migrados a `youtube-nocookie.com`; Vimeo con `?dnt=1`; JSON-LD `embedUrl` alineado (hallazgo audit PRIV-001 / TASK-0006).
- `assets/js/main.js`: retirada la persistencia en `localStorage` del selector de idioma mientras el sitio es solo español (ADR 0019).
- `sitemap.xml`: `<lastmod>` actualizado a `2026-07-20` en `/`, `/practica` y `/practica/videos`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.13] - 2026-07-20

### Contenido editorial (grafía Buddhismo)

- `comunidad/index.html`: eliminada la sección «Cómo nos nombramos»; la explicación de grafía queda centralizada en Linaje. El copy usa «Buddhismo» y «buddhista» con naturalidad, sin justificación repetida.
- `linaje/index.html`: reescrita la nota «Sobre la palabra Buddhismo» — término sánscrito *buddha*, *Buddha*/*Buda* como título (no nombre propio), y reconocimiento de *budismo*/*budista* como formas extendidas en español.
- `sitemap.xml`: `<lastmod>` actualizado a `2026-07-20` solo en `/comunidad` y `/linaje` (únicas páginas modificadas; sin URLs nuevas ni retiradas).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.12] - 2026-07-19

### Privacidad

- **Google Analytics 4 desactivado** en las 14 páginas HTML: eliminado el bloque `gtag.js` (`G-B8FY69RGSS`). Motivo: cookies `_ga` sin consentimiento ni política de privacidad (hallazgo audit PRIV-001).
- **Reactivación:** solo tras política publicada y Consent Mode v2 (o alternativa acordada). Ver `.audit/implementation/tasks/TASK-0006.md` y `docs/17-orden-implementacion.md` §2.75 (PRIV-001). ID de propiedad conservado para uso futuro: `G-B8FY69RGSS`.
- Métricas de indexación: seguir usando **Google Search Console** (sin cookies en el sitio).

### SEO (auditoría externa — continuación 2026-07-19)

- `.htaccess`: limpieza del índice residual de la etapa WordPress — `410` para `/prueba`, `301` de `/category/*` → `/blog`, `301` de `/?page_id=10` → `/comunidad` y de otros `?page_id=` → portada (hallazgo SEO-EXT-002; estas URLs seguían indexadas y una página "prueba" aparecía en el SERP de marca).
- `index.html` (JSON-LD Organization): retirado `alternateName` "budismo en Colombia" (keyword, no nombre real); añadidos `foundingDate: 2012`, `foundingLocation: Cali` y `knowsAbout` (Chan, Tierra Pura, meditación, atención plena). Dirección postal no añadida: pendiente de confirmación de la comunidad.
- `index.html` y `comunidad/index.html`: mención textual de la fundación en Cali (2012) — señal local que el sitio no tenía.
- `eventos/index.html`: título "Eventos y Retiros Budistas en Colombia | Camino del Dharma" y descripción con intención temática (og/twitter sincronizados).
- `blog/index.html`: título "Blog de Budismo — Reflexiones y Enseñanzas | Camino del Dharma" (og/twitter sincronizados).
- Evidencia y análisis completo: `.audit/working/seo-external.md`; tareas derivadas TASK-0013–0016.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.11] - 2026-07-19

### Mejoras

- Google Analytics 4 (`G-B8FY69RGSS`): etiqueta `gtag.js` directa en las 14 páginas HTML (sin Google Tag Manager).
- `sitemap.xml`: `<lastmod>` en todas las URLs indexables (`2026-07-19`), al modificarse cada HTML del sitio.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.10] - 2026-07-19

### Mejoras

- Rendimiento (PageSpeed): preload del hero WebP, fuente Inter 400 y CSS principal en inicio.
- Inicio: imágenes con `<picture>` (WebP + JPEG), `fetchpriority="high"` en hero, lazy load bajo el pliegue.
- Imágenes optimizadas en `assets/images/inicio/` (JPEG recomprimidos + variantes `.webp`).
- CSS: `picture` en bloques hero y section-figure.
- `sitemap.xml`: `<lastmod>` actualizado en `/` (`2026-07-19`).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.9] - 2026-07-19

### Mejoras

- Eventos: JSON-LD enriquecido en páginas de detalle (`@id`, organizer, performer, `validFrom`, dirección ampliada).
- Eventos: eliminado microdata (`itemscope`/`itemprop`) del listado y de las fichas; datos estructurados solo en JSON-LD de cada evento.
- `sitemap.xml`: `<lastmod>` actualizado en `/eventos`, `/eventos/encuentro-nacional-2026` y `/eventos/pausa-profunda-cali` (`2026-07-19`), tras los cambios JSON-LD en esas páginas.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.8] - 2026-07-19

### Mejoras

- `<meta name="theme-color">` en todas las páginas; favicon 48×48 añadido.
- SEO: títulos y descripciones refinados en inicio, comunidad, linaje y práctica.
- Inicio: tagline y enlace introductorio a la comunidad.
- Eliminado `site.webmanifest` y referencias PWA (decisión de no implementar app instalable).
- `sitemap.xml`: `<lastmod>` alineado en todas las URLs (`2026-07-19`).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.7] - 2026-07-18

### Mejoras

- `sitemap.xml`: fechas `<lastmod>` actualizadas en eventos y artículo del blog.
- Checklist de despliegue: paso obligatorio de revisar `sitemap.xml` antes de incrementar `VERSION`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.6] - 2026-07-18

### Mejoras

- Metadatos SEO y redes sociales refinados en todas las páginas (título, descripción, Open Graph, Twitter Cards).
- Favicons estandarizados (`assets/favicon/`) y `site.webmanifest`.
- Imagen por defecto para compartir (`assets/images/og-default.jpg`).
- `llms.txt`: ajustes menores de contenido.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.5] - 2026-07-18

### Mejoras

- `llms.txt`: guía curada del sitio para agentes de IA (convención llmstxt.org), con enlaces canónicos a las páginas principales.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.4] - 2026-07-18

### Mejoras

- Resolución de URLs canónicas en `calendar.js` y `share.js` para enlaces de eventos y compartir.
- Rutas absolutas corregidas en páginas de eventos, práctica y blog.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.3] - 2026-07-18

### Mejoras

- Imágenes actualizadas en galería, inicio, comunidad, linaje, eventos, práctica, blog, contacto, celebraciones y logo.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.2] - 2026-07-18

### Mejoras

- `sitemap.xsl`: texto introductorio más breve en la vista del mapa del sitio.

### Correcciones

- Eventos finalizados: eliminados CTAs obsoletos en HTML y estilos asociados en `main.css`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.1] - 2026-07-18

### Mejoras

- `sitemap.xsl`: vista legible del mapa del sitio al abrir `/sitemap.xml` en el navegador.
- `sitemap.xml`: referencia a la hoja de estilo XSL (sin impacto en buscadores).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.0] - 2026-07-18

### Publicación inicial

- Sitio web publicado en producción.
- SEO: meta description, canonical, Open Graph, Twitter Cards, `robots`.
- Metadatos documentales: `author`, `creator`, `publisher`, `developer`, `copyright`, `keywords`.
- `robots.txt` y `sitemap.xml` con URLs canónicas sin barra final.
- JSON-LD: Organization, WebSite, BreadcrumbList, Event, Article, VideoObject.
- Accesibilidad: HTML semántico, skip link, navegación por teclado, contraste.
- Diseño responsive; corrección del selector de idioma en móvil.
- `.htaccess`: HTTPS, URLs sin barra final, 404 personalizada, caché y cabeceras de seguridad.

### Estado

- Desarrollo: Finalizado
- Producción: Publicado

### Servidor

- Hostinger
- Dominio: <https://caminodeldharma.org>

### Observaciones

- Primera versión pública.
- Pendiente o en curso: verificación en Google Search Console y envío del sitemap.
