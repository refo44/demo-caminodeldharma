# Camino del Dharma — Estrategia de assets

**Iconos, fuentes, favicon e imágenes**  
**Versión 1.0**

Define qué assets existen, dónde viven y cómo se usan.

**Depende de:** `02-identidad-corporativa`, `13-content-source-inventario`

---

## 1. Resumen de decisiones

| Área | Decisión |
|------|----------|
| **Iconos** | SVG inline o sprite. Sin icon fonts. |
| **Biblioteca de iconos** | Una sola, minimal: Lucide o Heroicons. |
| **Fuentes** | Según manual de marca. Autohospedadas en `assets/fonts/`, woff2. |
| **Favicon** | Set completo: favicon.ico, favicon.svg, apple-touch-icon.png, site.webmanifest. |
| **JS** | Solo navegación, formularios, accesibilidad. Sin frameworks. Todo con `defer`. |

---

## 2. Estructura de assets

```
assets/
├── icons/          SVGs inline o sprite
├── images/         Fotos por sección (desde content-source)
├── fonts/          Tipografías autohospedadas
└── favicon/        ico, svg, png, webmanifest

js/                 Scripts mínimos
├── main.js         (navegación, formularios, accesibilidad)
```

---

## 3. Imágenes por sección

Según `content-source/Pagina web Camino del Dharma/FOTOS PAGINA WEB/`:

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

---

## 5. Videos

- **Origen:** `FOTOS PAGINA WEB/Pestaña 5/` (MP4 + Link videos YouTube)
- **Estrategia:** Integrar canal de YouTube; videos locales para sección de meditación.
- **Lluvia de ideas:** "Conectar el canal de youtube a la página", "Video sobre indicaciones para meditar".

---

## 6. Regla de migración

`content-source/` **no se despliega**. Copiar assets necesarios a `public/assets/` o dentro del theme. No enlazar nunca a `content-source/` desde el código.

---

**Versión:** 1.0
