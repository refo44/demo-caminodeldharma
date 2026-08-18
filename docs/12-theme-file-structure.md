# Camino del Dharma — Theme File Structure

**Estructura de archivos del theme WordPress**

Define la arquitectura de archivos del theme: qué plantillas existen y qué partes se reutilizan. Las rutas oficiales están en 11; este documento define cómo se renderizan.

**Ubicación en el repositorio (Fase 3):** `wordpress/wp-content/themes/camino-del-dharma/` (ADR 0011). Tras el corte de producción, el theme puede moverse a `theme/` o permanecer bajo `wp-content/themes/` (13 §1.3).

**Prerrequisito:** ratificación de ADR 0012 antes de iniciar Fase 3.

**Depende de:** `03-wordpress-content-model`, `04-mapa-pantallas`, `11-arbol-urls-final`, `05-arquitectura-informacion-navegacion`. **Referencia:** `02-identidad-corporativa` (theme.json), `06-wireframes` (estructura de bloques por pantalla)

**Arquitectura (ADR 0029):** el theme es un **theme de bloques (block theme / Full Site Editing)**,
no un theme clásico PHP. `theme.json` es la fuente de verdad de los tokens de diseño y alimenta el
panel **Estilos** del Editor de sitio, para que un Administrador pueda ajustar paleta, tipografía y
espaciado desde wp-admin sin desplegar código. Las vistas se definen como plantillas de bloques
(`templates/*.html`, `parts/*.html`), no como archivos PHP. Este documento sustituye, en las secciones
afectadas, la versión previa escrita bajo ADR 0009 (theme clásico, CSS congelado).

---

## 1. Plantillas por función

| Función | Plantilla (block template) |
|---------|-----------|
| Home | `templates/front-page.html` |
| La comunidad | `templates/page-comunidad.html` |
| El linaje | `templates/page-linaje.html` |
| Práctica y actividades | `templates/page-practica.html` |
| Eventos especiales | Si se usa CPT event: `/eventos/` se resuelve con `templates/archive-event.html`; **no se publica** una Page "Eventos" con slug `eventos` (evita conflicto de jerarquía WP). Si no hay CPT: `templates/page-eventos.html`. |
| Galería | `templates/page-galeria.html` |
| Contribuir (donaciones) | `templates/page-donaciones.html` |
| Contacto | `templates/page-contacto.html` |
| Blog | Ver §2.2 (`templates/home.html` para `/blog/`, `templates/single.html` para `/blog/{slug}/`) |
| Fallback páginas | `templates/page.html` (fallback técnico; no se usa como plantilla editorial principal). |
| Fallback global | `templates/index.html` (obligatorio: es el requisito mínimo de WordPress para reconocer el theme como theme de bloques; último fallback del sistema). Solo actúa como fallback técnico; no define una vista editorial. |

---

## 2. Event (CPT)

| Vista | Plantilla |
|-------|-----------|
| Listado | `templates/archive-event.html` |
| Single | `templates/single-event.html` |

*Single y archive de evento asumen soporte para imagen destacada del evento (03). El listado usa el bloque núcleo **Query Loop**; el single, bloques de campo de entrada. **Excepción — calendario del mes:** no lo resuelve Query Loop ni `get_calendar()`. En Fase 3, `camino-del-dharma-core` expone los datos de cada celda; el theme registra un bloque dinámico propio (p. ej. `camino-del-dharma/eventos-calendar`) en `templates/archive-event.html`, con tooltips `data-tooltip` y el aviso táctil `.eventos-calendar-hint` según 09 y 19. No se instala un plugin de calendario de terceros (ADR 0024, ADR 0025). La meditación semanal no es un `event`.*

### 2.1. Sangha (opcional; fuera del alcance actual; no está en mapa de pantallas ni wireframes)

| Vista | Plantilla |
|-------|-----------|
| Listado | `templates/archive-sangha.html` |
| Single | `templates/single-sangha.html` |

### 2.2. Blog (post nativo)

