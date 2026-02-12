# Camino del Dharma — Arquitectura CSS

**Capas, tokens y criterios de implementación de estilos**

Define cómo se organiza el CSS del sitio: relación entre theme.json, variables y `main.css`, criterios de especificidad y naming, y requisitos de accesibilidad aplicados al estilo.

**Depende de:** `02-identidad-corporativa`, `12-theme-file-structure`. **Referencia:** `15-assets-strategy` (tipografía, fuentes), `19-accesibilidad-estandares`, `20-layout-principles` (grid y responsive como principios)

---

## 1. Capas del sistema de estilos

| Capa | Origen | Rol |
|------|--------|-----|
| **Metadata del theme** | `style.css` | Solo cabecera (Theme Name, etc.). Sin reglas CSS. Obligatorio para WordPress. |
| **Tokens de diseño** | `theme.json` | Paleta, tipografías, espaciado, presets de bloques. Fuente de verdad para el editor. |
| **Variables y roles** | `assets/css/main.css` (o en :root al inicio) | Variables CSS (`--brand-*`, `--text`, `--link`, etc.) según 02. Roles semánticos para consistencia. |
| **Estilos de implementación** | `assets/css/main.css` | Layout, componentes, lectura, espaciado contemplativo. Un solo archivo; encolado en functions.php (12 §7). |

No hay frameworks (Tailwind, Bootstrap). No hay preprocesadores obligatorios; si se usa uno, la salida es un único bundle encolado. Si `main.css` crece, se permiten parciales en `assets/css/` que se concatenan o importan, manteniendo un único entry point encolado (estilo ITCSS opcional).

**Compatibilidad de rutas:** En Fase 2 (estático, p. ej. GitHub Pages) el entry point vive en `assets/css/main.css` en la raíz del repo; en WordPress, en `theme/assets/css/main.css`. Misma arquitectura, dos ubicaciones.

---

## 2. Estructura recomendada de main.css

Orden sugerido dentro de `main.css` (o de los parciales si se dividen después):

1. **Variables ( :root )**  
   Tokens de 02: `--brand-1` a `--brand-4` (este proyecto usa 4 colores de marca; 02), `--text`, `--link`, `--surface`, `--header-bg`, `--footer-bg`, `--font-display`, `--font-heading`, `--font-body`. Tipografía: **body** Inter (autohospedada en `assets/fonts/inter/`, 02 y 15); **display/headings** MarloweEscapade y Downtown para hero y títulos (02 y 15).

2. **Base / reset mínimo**  
   Box-sizing, márgenes básicos, tipografía base, enlaces. Prohibido reset agresivo (p. ej. `* { all: unset; }`). Se permite normalize ligero y box-sizing; solo lo necesario para consistencia.

3. **Layout**  
   Contenedores, grid o flex para secciones (hero, columnas “cómo practicamos”, footer). Ancho de columna de lectura objetivo 60 a 70ch y ritmo vertical consistente. No usar layout dependiente del DOM profundo (p. ej. `.site-header nav ul li a span`). Espaciado generoso (coherente con la configuración de espaciado en theme.json, definido en 12).

4. **Componentes**  
   Cabecera, pie, navegación, bloque de meditación, botones, formulario, tarjetas de evento. Nombres semánticos (p. ej. `.site-header`, `.meditation-block`, `.btn-primary`).

5. **Páginas específicas (si hace falta)**  
   Ajustes por template (front-page, page-contacto). Mantener al mínimo; preferir componentes reutilizables.

6. **Utilidades (opcional)**  
   Clases de ayuda puntuales (visually-hidden para accesibilidad, espaciado de margen). Pocas y documentadas.

7. **Estados y accesibilidad**  
   `:focus-visible`, estados de hover/active, contraste suficiente. Respetar `prefers-reduced-motion`. Criterios en 19.

---

## 3. Naming y especificidad

- **Naming:** Semántico y estable. Preferir nombres que describan función o componente (`.site-header`, `.event-card`) en lugar de apariencia. BEM u otra convención es opcional; lo importante es consistencia y baja especificidad.
- **Especificidad:** Mantenerla baja. Evitar anidaciones profundas e `!important`. Las variables y los roles de 02 reducen la necesidad de overrides.
- **IDs:** No usar para estilos; reservar para anclas y ARIA cuando aplique.
- **Atributos inline:** No estilizar por `[style]`; no inline styles en contenido editorial.

---

## 4. theme.json y main.css

