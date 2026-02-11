# Camino del Dharma — Estrategia de assets

**Iconos, fuentes, favicon, SVG, PDF, imágenes, audio**  
**Versión 1.3**

Define qué assets existen, dónde viven y cómo se usan.

**Depende de:** `02-identidad-corporativa`, `13-content-source-inventario`

---

## 1. Resumen de decisiones

| Área | Decisión |
|------|----------|
| **Iconos** | SVG inline o sprite. Sin icon fonts. |
| **Biblioteca de iconos** | **Lucide.** Una sola biblioteca en todo el sitio; no mezclar. Lucide ofrece el mejor equilibrio para una comunidad espiritual: limpia, amplia, no comercial, no tecnológica, no llamativa; coherente con identidad sobria y acogedora (docs 01, 02, 06). |
| **Fuentes** | Según manual de marca. Autohospedadas en `assets/fonts/`, woff2. |
| **Favicon** | Set completo: favicon.ico, favicon.svg, apple-touch-icon.png, site.webmanifest. |
| **SVG** | Iconos en `assets/icons/` (inline o sprite). Favicon en `assets/favicon/favicon.svg`. Sin icon fonts. |
| **PDF** | En content-source: solo referencia (manual de marca, no se despliega). Si el sitio ofrece PDFs descargables, usar `assets/documents/` y enlazar desde el sitio. |
| **Audio** | Si aplica: `assets/audio/`. Formatos: MP3 y/o Opus/WebM para streaming. Uso: meditación guiada, enseñanzas, podcasts. |
| **JS** | Solo navegación, formularios, accesibilidad. Sin frameworks. Todo con `defer`. |

---

## 2. Estructura de assets

```
assets/
├── icons/          SVGs (inline o sprite)
├── images/         Fotos por sección (desde content-source)
├── fonts/          Tipografías autohospedadas (woff2)
├── favicon/        ico, svg, png, webmanifest
├── audio/          Archivos de audio (si aplica): meditación, enseñanzas
└── documents/      PDFs públicos descargables (si aplica)

js/                 Scripts mínimos
├── main.js         (navegación, formularios, accesibilidad)
```

En WordPress, esta estructura va dentro del directorio del theme (véase `11-theme-file-structure`).

---

## 3. Imágenes por sección

Según `content-source/Pagina web Camino del Dharma/FOTOS PAGINA WEB/` (mapeo detallado en `13-content-source-inventario`). Tamaños recomendados: `13-content-source-inventario` (§4).

| Carpeta | Uso |
|---------|-----|
| Pestaña 1 | Inicio, hero, ambiente |
| Pestaña 2 | Comunidad, linaje |
| Pestaña 3 | Biografía fundador |
| Pestaña 5 | Videos (meditación, YouTube) |
| Pestaña 6 | Contacto |
| Pestaña 7 | Galería comunitaria |
| Pestaña 8 | Celebraciones (Vesak, Diwali) |

---

## 4. Logo

- **Origen:** `FOTOS PAGINA WEB/logo 1.png`
- **Uso:** Cabecera, favicon
- **Favicon:** Derivar del logo (múltiples tamaños)
- **Versión vectorial:** Si el logo existe solo en PNG, valorar en el futuro una versión SVG para la cabecera (mejora nitidez y escalado).

---

## 5. SVG

- **Iconos:** `assets/icons/`. Lucide (elegida por equilibrio sobriedad–acogida). Inline o sprite; sin icon fonts.
- **Favicon:** `assets/favicon/favicon.svg` para navegadores que lo admiten.
- **Logo:** Si existe versión vectorial del logo, preferir SVG para cabecera (escalado nítido).

---

## 6. PDF

- **En content-source:** El manual de marca (`Identidad CAMINO DEL DHARMA- (1).pdf`) y otros PDFs son **solo referencia**. No se despliegan ni se enlazan desde el sitio.
- **PDFs públicos:** Si el sitio ofrece documentos descargables (ej. información de donaciones, folletos), colocarlos en `assets/documents/` y enlazarlos desde las páginas correspondientes.

---

## 7. Videos

- **Origen:** `FOTOS PAGINA WEB/Pestaña 5/` (MP4 locales: ¿Qué es Meditar?, Conectar con nuestro planeta, Redes de Compasión) + **YouTube:** lista canónica en `content-source/.../Link-videos-youtube.md` (4 conferencias/enseñanzas; ver inventario 13).
- **Estrategia:** Integrar canal de YouTube; videos locales para sección de meditación. Los 4 videos YouTube son la fuente oficial para embeds de enseñanzas (03 §6).
- **Lluvia de ideas:** "Conectar el canal de youtube a la página", "Video sobre indicaciones para meditar".

---

## 8. Audio

- **Ubicación:** `assets/audio/` (si el sitio incluye contenido sonoro).
- **Formatos:** MP3 (compatibilidad amplia); Opus o WebM para streaming con menor peso. Ofrecer al menos un formato con buena compatibilidad.
- **Uso posible:** Meditación guiada, indicaciones para meditar, enseñanzas en audio, podcasts. Si el contenido viene de YouTube u otra fuente externa, priorizar embed o enlace; usar archivos locales cuando se necesite reproducción directa en el sitio.
- **Accesibilidad:** Incluir transcripción o descripción cuando sea contenido informativo.

---

## 9. Regla de migración

`content-source/` **no se despliega**. Copiar assets necesarios a `public/assets/` o dentro del theme. No enlazar nunca a `content-source/` desde el código.

---

## Cierre

Este documento define la **estrategia oficial de assets**: iconos, SVG, fuentes, favicon, PDF, imágenes, videos, audio y scripts. Está alineado con la identidad (02), el inventario de contenido fuente (13) y la estructura del theme (11).

---

**Versión:** 1.3