| Vista | Plantilla |
|-------|-----------|
| Listado (`/blog/`) | `templates/home.html` |
| Single (`/blog/{slug}/`) | `templates/single.html` |
| Archivo de tag (`/blog/tag/{slug}/`) | Resuelve por la jerarquía nativa de WordPress (`templates/taxonomy-post_tag.html` si se crea; si no, cae a `templates/archive.html`/`templates/index.html`) — no requiere plantilla propia. `noindex, follow` por defecto (ADR 0031) hasta que el tag tenga volumen suficiente; el filtro que lo aplica vive en `camino-del-dharma-core` o el theme, no en la plantilla. |

---

## 3. Estados

| Estado | Archivo |
|--------|---------|
| No existe | `templates/404.html` |
| Sin eventos vigentes | Contenido "sin resultados" del bloque **Query Loop** en `page-eventos.html` o `archive-event.html` (mensaje amable en `/eventos/`); es una propiedad nativa del bloque, no una condicional PHP escrita a mano. |

---

## 4. Partes reutilizables

**Mecanismo de un theme de bloques:** no hay `get_header()`/`get_footer()` ni `header.php`/`footer.php`. Cada plantilla en `templates/` inserta las partes con el bloque núcleo **Template Part** (`<!-- wp:template-part slug="header" tagName="header" /-->`), y `theme.json` las registra en `templateParts`. El markup real vive en `parts/*.html`.

| Archivo | Función |
|---------|---------|
| `parts/header.html` | Cabecera (logo + navegación) |
| `parts/footer.html` | Pie, contacto, redes, donaciones |
| `parts/navigation.html` | Menú principal (puede vivir dentro de `header.html` si no se reutiliza sola) |

Los bloques repetibles que antes eran includes PHP (`meditation-block`, `recitation-block`,
`mantra-block`) pasan a **patrones de bloques** (`patterns/*.php`, con cabecera de docblock — WordPress
los autorregistra desde WP 6.0 sin llamada manual a `register_block_pattern()`):

| Patrón | Uso |
|--------|-----|
| `patterns/meditation-block.php` | Bloque meditación semanal (reutilizable en Inicio y Práctica) |
| `patterns/recitation-block.php` | Recitación práctica de la comida (opcional; solo si se repite) |
| `patterns/mantra-block.php` | Mantra individual con audio (opcional; un patrón por mantra, cada instancia con su propio audio) |

Un patrón se usa cuando el contenido varía por instancia (p. ej. cada mantra tiene su propio audio) y
un editor necesita poder ajustarlo tras insertarlo. Un template part se usa cuando el contenido es
idéntico en todas las plantillas que lo incluyen (cabecera, pie).

---

## 5. Árbol del theme

Ruta en repo durante migración: `wordpress/wp-content/themes/camino-del-dharma/` (las rutas abajo son relativas a esa carpeta).

```
camino-del-dharma/
├── style.css              (obligatorio, metadata, no estilos — también lo exige un theme de bloques)
├── theme.json              ← fuente de verdad de tokens (paleta, tipografía, espaciado); genera
│                              Global Styles y alimenta Apariencia → Editor → Estilos
├── screenshot.png          (preview del theme en admin; práctica estándar WP)
├── functions.php          (bootstrap: theme supports, encolado de la hoja complementaria; si crece, ver inc/)
├── inc/                   (opcional; solo si el theme crece; en fase inicial todo en functions.php)
│   ├── setup.php
│   ├── enqueue.php
│   ├── helpers.php
│   └── security.php
├── templates/
│   ├── index.html          (obligatorio; fallback técnico de un theme de bloques)
│   ├── front-page.html
│   ├── page-comunidad.html
│   ├── page-linaje.html
│   ├── page-practica.html
│   ├── page-eventos.html
│   ├── page-galeria.html
│   ├── page-donaciones.html
│   ├── page-contacto.html
│   ├── page.html
│   ├── home.html            (listado del blog, /blog/)
│   ├── single.html          (entrada del blog, /blog/{slug}/)
│   ├── single-event.html   (si CPT event)
│   ├── archive-event.html  (si CPT event)
│   ├── 404.html
│   ├── archive-sangha.html (si CPT sangha)
│   └── single-sangha.html  (si CPT sangha)
├── parts/
│   ├── header.html
│   ├── footer.html
│   └── navigation.html
├── patterns/
│   ├── meditation-block.php
│   ├── recitation-block.php   (opcional)
│   └── mantra-block.php       (opcional)
└── assets/
    ├── css/
    │   └── main.css       ← hoja complementaria: layout, ritmo de lectura, componentes, estados/foco
    │                          (todo lo que Global Styles no expresa; §7)
    ├── js/
    │   └── main.js       ← navegación, accesibilidad; se encola en footer (no bloquea render); defer opcional vía script_loader_tag (14, 17)
    │                            NO se migra gallery.js: la galería pasa a bloque de Gutenberg (ADR 0021)
    ├── fonts/
    ├── icons/
    ├── images/
    └── favicon/
```

