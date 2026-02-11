# Camino del Dharma — Estrategia de assets

**Iconos, fuentes, favicon, SVG, PDF, imágenes, audio**  
**Versión 1.4**

Define qué assets existen, dónde viven y cómo se usan. La geografía del proyecto (docs, content-source, theme) está en 13; la arquitectura CSS (capas, tokens, main.css) en 14.

**Depende de:** `02-identidad-corporativa`, `16-content-source-inventario`. **Referencia:** `12-theme-file-structure`, `13-static-file-structure`, `14-css-architecture`

---

## 1. Resumen de decisiones

| Área               | Decisión                                                                                                                                                          |
|--------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Iconos**         | SVG inline o sprite. Sin icon fonts. Inline para iconos sueltos; sprite para iconos muy repetidos (no mezclar estrategias sin necesidad en la primera entrega).  |
| **Biblioteca de iconos** | **Lucide.** Una sola biblioteca en todo el sitio; no mezclar. Lucide ofrece el mejor equilibrio para una comunidad espiritual: limpia, amplia, no comercial, no tecnológica, no llamativa; coherente con identidad sobria y acogedora (docs 01, 02, 06). |
| **Fuentes**        | Según manual de marca. Autohospedadas en `assets/fonts/`, woff2.                                                                                                  |
| **Tipografía**     | Definida (MarloweEscapade, Downtown DEMO Regular). Según 02; uso en 14.                                                                                           |
| **Favicon**        | Set completo: `favicon.ico`, `favicon.svg`, `apple-touch-icon.png` (180×180), `favicon-32x32.png`, `favicon-16x16.png`, `site.webmanifest` con name/theme_color de 02. |
| **SVG**            | Iconos en `assets/icons/` (inline o sprite). Favicon en `assets/favicon/favicon.svg`. Sin icon fonts.                                                             |
| **PDF**            | En content-source: solo referencia (manual de marca, no se despliega). Si el sitio ofrece PDFs descargables, usar `assets/documents/` y enlazar desde el sitio.  |
| **Audio**          | Si aplica: `assets/audio/`. Formatos: MP3 y/o Opus/WebM para streaming. Uso: meditación guiada, enseñanzas, podcasts.                                             |
| **JS**             | Solo navegación, formularios, accesibilidad. Sin frameworks. Scripts en footer; `defer` opcional mediante `script_loader_tag` solo para scripts no críticos.      |
| **CSS**            | `style.css` solo cabecera del theme (obligatorio WP). Estilos reales en `assets/css/main.css`; encolar en functions.php. theme.json para tokens. Capas, variables y criterios en 12 §7 y 14. |

---

## 2. Estructura de assets

```text
assets/
├── css/
│   └── main.css    Estilos reales del theme (layout, lectura, componentes). Encolado en functions.php. Ver 12 §7.
├── js/
│   └── main.js     Navegación, formularios, accesibilidad. Sin frameworks; encolado en footer; `defer` opcional vía filtro.
├── icons/          SVGs (inline o sprite, según reglas de 1. Resumen)
├── images/         Fotos por sección (desde content-source)
├── fonts/          Tipografías autohospedadas (woff2)
├── favicon/        ico, svg, png, webmanifest
├── audio/          Archivos de audio (si aplica): meditación, enseñanzas
└── documents/      PDFs públicos descargables (si aplica)
```

En WordPress, esta estructura va dentro del directorio del theme (`theme-camino-del-dharma/assets/`). Geografía completa del repo (docs, content-source, theme) en 12.

---

## 3. Formatos y optimización

- **Imágenes:** Preferir WebP (JPG como fallback si hace falta). Evitar PNG salvo transparencias reales. Cuidar peso por imagen; tamaños típicos (hero, card, avatar, etc.) en 15 §4.
- **SVG:** Solo para iconos y logo cuando exista versión vectorial.
- El sitio es editorial; el rendimiento es accesibilidad. Mantener imágenes de contenido dentro de rangos razonables. En WordPress, usar `srcset` y tamaños responsivos nativos cuando aplique; evitar servir imágenes de hero de ancho completo a pantallas móviles.

### 3.1 Fuentes: carga y licencia

- **Carga:** `font-display: swap` obligatorio; declarar fallback stack en CSS (14). Las variables `--font-display`, `--font-heading` y `--font-body` en CSS deben mapear directamente a las fuentes definidas en 15 (según 02), no redefinirse arbitrariamente.
- **Licencia:** **Obligatorio** antes de producción: validar licencia de tipografías (p. ej. Downtown DEMO Regular; las fuentes "DEMO" a veces no permiten uso en producción). Si la licencia no permite uso comercial o en producción, reemplazar por alternativa adecuada.

