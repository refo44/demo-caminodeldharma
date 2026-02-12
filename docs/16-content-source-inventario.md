# Camino del Dharma — Inventario de contenido fuente

**Referencia rápida de qué hay en `content-source/` y cómo usarlo en el sitio.**  
**Versión 1.3**

**Referenciado por:** `15-assets-strategy` (estrategia de uso y migración de assets)

**Importante:** `content-source/` **solo existe en local** (no se despliega). Para el sitio estático (GitHub Pages) o el sitio final, copiar el material necesario en `assets/` en la raíz del repo o dentro del theme (véase 12). Mantener las copias originales sin editar dentro de `content-source/`; trabajar siempre sobre copias en el theme o en la raíz para evitar pérdida de calidad y sobrescrituras accidentales.

---

## 1. Estructura del directorio

| Carpeta / Archivo | Contenido |
|-------------------|-----------|
| `Pagina web Camino del Dharma/Contenido_Web_Camino_del_Dharma.docx` | Contenido principal de la web. **Seguir estrictamente.** |
| `Pagina web Camino del Dharma/Contenido_Web_Camino_del_Dharma.md` | Mismo contenido que el .docx (misma fuente canónica). **Seguir estrictamente.** |
| `Pagina web Camino del Dharma/Lluvia de ideas para la página web de la comunidad.docx` | Ideas adicionales (eventos, accesibilidad, donaciones, etc.) |
| `Pagina web Camino del Dharma/Identidad CAMINO DEL DHARMA- (1).pdf` | Manual de marca (paleta, tipografía, logos) |
| `Pagina web Camino del Dharma/Link videos subidos en Youtube.docx` | URLs de videos en YouTube (fuente original) |
| `Pagina web Camino del Dharma/Link-videos-youtube.md` | Mismo contenido que el .docx: lista canónica de 4 videos YouTube (título + URL) |
| `Pagina web Camino del Dharma/FOTOS PAGINA WEB/` | Imágenes, videos y logo por pestaña |

---

## 2. FOTOS PAGINA WEB — Mapeo por sección

Las carpetas en `content-source` se llaman «Pestaña N». Todos los archivos están renombrados en **kebab-case y semánticos** para producción.

| Pestaña (carpeta) | Archivos (nombres actuales) | Uso en el sitio |
|---------|-----------|-----------------|
| **Raíz** | `logo-camino-del-dharma.png` | Logo, favicon |
| **Pestaña 1** | `hero-estatua-buda-montanas.jpg`, `inicio-encuentro-comunidad.jpg`, `inicio-kuan-yin.jpg`, `inicio-ambiente.jpg` | Inicio, hero, ambiente |
| **Pestaña 2** | `comunidad-desarrollo-camino-dharma.jpg`, `comunidad-quienes-somos.jpg`, `linaje-budismo-chan.jpg`, `linaje-budismo-tierra-pura.jpg`, `linaje-shakyamuni-origen-buddhismo.jpg` | Comunidad, Linaje |
| **Pestaña 3** | `foto-biografia-fundador.jpg` | Biografía del fundador |
| **Pestaña 5** | `video-que-es-meditar.mp4`, `video-conectar-nuestro-planeta.mp4`, `video-redes-compasion.mp4`, Link videos subidos en Youtube.docx | Videos, meditación, YouTube |
| **Pestaña 6** | `contacto-comunidad.jpg` | Contacto |
| **Pestaña 7** | En producción: `galeria-01.jpg` … `galeria-N.jpg` (kebab-case, sin espacios, orden numérico). Ver `scripts/rename-gallery-to-kebab.sh`. | **Página Galería** (`/galeria/`): grid paginado con todas las imágenes. Tras añadir o renombrar fotos, actualizar el array `gallery-data` en `galeria/index.html` (o generar el JSON desde el listado del directorio). |
| **Pestaña 8** | `celebracion-diwali.jpg`, `celebracion-vesak-2019.jpg` | Celebraciones (Vesak, Diwali) |
| **Eventos** | En `assets/images/eventos/`: `evento-taller-pausa-profunda-feb-2026.jpeg`, `evento-6-encuentro-nacional.jpeg`, `evento-buddhismo-tiempos-cansancio.jpeg` | Carteles de eventos en `/eventos/` |

---

## 3. Documentos de texto

| Archivo | Contenido |
|---------|-----------|
| Contenido_Web_Camino_del_Dharma.docx / .md | Estructura de 6 páginas, copy, footer, datos bancarios. **Contenido canónico: debe seguirse estrictamente.** |
| Lluvia de ideas… | Cronograma eventos, testimonios, accesibilidad, El buda responde, donaciones, sanghas |
| Link videos subidos en Youtube.docx / Link-videos-youtube.md | Enlaces a videos en YouTube (4 conferencias/enseñanzas; ver lista en .md) |

---

## 4. Tamaños recomendados para el sitio

| Uso | Ancho máximo | Formato |
|-----|--------------|---------|
| Hero / Inicio | 1200–1600 px | JPG/WebP |
| Foto fundador | 600–800 px | JPG/WebP |
| Imágenes sección | 800–1000 px | JPG/WebP |
| Thumbnail galería | 400–600 px | JPG/WebP |

---

## 5. Documentos descargables (PDF)

| Archivo en `assets/documents/` | Uso |
|--------------------------------|-----|
| `recitacion-practica-comida.pdf` | Recitación práctica de la comida (página Práctica). Enlace «Descargar PDF». |

Los PDFs se colocan en `assets/documents/` en la raíz del repo; no en `content-source/`. Ver 15.

---

## 6. Acciones pendientes

1. **Videos YouTube:** URLs en `Link-videos-youtube.md` (y en .docx). Usar para embeds (03 §6).
2. **Imágenes:** En producción, todas las imágenes en `assets/images/` deben estar en kebab-case y optimizadas para web. Galería: ejecutar `scripts/rename-gallery-to-kebab.sh` para unificar nombres a `galeria-01.jpg` … `galeria-N.jpg`; luego `scripts/optimize-images.sh` para peso (15 §3.0).
3. **Metadatos:** Asignar alt, title y caption a cada imagen al subir al CMS.

---

## 7. Referencia: Paramitas

**Lluvia de ideas:** "Tener en cuenta la organización de información de la página de Paramitas."

Revisar la página de Paramitas como referencia de estructura y navegación si está disponible.

---

## Cierre

Este documento es el **inventario oficial** de `content-source/`: qué archivos y carpetas existen y cómo se mapean al sitio. Está alineado con la estructura real del directorio, con la estrategia de assets (15) y con la estructura del theme (12). No se despliega; los assets se copian al theme o a `assets/` en la raíz del repo (GitHub Pages) según 12.

---

**Versión:** 1.4