CPTs, taxonomías y roles **no** viven en este árbol: son responsabilidad de `camino-del-dharma-core`
(ADR 0024). El theme nunca registra dominio.

---

## 6. URL → plantilla

| Ruta | Archivo |
|------|---------|
| `/` | `templates/front-page.html` |
| `/comunidad/` | `templates/page-comunidad.html` |
| `/linaje/` | `templates/page-linaje.html` |
| `/practica/` | `templates/page-practica.html` |
| `/eventos/` | `templates/page-eventos.html` o `templates/archive-event.html` |
| `/eventos/{slug}/` | `templates/single-event.html` (si CPT event) |
| `/galeria/` | `templates/page-galeria.html` |
| `/donaciones/` | `templates/page-donaciones.html` |
| `/contacto/` | `templates/page-contacto.html` |
| `/blog/` | `templates/home.html` |
| `/blog/{slug}/` | `templates/single.html` |
| `/blog/tag/{slug}/` | jerarquía nativa (`taxonomy-post_tag.html`/`archive.html`/`index.html`); noindex por defecto (ADR 0031) |
| Cualquier otra | `templates/404.html` |

*(Si se implementa CPT sangha: `/sanghas/` → `archive-sangha.html`; `/sanghas/{slug}/` → `single-sangha.html`.)*

**Slugs de Pages:** Los slugs de las Pages en el admin deben coincidir exactamente con 11 (árbol de URLs) para que WordPress resuelva `page-{slug}.html` (p. ej. slug `comunidad` → `page-comunidad.html`). Evita crear "La comunidad" si WordPress asigna `comunidad-2`.

---

## 7. Estrategia CSS (ADR 0029)

| Archivo | Rol |
|---------|-----|
| `style.css` | **Obligatorio.** Solo cabecera del theme (Theme Name, Description, Version, Text Domain). WordPress no reconoce el theme sin este archivo. No usar para estilos; solo metadata. |
| `theme.json` | **Fuente de verdad de los tokens de diseño:** paleta (02), tipografías, tamaños, espaciado, anchos de layout. Genera las variables `--wp--preset--*` que consume el frontend y controla el panel **Estilos** del Editor de sitio — es la parte del CSS que un Administrador puede cambiar desde wp-admin sin tocar código. |
| `assets/css/main.css` | **Hoja complementaria:** layout de componentes, márgenes de lectura, ancho de columna, espaciado contemplativo, estados de foco/accesibilidad — todo lo que Global Styles no expresa. Se encola en `functions.php`. **No** es editable desde wp-admin; esto es normal en cualquier theme de bloques de WordPress (Global Styles cubre tokens, no lógica de layout), no una regresión de ADR 0029. |

Los componentes nunca usan hex directo ni tamaños fijos que dupliquen `theme.json`: consumen los
presets (`var(--wp--preset--color--brand-1)`, etc.) igual que antes consumían `var(--brand-1)` en
`:root`. La diferencia frente a ADR 0009 es de origen (theme.json en vez de una constante fija en
`main.css`), no de disciplina de naming.

