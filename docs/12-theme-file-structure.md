# Camino del Dharma — Theme File Structure

**Estructura de archivos del theme WordPress**

Define la arquitectura de archivos del theme: qué plantillas existen y qué partes se reutilizan. Las rutas oficiales están en 11; este documento define cómo se renderizan.

**Depende de:** `03-wordpress-content-model`, `04-mapa-pantallas`, `11-arbol-urls-final`, `05-arquitectura-informacion-navegacion`. **Referencia:** `02-identidad-corporativa` (theme.json), `06-wireframes` (estructura de bloques por pantalla)

---

## 1. Plantillas por función

| Función | Plantilla |
|---------|-----------|
| Home | `front-page.php` |
| La comunidad | `page-comunidad.php` |
| El linaje | `page-linaje.php` |
| Práctica y actividades | `page-practica.php` |
| Eventos especiales | `page-eventos.php` o `archive-event.php` (si se activa CPT event, `archive-event.php` será la vista principal de `/eventos/`) |
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

### 2.1. Sangha (opcional; fuera del alcance actual; no está en mapa de pantallas ni wireframes)

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
| `parts/header.php` | Cabecera (logo + incluye parts/navigation.php) |
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
├── functions.php          (opcional: inc/setup.php, inc/enqueue.php para evitar archivo gigante; en proyectos pequeños todo puede vivir aquí)
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
│   │   └── main.js       ← navegación, accesibilidad, interacción ligera; se encola en footer (14, 17)
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

**En `functions.php`:** encolar `main.css` dentro de `add_action( 'wp_enqueue_scripts', function() { ... } );`. Usar `get_template_directory_uri()` (este proyecto es parent theme cerrado; si hubiera child theme, en el child se usaría `get_stylesheet_directory_uri()`). Ejemplo: `wp_enqueue_style( 'camino-main', get_template_directory_uri() . '/assets/css/main.css', [], '1.0' );`. `main.js` se encola con `wp_enqueue_script( ..., [], '1.0', true )` (quinto parámetro `true` = carga en footer para no bloquear render; para atributo `defer` explícito usar `script_loader_tag`). `style.css` no se usa para estilos del sitio; solo para metadata del theme. Un solo CSS de implementación: `main.css`.

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

## 10. Compatibilidad con la maqueta estática

La estructura de plantillas replica la maqueta estática definida en Fase 2 (17). Cada archivo `page-*.php` envuelve el HTML previamente validado, sin rediseñar ni alterar la jerarquía de bloques. La migración a WordPress es una adaptación de HTML a PHP, no una re-interpretación visual. WordPress no rediseña; solo renderiza contenido dinámico.

---

## 11. Mejores prácticas (WordPress) y anti-patrones a evitar

Esta sección define criterios de implementación para que el theme siga prácticas sólidas y evite soluciones frágiles o difíciles de mantener. Aplica en Fase 3 (WordPress) y debe respetar la maqueta de Fase 2 (17).

### 11.1 Principios de implementación

- **Plantillas limpias:** `front-page.php`, `page-*.php`, `single-*`, `archive-*` deben contener principalmente markup y llamadas simples (`get_header()`, `the_title()`, `the_content()`, `get_template_part()`).
- **Lógica fuera de las plantillas:** Cualquier lógica no trivial (cálculos, transformaciones, condiciones complejas, armado de data) vive en `functions.php` o archivos cargados desde `functions.php` (ver §11.2).
- **Una fuente de verdad visual:** `assets/css/main.css` es el CSS real del sitio. No duplicar estilos en otros archivos.
- **Reutilización por partes:** Usar `get_template_part()` para cabecera, navegación, pie y bloques repetibles (p. ej. meditación) tal como define §4.
- **Seguridad por defecto:** Output escapado y entradas sanitizadas (ver §11.4). No imprimir datos sin escape.
- **Cero “builder lock-in”:** No introducir Elementor u otros builders. No introducir ACF Blocks salvo necesidad estructural repetida que Gutenberg nativo no cubra (ver §9).
- **Hooks básicos del theme:** Registrar `add_theme_support( 'post-thumbnails' )`, `add_theme_support( 'title-tag' )`, `add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) )`, `register_nav_menus()`.
- **Responsive:** El theme debe respetar el comportamiento responsive definido en `20-layout-principles`. No introducir breakpoints nuevos sin necesidad editorial.
- **Accesibilidad:** Mantener estructura semántica, navegación por teclado y contraste definidos en `19-accesibilidad-estandares`.

