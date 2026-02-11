# Camino del Dharma — Arquitectura CSS

**Capas, tokens y criterios de implementación de estilos**

Define cómo se organiza el CSS del sitio: relación entre theme.json, variables y `main.css`, criterios de especificidad y naming, y requisitos de accesibilidad aplicados al estilo.

**Depende de:** `02-identidad-corporativa`, `12-theme-file-structure`. **Referencia:** `15-assets-strategy` (tipografía, fuentes), `19-accesibilidad-estandares`

---

## 1. Capas del sistema de estilos

| Capa | Origen | Rol |
|------|--------|-----|
| **Metadata del theme** | `style.css` | Solo cabecera (Theme Name, etc.). Sin reglas CSS. Obligatorio para WordPress. |
| **Tokens de diseño** | `theme.json` | Paleta, tipografías, espaciado, presets de bloques. Fuente de verdad para el editor. |
| **Variables y roles** | `assets/css/main.css` (o en :root al inicio) | Variables CSS (`--brand-*`, `--text`, `--link`, etc.) según 02. Roles semánticos para consistencia. |
| **Estilos de implementación** | `assets/css/main.css` | Layout, componentes, lectura, espaciado contemplativo. Un solo archivo; encolado en functions.php (12 §7). |

No hay frameworks (Tailwind, Bootstrap). No hay preprocesadores obligatorios; si se usa uno, la salida es un único bundle encolado. Si `main.css` crece, se permiten parciales en `assets/css/` que se concatenan o importan, manteniendo un único entry point encolado (estilo ITCSS opcional).

---

## 2. Estructura recomendada de main.css

Orden sugerido dentro de `main.css` (o de los parciales si se dividen después):

1. **Variables ( :root )**  
   Tokens de 02: `--brand-1` a `--brand-4` (este proyecto usa 4 colores de marca; 02), `--text`, `--link`, `--surface`, `--header-bg`, `--footer-bg`, `--font-display`, `--font-heading`, `--font-body`. Tipografía: body siempre en fuente neutra legible; display/headings solo para hero y títulos (MarloweEscapade, Downtown según 02 y 15).

2. **Base / reset mínimo**  
   Box-sizing, márgenes básicos, tipografía base, enlaces. Prohibido reset agresivo (p. ej. `* { all: unset; }`). Se permite normalize ligero y box-sizing; solo lo necesario para consistencia.

3. **Layout**  
   Contenedores, grid o flex para secciones (hero, columnas “cómo practicamos”, footer). Ancho de columna de lectura objetivo 60 a 70ch y ritmo vertical consistente. No usar layout dependiente del DOM profundo (p. ej. `.site-header nav ul li a span`). Espaciado generoso (coherente con 12 §8 theme.json).

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
- Los estilos del frontend no deben romper el estilo base de bloques Gutenberg (`.wp-block-*`). Cualquier override debe ser intencional y localizado.

---

## 5. Accesibilidad en el CSS

- **Contraste:** Texto y controles deben cumplir WCAG 2.1 AA. Combinaciones en 02; verificar texto sobre `--brand-3` y `--brand-2` (19).
- **Focus:** Estilo visible para `:focus-visible` en enlaces, botones y controles de formulario. No eliminar outline sin reemplazo.
- **Tamaño de click/tap:** Áreas interactivas con tamaño adecuado, preferiblemente 44×44 px en controles principales, sin comprometer el ritmo contemplativo del layout.

Detalle completo en `19-accesibilidad-estandares`.

---

## 6. Reglas de no-hacer

- No añadir estilos en `style.css` (solo cabecera).
- No introducir frameworks CSS sin decisión explícita de proyecto.
- No usar !important para resolver conflictos de diseño; corregir especificidad o orden de carga.
- No hardcodear colores o tamaños de fuente que ya existen en 02 o theme.json; usar variables.

---

## Cierre

Este documento define la **arquitectura CSS oficial** del sitio: una capa de tokens (theme.json + variables), un solo archivo de estilos reales (main.css), naming semántico y criterios de accesibilidad aplicados al estilo. Alineado con 02 (identidad, paleta, tipografía), 12 (theme, encolado), 15 (tipografía y assets) y 19 (accesibilidad).

---

**Versión:** 1.1
