# Camino del Dharma — Inventario de contenido fuente

**Referencia rápida de qué hay en `content-source/` y cómo usarlo en el sitio.**  
**Versión 1.2**

**Referenciado por:** `15-assets-strategy` (estrategia de uso y migración de assets)

**Importante:** `content-source/` **solo existe en local** (no se despliega). Para la maqueta estática y el sitio final, copiar el material necesario en `public/assets/` o dentro del theme (véase 12). Mantener las copias originales sin editar dentro de `content-source/`; trabajar siempre sobre copias en el theme o en `public/` para evitar pérdida de calidad y sobrescrituras accidentales.

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
| **Pestaña 7** | `galeria-01.jpg` … `galeria-35.jpg`, `galeria-36.jpeg` … `galeria-43.jpeg` (43 archivos) | **Inicio:** fila de 4 imágenes + enlace «Ver galería completa». **Página Galería** (`/galeria/`): grid con las 43 imágenes. |
| **Pestaña 8** | `celebracion-diwali.jpg`, `celebracion-vesak-2019.jpg` | Celebraciones (Vesak, Diwali) |

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

## 5. Acciones pendientes

1. **Videos YouTube:** URLs en `Link-videos-youtube.md` (y en .docx). Usar para embeds (03 §6).
2. **Imágenes:** Ya renombradas en content-source a kebab-case semántico (véase §2). Al copiar a `public/` o al theme, usar estos mismos nombres.
3. **Metadatos:** Asignar alt, title y caption a cada imagen al subir al CMS

---

## 6. Referencia: Paramitas

**Lluvia de ideas:** "Tener en cuenta la organización de información de la página de Paramitas."

Revisar la página de Paramitas como referencia de estructura y navegación si está disponible.

---

## Cierre

Este documento es el **inventario oficial** de `content-source/`: qué archivos y carpetas existen y cómo se mapean al sitio. Está alineado con la estructura real del directorio, con la estrategia de assets (15) y con la estructura del theme (12). No se despliega; los assets se copian al theme o a `public/assets/` según 12.

---

**Versión:** 1.2
