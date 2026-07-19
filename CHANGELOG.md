# Changelog

Historial de versiones publicadas en producción. Hostinger no conserva un registro de despliegues cuando se sube un ZIP manualmente; este archivo es la referencia canónica de qué está en vivo.

La versión actual del repositorio está en [`VERSION`](VERSION).

Formato de paquete de despliegue: `camino-del-dharma-vX.Y.Z.zip`

**Antes de incrementar la versión:** actualizar `<lastmod>` en [`sitemap.xml`](sitemap.xml) para cada página HTML modificada (ver checklist en [`README.md`](README.md#despliegue-en-hostinger)).

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