---

## 4. Imágenes por sección

Según `content-source/Pagina web Camino del Dharma/FOTOS PAGINA WEB/` (mapeo detallado en `16-content-source-inventario`). Tamaños recomendados: `16-content-source-inventario` (§4).

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

## 5. Logo

- **Origen:** `FOTOS PAGINA WEB/logo 1.png`
- **Uso:** Cabecera.
- **Favicon:** Derivar desde el logo, idealmente desde versión vectorial o un master de alta resolución (evitar favicon borroso desde PNG pequeño).
- **Versión vectorial:** Si el logo existe solo en PNG, valorar en el futuro una versión SVG para la cabecera (mejora nitidez y escalado). Mientras tanto, usar PNG a 2x para cabecera y favicon; evitar escalar PNG pequeños más allá de su tamaño real.

---

## 6. SVG

- **Iconos:** `assets/icons/`. Lucide (elegida por equilibrio sobriedad–acogida). Inline para iconos únicos o pocos por página; sprite si se repiten mucho en distintas secciones o listados. No usar ambas estrategias a la vez en la primera entrega salvo necesidad real. Sin icon fonts.
- **Accesibilidad (19):** Iconos decorativos: `aria-hidden="true"`. Iconos informativos: **preferir texto visible**; si no es posible, usar texto oculto con clase `.visually-hidden` o, en última instancia, un nombre accesible (`aria-label`) según `19-accesibilidad-estandares`.
- **Favicon:** `assets/favicon/favicon.svg` para navegadores que lo admiten.
- **Logo:** Si existe versión vectorial del logo, preferir SVG para cabecera (escalado nítido).

---

## 7. PDF

- **En content-source:** El manual de marca (`Identidad CAMINO DEL DHARMA- (1).pdf`) y otros PDFs son **solo referencia**. No se despliegan ni se enlazan desde el sitio.
- **PDFs públicos:** Si el sitio ofrece documentos descargables (ej. información de donaciones, folletos), colocarlos en `assets/documents/` y enlazarlos desde las páginas correspondientes.
- **Accesibilidad (19):** Todo PDF público debe cumplir mínimo: título, orden de lectura, contraste. Si no se garantiza, ofrecer alternativa en HTML. Detalle en `19-accesibilidad-estandares`.

---

## 8. Videos

- **Origen:** `FOTOS PAGINA WEB/Pestaña 5/` (MP4 locales: ¿Qué es Meditar?, Conectar con nuestro planeta, Redes de Compasión) + **YouTube:** lista canónica en `content-source/.../Link-videos-youtube.md` (4 conferencias/enseñanzas; ver inventario 16).
- **Estrategia:** Por defecto, embed de YouTube (los 4 videos listados son la fuente oficial para enseñanzas; 03 §6) e integración del canal. Videos locales solo cuando haya necesidad editorial clara (p. ej. sección de meditación).
- **Videos locales:** Usar `<video controls>` con `preload="metadata"`; incluir poster (imagen de preview); evitar autoplay. Impacta rendimiento y accesibilidad (19).
- **Lluvia de ideas:** "Conectar el canal de youtube a la página", "Video sobre indicaciones para meditar".

---

## 9. Audio

- **Ubicación:** `assets/audio/` (si el sitio incluye contenido sonoro).
- **Formatos:** MP3 (compatibilidad amplia); Opus o WebM para streaming con menor peso. Ofrecer al menos un formato con buena compatibilidad.
- **Uso posible:** Meditación guiada, indicaciones para meditar, enseñanzas en audio, podcasts. Si el contenido viene de YouTube u otra fuente externa, priorizar embed o enlace; usar archivos locales cuando se necesite reproducción directa en el sitio.
- **Accesibilidad:** Usar `<audio controls>` (no ocultar controles). Incluir transcripción o descripción cuando sea contenido informativo.

---

## 10. Regla de migración

`content-source/` **no se despliega**. Copiar assets necesarios al theme (`theme-camino-del-dharma/assets/`: images, fonts, icons, favicon, etc.). Si existe maqueta estática previa (`public/` o `dist/`), alinear con esta estructura; el destino final de producción es el theme. No enlazar nunca a `content-source/` desde el código. Detalle del flujo en 13.

---

## Cierre

Este documento define la **estrategia oficial de assets**: iconos, SVG, fuentes, favicon, PDF, imágenes, videos, audio y scripts. Alineado con la identidad (02), el inventario (16), la estructura del theme (12), la estructura de archivos estáticos (13) y la arquitectura CSS (14).

---

**Versión:** 1.4