**En `functions.php`:** encolar `main.css` dentro de `add_action( 'wp_enqueue_scripts', function() { ... } );`. Usar `get_template_directory_uri()` (este proyecto es parent theme cerrado; si hubiera child theme, en el child se usaría `get_stylesheet_directory_uri()`). Ejemplo: `wp_enqueue_style( 'camino-main', get_template_directory_uri() . '/assets/css/main.css', [], '1.0' );`. **Versionado:** Recomendación para evitar caché en deploy: usar versión dinámica (p. ej. `filemtime( get_template_directory() . '/assets/css/main.css' )`) en lugar de `'1.0'` fijo. `main.js` se encola con `wp_enqueue_script( ..., [], VERSION, true )` (quinto parámetro `true` = carga en footer, no bloquea render; **no es** el atributo `defer`; para `defer` explícito usar filtro `script_loader_tag`). `style.css` no se usa para estilos del sitio; solo para metadata del theme.

**Edición desde wp-admin:** la capacidad nativa `edit_theme_options` (que gobierna el acceso a
Apariencia → Editor → Estilos) es exclusiva de Administrador por defecto en WordPress. No se amplía a
Editor ni a otros roles editoriales — evita que un cambio de paleta o tipografía ocurra sin criterio de
diseño.

---

## 8. theme.json

- Paleta: solo colores de `02-identidad-corporativa` (`settings.color.palette`).
- Tipografías: según manual de marca (02) (`settings.typography.fontFamilies`, `fontSizes`).
- Espaciado: tamaños definidos para lectura cómoda y espaciado generoso (`settings.spacing.spacingSizes`), coherente con la experiencia contemplativa del sitio.
- `templateParts`: registra `header` y `footer` (§4) con su `area` (`header`/`footer`).
- `styles`: valores por defecto que replican exactamente los tokens actuales de `assets/css/main.css` — la migración inicial no cambia ni un valor; solo cambia el mecanismo por el que se puede editar después (ADR 0029).
- Sin bloques de animación en área de lectura.

**No reemplaza** layout complejo ni componentes; se complementa con `assets/css/main.css` (§7).

**Línea base de paridad:** el `theme.json` con el que se lanza Fase 3 se conserva como referencia
(commit dedicado o snapshot en `docs/migracion-static-wordpress.md`) para poder comparar cualquier
ajuste de Global Styles posterior contra el diseño validado en la maqueta estática.

---

## 9. Stack técnico (arquitectura)

- **Plantillas:** theme de bloques (Full Site Editing) — `templates/*.html` y `parts/*.html` con marcado de bloques núcleo (Query Loop, Post Content, Template Part, Group, etc.). No hay plantillas PHP para vistas (`front-page.php`, `page-*.php`, etc. quedan sustituidas).
- **Diseño:** `theme.json` (Global Styles) + `assets/css/main.css` complementario. Sin Tailwind, Bootstrap ni frameworks CSS.
- **Editor:** Gutenberg nativo con Editor de sitio habilitado. Contenido reutilizable vía patrones de bloques (`patterns/`) y template parts (`parts/`), no includes PHP. Video embed: bloque nativo de Gutenberg (Embed/YouTube/Vimeo); no se construye un sistema de bloques a medida. **Elementor:** no. **ACF Blocks:** no se introducen salvo que haya una necesidad estructural repetida que Gutenberg nativo no cubra. Si se añade ACF u otro builder, la estrategia CSS se mantiene (theme.json + un solo `main.css` complementario encolado).
- **Dominio:** CPTs, taxonomías y roles viven en `camino-del-dharma-core` (ADR 0024), nunca en el theme.

---

## 10. Compatibilidad con la maqueta estática

La estructura de plantillas replica la maqueta estática definida en Fase 2 (17): cada `templates/*.html` envuelve el bloque **Contenido de la entrada** (equivalente en bloques de `the_content()`) sobre el HTML previamente validado, sin rediseñar ni alterar la jerarquía de bloques. La migración a WordPress es una adaptación de HTML/CSS a plantillas de bloques, no una re-interpretación visual. WordPress no rediseña; solo renderiza contenido dinámico (ADR 0002, ADR 0029).

---

## 11. Mejores prácticas (WordPress) y anti-patrones a evitar