### 11.2 Organización del código (evitar “functions.php dios”)

`functions.php` debe ser un bootstrap (cargar archivos y registrar hooks). Evitar que se convierta en un “God file”.

Estructura recomendada si se necesita crecer:

- `inc/setup.php` — theme supports, menus, features
- `inc/enqueue.php` — encolado CSS/JS
- `inc/cpt-event.php` — CPT event y taxonomías si aplica
- `inc/helpers.php` — helpers pequeños y seguros
- `inc/security.php` — nonces, sanitización, hardening básico

**Reglas:** `functions.php` solo incluye `require_once` + hooks de alto nivel. No crear “mini frameworks” ni contenedores de DI. No usar autoload Composer dentro del theme salvo decisión explícita de proyecto.

### 11.3 Anti-patrones comunes (prohibidos)

- **Todo en un solo archivo:** `functions.php` como lugar donde “vive todo”.
- **Lógica compleja en templates:** queries grandes, loops con condiciones enredadas, cálculos o transformaciones extensas dentro de `page-*.php`. No duplicar loops (`have_posts()` / `the_post()`) entre templates sin necesidad.
- **Hardcodear URLs:** no escribir rutas absolutas a mano (usar `home_url()`, `get_permalink()`, etc.).
- **Hardcodear textos editoriales:** no fijar copy dentro de plantillas; usar contenido gestionado desde WordPress (09, content-source).
- **Inline styles:** no usar `style=""` para maquetación.
- **CSS fragmentado sin control:** múltiples archivos de estilos sin necesidad. Un solo entry (`assets/css/main.css`).
- **Plugins para resolver lo ya definido:** no añadir plugins para layout, navegación, grids o estilos que ya están definidos por 06, 14 y 20.
- **“Golden Hammer” de plugins/builders:** evitar builders o plugins pesados por costumbre.

### 11.4 Seguridad mínima (obligatorio)

- **Escapar output:** usar `esc_html()`, `esc_attr()`, `esc_url()` según contexto. Escapar todas las salidas dinámicas cuando aplique.
- **Sanitizar input:** usar `sanitize_text_field()`, `sanitize_email()`, `wp_kses_post()` según corresponda.
- **Nonces:** cualquier formulario o acción que modifique estado debe usar nonce (`wp_nonce_field()` y validación con `check_admin_referer()` o `wp_verify_nonce()`).
- **No exponer data sensible:** no imprimir correos, teléfonos u otros datos sin intención editorial clara.

### 11.5 Performance y accesibilidad (alineado con Fase 2)

- **CSS/JS mínimos:** `main.css` + `main.js` (si aplica), sin frameworks. Scripts con carga en footer (o defer vía `script_loader_tag`) y sin lógica compleja.
- **Accesibilidad preservada:** no romper focus visible, navegación por teclado, contraste y estructura semántica de la maqueta estática (19 y 20).
- **No animaciones decorativas en lectura:** coherente con la regla contemplativa (18).

### 11.6 Regla de coherencia con Fase 2

La traducción a WordPress es un cambio de motor, no de diseño:

- No cambiar orden de bloques definido en `06-wireframes`.
- No cambiar layout, grid y responsive definidos en `20-layout-principles`.
- No cambiar microcopy sin pasar por `09-ui-copy-sheet` y content-source.

---

## 12. Filosofía técnica del theme

El theme prioriza simplicidad, claridad estructural y longevidad. Cada decisión técnica debe favorecer legibilidad, estabilidad y facilidad de mantenimiento antes que complejidad o efectos visuales. Theme liviano; sin dependencia de frameworks; basado en Gutenberg; CSS propio y controlado; arquitectura clara y estable.

---

## Cierre

Este documento define la **estructura oficial de archivos del theme**: plantillas, partes reutilizables, mapeo URL → plantilla, compatibilidad con la maqueta estática (10), mejores prácticas y anti-patrones (11) y filosofía técnica (12). Alineado con 11 (árbol de URLs), 04 (mapa de pantallas), 03 (modelo de contenido), 05 (navegación), 17 (orden de implementación), 19 (accesibilidad) y 20 (layout y responsive).

---

**Versión:** 1.1
