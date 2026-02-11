# Camino del Dharma — Inventario de contenido fuente

**Referencia rápida de qué hay en `content-source/` y cómo usarlo en el sitio.**  
**Versión 1.1**

**Referenciado por:** `12-assets-strategy` (estrategia de uso y migración de assets)

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

Las carpetas en `content-source` se llaman «Pestaña N»; la tabla indica su uso por sección del sitio (04, 05).

| Pestaña (carpeta) | Contenido | Uso en el sitio |
|---------|-----------|-----------------|
| **Raíz** | logo 1.png | Logo, favicon |
| **Pestaña 1** | DSC01580.JPG, estatua-buda-montanas-flores-loto…, Kuan-Yin. 4.jpg, e8e40a6a… | Inicio, hero, ambiente |
| **Pestaña 2** | Foto desarrollo camino del dharma, Foto quienes somos, Imagen Budismo Chan, Imagen Budismo Tierra Pura, Imagen Shakyamuni | Comunidad, Linaje |
| **Pestaña 3** | Foto 1 Biografía Fundador.jpg | Biografía del fundador |
| **Pestaña 5** | ¿Qué es Meditar_.mp4, Conectar con nuestro planeta.mp4, Redes de Compasión_.mp4, Link videos subidos en Youtube.docx | Videos, meditación, YouTube |
| **Pestaña 6** | 419541416_… | Contacto |
| **Pestaña 7** | ~40 imágenes (DSC_*, IMG_*, WhatsApp Image…) | Galería comunitaria |
| **Pestaña 8** | diwali-festival-lights-tradition.jpg, IMG_20191201_150120.jpg | Celebraciones (Vesak, Diwali) |

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

1. **Identidad:** Extraer paleta y tipografía del PDF `Identidad CAMINO DEL DHARMA- (1).pdf`
2. **Videos YouTube:** URLs en `Link-videos-youtube.md` (y en .docx). Usar para embeds (03 §6).
3. **Renombrar imágenes:** Considerar nombres en kebab-case para producción (ej. `foto-biografia-fundador.jpg`)
4. **Metadatos:** Asignar alt, title y caption a cada imagen al subir al CMS

---

## 6. Referencia: Paramitas

**Lluvia de ideas:** "Tener en cuenta la organización de información de la página de Paramitas."

Revisar la página de Paramitas como referencia de estructura y navegación si está disponible.

---

## Cierre

Este documento es el **inventario oficial** de `content-source/`: qué archivos y carpetas existen y cómo se mapean al sitio. Está alineado con la estructura real del directorio y con la estrategia de assets (12). No se despliega; los assets se copian al theme o a `public/assets/` según 12.

---

**Versión:** 1.1