Esta sección define criterios de implementación para que el theme siga prácticas sólidas y evite soluciones frágiles o difíciles de mantener. Aplica en Fase 3 (WordPress) y debe respetar la maqueta de Fase 2 (17).

### 11.1 Principios de implementación

- **Plantillas limpias:** `templates/*.html` usan bloques núcleo (Query Loop, Post Content, Template Part, Group) sin markup ad hoc. Al ser HTML de bloques, no pueden contener lógica de negocio por diseño — eso es una propiedad a favor, no una limitación a rodear.
- **Lógica fuera de las plantillas:** Cualquier lógica no trivial (cálculos, transformaciones, condiciones complejas, armado de data) vive en `functions.php`, en archivos cargados desde `functions.php` (ver §11.2), o en `camino-del-dharma-core` si es lógica de dominio.
- **Una fuente de verdad por capa:** `theme.json` para tokens editables desde wp-admin; `assets/css/main.css` para todo lo demás (§7). No duplicar valores entre ambos ni en otros archivos.
- **Reutilización por partes y patrones:** Template parts (`parts/`) para cabecera y pie; patrones de bloques (`patterns/`) para bloques repetibles con contenido variable (p. ej. meditación, mantras) — ver §4.
- **Seguridad por defecto:** Output escapado y entradas sanitizadas (ver §11.4). No imprimir datos sin escape en el PHP que sí existe (`functions.php`, `inc/`).
- **Cero “builder lock-in”:** No introducir Elementor u otros builders de terceros. No introducir ACF Blocks salvo necesidad estructural repetida que Gutenberg nativo no cubra (ver §9). El Editor de sitio nativo (ADR 0029) no es un builder de terceros: es el mecanismo núcleo que WordPress ofrece para esto.
- **Hooks básicos del theme:** Registrar `add_theme_support( 'post-thumbnails' )`, `add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) )`, `register_nav_menus()` (el menú resultante se asigna al bloque Navigation en `parts/header.html`). `add_theme_support( 'title-tag' )` no aplica a un theme de bloques (el título ya lo controla `theme.json`/el bloque de plantilla). Opcional: `add_theme_support( 'editor-styles' )` y `add_editor_style( 'assets/css/main.css' )` para que el editor Gutenberg se aproxime al front.
- **Responsive:** El theme debe respetar el comportamiento responsive definido en `20-layout-principles`. No introducir breakpoints nuevos sin necesidad editorial.
- **Accesibilidad:** Mantener estructura semántica, navegación por teclado y contraste definidos en `19-accesibilidad-estandares`.

### 11.2 Organización del código (evitar “functions.php dios”)

`functions.php` debe ser un bootstrap (cargar archivos y registrar hooks). Evitar que se convierta en un “God file”.

Estructura recomendada si se necesita crecer:

- `inc/setup.php` — theme supports, menús, features
- `inc/enqueue.php` — encolado de `assets/css/main.css` y `assets/js/main.js`
- `inc/helpers.php` — helpers pequeños y seguros
- `inc/security.php` — nonces, sanitización, hardening básico

CPTs, taxonomías y roles no van aquí: son responsabilidad de `camino-del-dharma-core` (ADR 0024).

**Reglas:** `functions.php` solo incluye `require_once` + hooks de alto nivel. No crear “mini frameworks” ni contenedores de DI. No usar autoload Composer dentro del theme salvo decisión explícita de proyecto.

### 11.3 Anti-patrones comunes (prohibidos)