- **theme.json** define colores, tipografías y espaciado que WordPress y el editor usan como presets. Debe alinearse con la paleta de 02.
- **main.css** usa las mismas decisiones (vía variables en `:root`, porque WordPress no comparte variables entre theme.json y CSS de forma automática) y añade layout, componentes y detalles que el editor no controla.
- Los valores de color y tipografía pueden existir como tokens tanto en theme.json como en `:root`. Regla: los componentes nunca usan hex directo; consumen roles semánticos vía variables (`var(--text)`, `var(--link)`, etc.).
- Los estilos del frontend no deben romper el estilo base de bloques Gutenberg (`.wp-block-*`). Cualquier override debe ser intencional y localizado. En Fase 2 no hay `.wp-block-*`; en Fase 3 no romperlos.

---

## 5. Grid system (implementación)

Implementación técnica del grid definido en `20-layout-principles` (§5):

- **Base:** Preferir CSS Grid para layout de columnas; Flexbox solo para alineación interna de componentes. Contenedor principal centrado que alberga cabecera, contenido y pie.
- **Tokens mínimos del grid (en :root):** `--container-max`, `--gutter`, `--section-gap`, `--grid-gap`; existen y se usan para consistencia; los valores se definen según 20 y contexto.
- **Contenedor:** Una clase (o contenedor semántico) que define el ancho máximo y el centro estructural. El ancho de lectura (60–70ch) vive dentro de él. El contenedor central define el eje del sitio; los bloques visuales pueden romper el ancho cuando el wireframe lo indique (hero, imágenes, citas).
- **Columnas:** Columnas reutilizables cuando el wireframe pida 2 columnas (p. ej. texto + imagen en proporciones 1/2–1/2 o 2/3–1/3). No crear grids distintos por página; un solo sistema.
- **Utilidades:** Si se usan clases de utilidad para columnas o anchos, que sean pocas y coherentes con el grid. Detalle de breakpoints y comportamiento en §6.

Este documento responde *cómo se codifica* lo que el layout define; la lógica espacial está en 20.

---

## 6. Responsive rules (implementación)

Implementación técnica del comportamiento responsive definido en `20-layout-principles` (§6).

El comportamiento responsive no rediseña la página; solo adapta proporciones y columnas.

- **Breakpoints:** Definir breakpoints claros (p. ej. móvil, tablet, desktop). Los breakpoints se declaran como custom properties (o al menos como constantes comentadas al inicio del archivo). Estrategia recomendada: mobile-first; el layout base es una columna; se añaden columnas o anchos cuando el viewport lo permite.
- **Contenedor y lectura:** En viewports pequeños el contenedor ocupa el ancho disponible con márgenes laterales suficientes. El ancho de lectura deja de fijarse en `ch` y pasa a fluido con márgenes cómodos.
- **Herramientas:** Uso de `clamp()`, `minmax()`, `min()`/`max()` cuando aporten legibilidad sin complejidad innecesaria.
- **Adaptación:** La estructura pasa de multi-columna a una sola columna; el orden de bloques no cambia. Menú y elementos interactivos adaptados a táctil (05, 19).

---

## 7. Accesibilidad en el CSS

- **Contraste:** Texto y controles deben cumplir WCAG 2.1 AA. Combinaciones en 02; verificar texto sobre `--brand-3` y `--brand-2` (19).
- **Focus:** Estilo visible para `:focus-visible` en enlaces, botones y controles de formulario. No eliminar outline sin reemplazo.
- **Reduced motion:** Respetar `prefers-reduced-motion`; reducir o eliminar animaciones cuando el usuario lo indique.
- **Tamaño de click/tap:** Áreas interactivas con tamaño adecuado, preferiblemente 44×44 px en controles principales, sin comprometer el ritmo contemplativo del layout.
- **Reflow/legibilidad:** Evitar layout que se rompa con zoom o cambio de tamaño de texto; criterios en 19.

Detalle completo en `19-accesibilidad-estandares`.

---

## 8. Reglas de no-hacer

- No añadir estilos en `style.css` (solo cabecera).
- No introducir frameworks CSS sin decisión explícita de proyecto.
- No usar !important para resolver conflictos de diseño; corregir especificidad o orden de carga.
- No hardcodear colores o tamaños de fuente que ya existen en 02 o theme.json; usar variables.

---

## Cierre

Este documento define la **arquitectura CSS oficial** del sitio: una capa de tokens (theme.json + variables), un solo archivo de estilos reales (main.css), naming semántico y criterios de accesibilidad aplicados al estilo. Alineado con 02 (identidad, paleta, tipografía), 12 (theme, encolado), 15 (tipografía y assets), 19 (accesibilidad) y 20 (grid y responsive).

---

**Versión:** 1.2
