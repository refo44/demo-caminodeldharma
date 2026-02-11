# Camino del Dharma — Theme File Structure

**Estructura de archivos del theme WordPress**

Define la arquitectura de archivos del theme: qué plantillas existen y qué partes se reutilizan. Las rutas oficiales están en 10; este documento define cómo se renderizan.

**Depende de:** `03-wordpress-content-model`, `04-mapa-pantallas`, `11-arbol-urls-final`, `05-arquitectura-informacion-navegacion`. **Referencia:** `02-identidad-corporativa` (theme.json), `06-wireframes` (estructura de bloques por pantalla)

---

## 1. Plantillas por función

| Función | Plantilla |
|---------|-----------|
| Home | `front-page.php` |
| La comunidad | `page-comunidad.php` |
| El linaje | `page-linaje.php` |
| Práctica y actividades | `page-practica.php` |
| Eventos especiales | `page-eventos.php` o `archive-event.php` |
| Contacto | `page-contacto.php` |
| Fallback páginas | `page.php` (fallback técnico; no se usa como plantilla editorial principal). |
| Fallback global | `index.php` (último fallback del sistema WordPress). Solo actúa como fallback técnico; no define una vista editorial. |

---

## 2. Event (CPT)

| Vista | Plantilla |
|-------|-----------|
| Listado | `archive-event.php` |
| Single | `single-event.php` |

*Single y archive de evento asumen soporte para imagen destacada del evento (03).*

### 2.1. Sangha (si se implementa CPT)

| Vista | Plantilla |
|-------|-----------|
| Listado | `archive-sangha.php` |
| Single | `single-sangha.php` |

---

## 3. Estados

| Estado | Archivo |
|--------|---------|
| No existe | `404.php` |
| Sin eventos vigentes | Render condicional en `page-eventos.php` o `archive-event.php` (mensaje amable en `/eventos/`) |

---

## 4. Partes reutilizables

| Archivo | Función |
|---------|---------|
| `parts/header.php` | Cabecera, logo, navegación |
| `parts/footer.php` | Pie, contacto, redes, donaciones |
| `parts/navigation.php` | Menú principal |
| `parts/meditation-block.php` | Bloque meditación semanal (reutilizable en Inicio y Práctica) |

Las partes reutilizables se integran mediante `get_template_part()` para mantener consistencia entre plantillas.

---

## 5. Árbol del theme

```
theme-camino-del-dharma/
├── style.css              (obligatorio, metadata, no estilos)
├── theme.json             ← tokens de diseño (paleta, tipografía, espaciado)
├── functions.php
├── index.php
├── front-page.php
├── page-comunidad.php
├── page-linaje.php
├── page-practica.php
├── page-eventos.php
├── page-contacto.php
├── page.php
├── single-event.php       (si CPT event)
├── archive-event.php      (si CPT event)
├── 404.php
├── archive-sangha.php     (si CPT sangha)
├── single-sangha.php      (si CPT sangha)
├── assets/
│   ├── css/
│   │   └── main.css       ← estilos reales (layout, lectura, componentes)
│   ├── js/
│   │   └── main.js       ← navegación, accesibilidad, interacción ligera; sin lógica compleja
│   ├── fonts/
│   ├── icons/
│   ├── images/
│   └── favicon/
└── parts/
    ├── header.php
    ├── footer.php
    ├── navigation.php
    └── meditation-block.php
```

---

## 6. URL → plantilla

| Ruta | Archivo |
|------|---------|
| `/` | `front-page.php` |
| `/comunidad/` | `page-comunidad.php` |
| `/linaje/` | `page-linaje.php` |
| `/practica/` | `page-practica.php` |
| `/eventos/` | `page-eventos.php` o `archive-event.php` |
| `/eventos/{slug}/` | `single-event.php` (si CPT event) |
| `/contacto/` | `page-contacto.php` |
| Cualquier otra | `404.php` |

*(Si se implementa CPT sangha: `/sanghas/` → archive-sangha.php; `/sanghas/{slug}/` → single-sangha.php.)*

---

## 7. Estrategia CSS

| Archivo | Rol |
|---------|-----|
| `style.css` | **Obligatorio.** Solo cabecera del theme (Theme Name, Description, Version, Text Domain). WordPress no reconoce el theme sin este archivo. No usar para estilos; solo metadata. |
| `theme.json` | Tokens de diseño: paleta (02), tipografías, espaciado. Controla editor y presets de bloques. |
| `assets/css/main.css` | Estilos reales: layout, márgenes de lectura, ancho de columna, espaciado contemplativo, componentes. Se encola en `functions.php`. |

**En `functions.php`:** encolar `main.css` dentro de `add_action( 'wp_enqueue_scripts', function() { ... } );`. Usar `get_template_directory_uri()` (este proyecto es parent theme cerrado; si hubiera child theme, en el child se usaría `get_stylesheet_directory_uri()`). Ejemplo: `wp_enqueue_style( 'camino-main', get_template_directory_uri() . '/assets/css/main.css', [], '1.0' );`. `style.css` no se usa para estilos del sitio; solo para metadata del theme. Un solo CSS de implementación: `main.css`.

---

## 8. theme.json

- Paleta: solo colores de `02-identidad-corporativa`
- Tipografías: según manual de marca (02)
- Tamaños tipográficos definidos para lectura cómoda; espaciado generoso (coherente con experiencia contemplativa del sitio)
- Sin bloques de animación en área de lectura

**No reemplaza** layout complejo ni componentes; se complementa con `assets/css/main.css`.

---

## 9. Stack técnico (arquitectura)

- **Plantillas:** PHP clásico (front-page, page-*, single-*, archive-*).
- **Diseño:** theme.json + CSS vanilla (`main.css`). Sin Tailwind, Bootstrap ni frameworks CSS.
- **Editor:** Gutenberg nativo. Bloques nativos y template parts (partes reutilizables como meditación, testimonios). Video embed: bloque nativo de Gutenberg (Embed/YouTube/Vimeo); no se construye un sistema de bloques a medida. **Elementor:** no. **ACF Blocks:** no se introducen salvo que haya una necesidad estructural repetida que Gutenberg nativo no cubra. Si se añade ACF u otro builder, la estrategia CSS se mantiene (un solo `main.css` encolado).

---

## Cierre

Este documento define la **estructura oficial de archivos del theme**: plantillas, partes reutilizables y mapeo URL → plantilla. Alineado con 10 (árbol de URLs), 04 (mapa de pantallas), 03 (modelo de contenido) y 05 (navegación). Las rutas están en 10; las plantillas aquí.

---

**Versión:** 1.1