- **Todo en un solo archivo:** `functions.php` como lugar donde “vive todo”.
- **Lógica en plantillas de bloques:** intentar simular PHP dentro de `templates/*.html` con shortcodes o hacks; si hace falta lógica, es una señal de que se necesita un bloque dinámico o un patrón, no un parche en el HTML de la plantilla.
- **Hardcodear URLs:** no escribir rutas absolutas a mano ni en PHP (`home_url()`, `get_permalink()`) ni en bloques (usar los bloques de enlace/navegación nativos, que resuelven permalinks).
- **Hardcodear textos editoriales:** no fijar copy dentro de plantillas; usar contenido gestionado desde WordPress (09, content-source) a través del bloque Contenido de la entrada.
- **Inline styles:** no usar `style=""` para maquetación (los bloques núcleo ya evitan esto cuando se configuran vía `theme.json`).
- **CSS fragmentado sin control:** múltiples archivos de estilos complementarios sin necesidad. Un solo entry (`assets/css/main.css`) además de `theme.json`.
- **Plugins para resolver lo ya definido:** no añadir plugins para layout, navegación, grids o estilos que ya están definidos por 06, 14 y 20, ni para lo que el Editor de sitio nativo ya resuelve.
- **“Golden Hammer” de plugins/builders:** evitar builders o plugins pesados por costumbre.
- **Ampliar quién edita Global Styles:** no otorgar `edit_theme_options` a roles distintos de Administrador "por comodidad" (§7).

### 11.4 Seguridad mínima (obligatorio)

- **Escapar output:** usar `esc_html()`, `esc_attr()`, `esc_url()` según contexto. Escapar todas las salidas dinámicas cuando aplique.
- **Sanitizar input:** usar `sanitize_text_field()`, `sanitize_email()`, `wp_kses_post()` según corresponda.
- **Nonces:** cualquier formulario o acción que modifique estado debe usar nonce (`wp_nonce_field()` y validación con `check_admin_referer()` o `wp_verify_nonce()`).
- **No exponer data sensible:** no imprimir correos, teléfonos u otros datos sin intención editorial clara.

### 11.5 Performance y accesibilidad (alineado con Fase 2)

- **CSS/JS mínimos:** `theme.json` + `main.css` + `main.js` (si aplica), sin frameworks. Scripts con carga en footer (o defer vía `script_loader_tag`) y sin lógica compleja.
- **`gallery.js` no se migra.** En la maqueta estática construye el grid, la paginación y las miniaturas de `/galeria`. En WordPress esa función la asume el **bloque de galería de Gutenberg**, que además aporta el **lightbox nativo** ("Ampliar al hacer clic", WP 6.4+). No se implementa visor propio ni se instala plugin para ello: **ADR 0021**.
- **Accesibilidad preservada:** no romper focus visible, navegación por teclado, contraste y estructura semántica de la maqueta estática (19 y 20).
- **No animaciones decorativas en lectura:** coherente con la regla contemplativa (18).

### 11.6 Regla de coherencia con Fase 2

La traducción a WordPress es un cambio de motor y de mecanismo de plantillas, no de diseño:

- No cambiar orden de bloques definido en `06-wireframes`.
- No cambiar layout, grid y responsive definidos en `20-layout-principles`.
- No cambiar microcopy sin pasar por `09-ui-copy-sheet` y content-source.
- El `theme.json` inicial replica exactamente los tokens de la maqueta; solo cambios posteriores y deliberados vía Editor de sitio pueden diverger (ADR 0029), nunca la migración misma.

---

## 12. Filosofía técnica del theme

El theme prioriza simplicidad, claridad estructural y longevidad. Cada decisión técnica debe favorecer legibilidad, estabilidad y facilidad de mantenimiento antes que complejidad o efectos visuales. El desarrollo sigue principios KISS, YAGNI y separación de responsabilidades (plantillas de bloques renderizan; lógica en functions/inc; dominio en el plugin). Theme liviano; sin dependencia de frameworks; theme de bloques (Full Site Editing) basado en Gutenberg nativo; tokens de diseño editables desde wp-admin vía `theme.json`; CSS complementario propio y controlado; arquitectura clara y estable.

---

## Cierre

Este documento define la **estructura oficial de archivos del theme**: plantillas, partes reutilizables, mapeo URL → plantilla, compatibilidad con la maqueta estática (10), mejores prácticas y anti-patrones (11) y filosofía técnica (12). Alineado con 11 (árbol de URLs), 04 (mapa de pantallas), 03 (modelo de contenido), 05 (navegación), 17 (orden de implementación), 19 (accesibilidad), 20 (layout y responsive) y ADR 0029 (theme de bloques / Full Site Editing).

---

**Versión:** 2.3 — Calendario del mes: aviso táctil `.eventos-calendar-hint` según 09 y 19.
