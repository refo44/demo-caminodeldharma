# Camino del Dharma — Accesibilidad (estándares)

**Documento único de estándares de accesibilidad del sitio.**  
**Versión 1.1**

Define estrategia, principios, reglas de diseño, HTML semántico, ARIA, contenido editorial, checklist de implementación y testing. Un solo documento operativo, alineado con WCAG 2.1/2.2 Level AA y con la naturaleza editorial del proyecto (no SaaS, no dashboards).

**Decisión arquitectónica:** Este documento consolida todos los estándares de accesibilidad del proyecto en una sola fuente normativa. No se crearán documentos separados (NFR, testing, content guidelines) salvo que el sistema crezca en complejidad (p. ej. múltiples equipos, design system grande, producto tipo SaaS). Cualquier PR que toque UI debe validar el checklist de implementación (§10) y el testing (§11) antes de merge.

**Depende de:** `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `17-orden-implementacion`, `18-tendencias-ux-ui-sistema-editorial`

---

## 1. Estrategia de accesibilidad

- **Compromiso del sitio:** Que cualquier persona pueda leer, orientarse y comprender el contenido sin fricción. Centrado en claridad, calma y legibilidad.
- **Referencia:** WCAG 2.1 Level AA (mínimo) o WCAG 2.2 Level AA (recomendado). [WCAG 2.1](https://www.w3.org/TR/WCAG21/) · [WCAG 2.2](https://www.w3.org/TR/WCAG22/) (W3C).
- **Filosofía:** Accesibilidad como parte del diseño y del contenido, no como capa posterior. Integrado con identidad (02), UX/UI (18) y orden de implementación (17).

**Lluvia de ideas:** "Accesibilidad para personas con discapacidad visual (información experta específica para el proyecto)."

---

## 2. Contexto: tipo de proyecto

Camino del Dharma es un sitio **editorial** y **comunitario**: contenido largo, navegación simple, experiencia contemplativa, sin interacción compleja ni dashboards. Por tanto:

- **Prioridad:** tipografía, contraste, estructura semántica, contenido claro.
- **No priorizar de forma general:** ARIA compleja, focus management avanzado, live regions innecesarias. ARIA solo cuando el HTML nativo no alcance (ver §8).

---

## 3. Principios base (WCAG)

| Principio | Objetivo |
|-----------|----------|
| **Perceptible** | La información y la interfaz son presentables de forma que la gente pueda percibirlas (alternativas de texto, contraste, control de audio, etc.). |
| **Operable** | La interfaz es operable (teclado, tiempo suficiente, sin convulsiones, navegable, modos de entrada). |
| **Comprensible** | La información y el uso de la interfaz son comprensibles (legible, predecible, asistencia en la entrada). |
| **Robusto** | El contenido es interpretable de forma fiable por agentes de usuario y tecnologías de asistencia. |

---

## 4. Usuarios a considerar

| Perfil | Impacto en decisiones |
|--------|------------------------|
| **Visual** | Ceguera, baja visión, daltonismo → semántica, labels, alt text, contraste, zoom. |
| **Auditivo** | Sordera total o parcial → subtítulos, transcripciones, no depender solo del audio. |
| **Motor** | Navegación sin ratón, baja precisión → teclado, targets grandes, orden de tabulación. |
| **Cognitivo** | Dislexia, TDAH, procesamiento lento → lenguaje claro, jerarquía simple, consistencia. |
| **Neurológico** | Fotosensibilidad, migrañas, fatiga → evitar parpadeo, reducir animación, respetar `prefers-reduced-motion`. |

---

## 5. Objetivo WCAG y referencia normativa

- **Objetivo:** Cumplir **Level AA** (todos los criterios A y AA). No se exige AAA como política general; se pueden aplicar criterios AAA puntualmente cuando sea razonable.
- **Referencia:** [WCAG 2.1](https://www.w3.org/TR/WCAG21/) (español: [REC-WCAG21-20250506](https://www.w3.org/TR/2025/REC-WCAG21-20250506/)). WCAG 2.2 amplía 2.1 con criterios adicionales y es alternativa de conformidad cuando la política exige 2.0.

---

## 6. Reglas de diseño accesible

- **Tipografía legible:** Tamaños cómodos, jerarquía clara (H1–H3), según manual de marca (02) y tendencias UX (17).
- **Contraste:** Mínimo AA (texto e imágenes de texto): texto normal 4.5:1, texto grande 3:1. Verificar combinaciones críticas (texto sobre fondos brand-3, brand-2). Componentes de interfaz: contraste no textual ≥ 3:1 cuando aplique.
- **Targets:** Áreas clicables suficientes para puntero/táctil; en móvil, botones y enlaces cómodos. Recomendación WCAG 2.2: apuntar a ~24×24 CSS px mínimo para controles interactivos cuando sea posible, sin romper el diseño editorial; el objetivo de diseño preferido sigue siendo targets cómodos cercanos a 44×44 cuando el layout lo permita.
- **Ritmo visual:** Espacio en blanco, ancho de línea de lectura (p. ej. 60–70ch), sin saturación.
- **Color:** No depender solo del color para transmitir significado (WCAG 1.4.1).
- **Lenguaje:** Claro y directo (alineado con `07-guia-voz-microcopy-ux`). Evitar jerga innecesaria.

### Reglas mínimas (alto impacto, bajo costo)

- **Skip link:** Enlace "Saltar al contenido" visible al recibir foco (primer elemento interactivo o muy próximo).
- **Idioma:** Atributo `lang` en `<html>`; consistencia de idioma en las páginas; `lang` en fragmentos en otro idioma si aplica.
- **Orden de foco:** Orden de tabulación lógico y predecible; no solo "navegable por teclado", sino secuencia que preserve el significado.
- **Focus visible:** No usar `outline: none` sin reemplazo (usar `:focus-visible` con estilo visible equivalente).
- **Reflujo y zoom:** Contenido usable al 200 % de zoom y en viewport equivalente a 320 px de ancho sin pérdida de información ni funcionalidad (WCAG 1.4.10).
- **Landmarks consistentes:** Una sola etiqueta `<main>` por página; `header`/`nav`/`main`/`footer` consistentes entre pantallas.
- **Headings sin saltos:** No saltar de H1 a H3 por estilo; la jerarquía de H1–H3 sigue el contenido, no el diseño.
- **Enlaces distinguibles:** Enlaces visualmente distinguibles del texto sin depender solo del color (subrayado o estilo equivalente).
- **Hover/focus coherentes:** Hover y foco deben existir y ser consistentes; el foco no puede ser menos visible que el hover.
- **Imágenes con texto:** Si una imagen contiene texto relevante, debe existir alternativa en HTML (caption o texto cercano).
- **Autocomplete en formularios:** Usar atributos `autocomplete` cuando aplique (nombre, email, etc.) para mejorar usabilidad real.
- **Mensajes de error claros:** Mensajes de error visibles, asociados al campo y que indiquen cómo corregir (no solo "campo inválido").
- **Respeto a zoom/tamaño de texto:** No bloquear zoom ni escalado del navegador; evitar layouts que dependan de `vh` rígido en móvil.
- **Contenido colapsable (menú móvil):** Los controles que abren o cierran navegación colapsable deben actualizar `aria-expanded` y mantener el contenido navegable por teclado cuando esté visible; el `<nav>` no se elimina del DOM, solo se oculta/muestra con atributos o CSS.

---

## 7. Dónde está el mayor impacto en este sitio

1. Contraste correcto  
2. Texto legible (tamaño, ritmo, ancho de línea)  
3. Navegación simple  
4. HTML semántico (headings, landmarks)  
5. Alt text bien escrito  
6. Formulario accesible (labels, mensajes de error)  
7. Focus visible  

---

## 8. HTML semántico y ARIA

### Principio base

1. **HTML semántico primero.** Usar elementos nativos (`button`, `a`, `input`, `select`, `dialog`, `nav`, `main`, `header`, `footer`, `section`, `h1`–`h6`) como primera opción. Landmarks: `<main>`, `<nav>`, `<header>`, `<footer>` para estructura navegable por lectores de pantalla.
2. **ARIA solo** para completar semántica, estado o relación cuando el HTML no alcance (componentes dinámicos, UI custom, estados que cambian sin recarga).
3. **No usar ARIA para “arreglar” markup malo.** Si existe un elemento nativo que resuelve el caso, se usa el nativo.

### Política de uso de ARIA

- El sistema debe utilizar elementos nativos como primera opción.
- ARIA se utilizará únicamente para:
  - Exponer **nombre accesible** cuando no hay texto visible.
  - Exponer **estado** (expanded, selected, pressed, invalid).
  - Exponer **relaciones** (controls, labelledby, describedby).
  - Exponer **anuncios dinámicos** (aria-live) cuando hay cambios relevantes.

### Cuándo NO usar ARIA

- No usar `div` con `role="button"` si puede ser un `button`.
- No agregar roles redundantes (p. ej. `role="button"` en un `button`).
- No usar `aria-hidden="true"` para ocultar contenido que debe ser accesible.
- No usar `aria-label` cuando ya existe un label visible adecuado (se prefiere texto visible).
- No inventar roles o combinaciones sin patrón conocido.
- No usar `role="presentation"` ni `aria-hidden="true"` en contenedores que incluyan elementos enfocables.
- No usar `tabindex` positivo (`tabindex="1"`, `2`, etc.); solo `0` y `-1` cuando haya una razón clara.
- No preferir `aria-label` cuando se pueda incluir texto en el DOM (visible o con `.visually-hidden`).
- No dejar SVG decorativos sin marcar: deben llevar `aria-hidden="true"` y `focusable="false"`.

### Reglas obligatorias

- **Todo elemento interactivo** debe tener nombre accesible. Orden de preferencia:
  1. Texto visible (preferido)
  2. Texto oculto con clase `.visually-hidden` (cuando el diseño no permita texto visible)
  3. `aria-labelledby` (cuando el nombre provenga de otro elemento)
  4. `aria-label` (último recurso, solo si no hay alternativa)
- **Estados dinámicos** deben reflejarse con atributos ARIA cuando aplique: `aria-expanded`, `aria-pressed`, `aria-selected`, `aria-current`, `aria-invalid`.
- **Errores de formularios:** `aria-describedby` apuntando al bloque de error/ayuda; `aria-invalid="true"` cuando hay error.
- **Contenido oculto accesible:** El contenido oculto visualmente que forma parte de la navegación o estructura accesible no debe eliminarse del DOM ni recrearse dinámicamente sin necesidad; se oculta o muestra con CSS o atributos, manteniendo su relación semántica.

> Nota de implementación: la clase `.visually-hidden` (o la equivalente `screen-reader-text` de WordPress) se define en `14-css-architecture`. El proyecto usa una sola convención para texto solo visible a tecnologías de asistencia.

### Patrones mínimos por componente

| Componente | Requisitos |
|------------|------------|
| **Icon button** (botón sin texto visible) | Proveer nombre accesible con texto en el DOM. Orden preferido: (1) `<span class="visually-hidden">…</span>`, (2) `aria-labelledby`, (3) `aria-label` solo si no hay alternativa. El icono/SVG debe ir con `aria-hidden="true"` y `focusable="false"`. |
| **Enlaces** | Texto del enlace descriptivo. Evitar "aquí", "ver más", "clic". |
| **Modal / Dialog** | Si es custom: `role="dialog"` (o `alertdialog` si interrumpe); `aria-modal="true"`; `aria-labelledby` obligatorio; `aria-describedby` si hay texto explicativo. Focus al abrir dentro del modal; focus trap; retorno del foco al cerrar. Si se usa `<dialog>` nativo, validar igual el comportamiento de foco y cierre por teclado (Esc). |
| **Dropdown / Menú** | Si es menú de navegación simple, preferir `nav` + enlaces y no convertirlo en "menú ARIA" si no hace falta. Si es dropdown interactivo: trigger con `aria-expanded` dinámico y `aria-controls` al panel. Teclado: Enter/Space abre, Esc cierra, flechas navegan si aplica. |
| **Tabs** | `role="tablist"`, `role="tab"`, `role="tabpanel"`; `aria-selected` y `tabindex` correctos; `aria-controls` y `aria-labelledby` consistentes. |
| **Alertas / mensajes dinámicos** | `aria-live="polite"` para info; `aria-live="assertive"` para errores críticos. Solo cambios relevantes. |
| **Formularios** | Label asociado al control; placeholder no sustituye label. El texto de error debe insertarse en el DOM (no solo cambiar estilos). Error: `aria-invalid="true"` y `aria-describedby="id-del-error"`. Campos requeridos: indicación visible y `aria-required="true"` si aplica. |

### Checklist de PR (ARIA)

- [ ] Todo icon button tiene nombre accesible usando texto en el DOM (`.visually-hidden`) o `aria-labelledby`; `aria-label` solo si no hay alternativa.
- [ ] Iconos decorativos dentro de controles tienen `aria-hidden="true"`.
- [ ] Controles con panel tienen `aria-expanded` y `aria-controls`.
- [ ] Errores de formulario usan `aria-invalid` y `aria-describedby`.
- [ ] No hay roles redundantes ni ARIA sustituyendo HTML nativo.
- [ ] Cambios dinámicos relevantes tienen `aria-live` cuando corresponde.

---

## 9. Reglas para contenido editorial

- **Headings:** H1–H3 ordenados; un H1 por página; jerarquía lógica.
- **Alt text:** En todas las imágenes informativas; descriptivo y conciso. Decorativas: `alt=""` o implementación que permita ignorarlas.
- **Decorativo (definición):** Se considera decorativo todo elemento visual que no aporta información ni funcionalidad adicional (iconos que acompañan texto ya claro, separadores, ornamentos).
- **Texto en imágenes:** No usar texto incrustado en imágenes como único medio para información esencial, salvo que sea decorativo o exista alternativa equivalente (p. ej. texto en HTML).
- **Enlaces:** Texto descriptivo (evitar "aquí", "más info" sin contexto). Abrir en nueva pestaña (`target="_blank"`) solo cuando sea necesario; si se usa, **debe indicarse** con texto visible o texto oculto accesible (p. ej. ícono + texto "se abre en nueva ventana").
- **Lenguaje:** Claro y directo; alineado con guía de voz (06).
- **Medios:** Subtítulos o transcripción cuando el contenido sea informativo (video/audio). No depender solo del audio para información esencial.

---

## 10. Checklist de implementación

- [ ] Skip link ("Saltar al contenido") visible al foco.
- [ ] Atributo `lang` en `<html>`; idioma consistente en la página.
- [ ] Navegación completa por teclado; orden de tabulación lógico.
- [ ] Focus visible (`:focus-visible`); no `outline: none` sin reemplazo.
- [ ] Contenido usable a 200 % de zoom y en 320 px de ancho (reflow).
- [ ] Imágenes con alt adecuado.
- [ ] Formularios con labels reales; errores enlazados (aria-describedby, aria-invalid).
- [ ] Sin animaciones agresivas; respeto a `prefers-reduced-motion`.
- [ ] Sin contenido que parpadee o destelle (riesgo neurológico).
- [ ] Contraste AA en texto, enlaces y botones.
- [ ] `<title>` único por página, descriptivo; no repetir "Inicio" genérico. Landmarks y headings correctos.

---

## 11. Testing básico

- **Mínimo obligatorio en PRs con UI:**
  - **Teclado:** Tab/Shift+Tab recorren todos los interactivos visibles sin perder foco ni saltar secciones relevantes.
  - **Focus visible:** El foco se ve claramente en enlaces, botones e inputs.
  - **Lighthouse:** Sin fallas críticas de accesibilidad en la vista tocada.
  - **Formularios (si se tocaron):** Label asociado, error visible y lectura correcta del error.

- **Navegación solo con teclado:** Recorrer todo el sitio sin ratón; sin trampas de foco; orden de tabulación lógico.
- **Revisión de contraste:** Herramientas automáticas o manual sobre combinaciones críticas.
- **Screen reader (opcional, recomendado en releases):** Prueba puntual con lector de pantalla para flujos clave (navegación, formulario de contacto).
- **Validación automática:** Lighthouse y, como apoyo complementario, axe (extensión) o WAVE. No como único criterio.
- **Páginas mínimas a testear:** Inicio (home), una página de contenido largo, página con formulario de contacto, página de eventos o listado (si aplica).

---

## 12. Relación con el resto del sistema

- **18-tendencias-ux-ui-sistema-editorial:** Incluye contraste AA, `prefers-reduced-motion`, `:focus-visible`, headings correctos, labels en formularios. Este doc (19) es el estándar formal de accesibilidad y extiende esa base con ARIA, contenido editorial y checklist.
- **02-identidad-corporativa:** Paleta y contraste; verificar AA con los colores de marca.
- **14-css-architecture:** Aplica en la capa de estilos: focus visible, contraste, tamaño táctil, `prefers-reduced-motion`; detalle en ese documento; criterio normativo aquí (19).
- **17-orden-implementacion:** El checklist (§10) y el testing (§11) se integran en la validación pre-lanzamiento (Fase 2 y checklist final).
- **07-guia-voz-microcopy-ux:** Relacionado con lenguaje claro y tono; las reglas de contenido accesible (§9) se alinean con la voz del sitio.

---

## Cierre

Este documento es el **estándar único de accesibilidad** del proyecto: estrategia, principios, diseño, HTML semántico, ARIA, contenido y testing en un solo lugar. Alineado con WCAG 2.1/2.2 Level AA y con la naturaleza editorial y contemplativa del sitio. No se crean documentos adicionales de accesibilidad; este doc es la referencia operativa.

---

**Versión:** 1.1  
**Referencias:** `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `17-orden-implementacion`, `18-tendencias-ux-ui-sistema-editorial`, lluvia de ideas de accesibilidad del proyecto. Estándar: [WCAG 2.1](https://www.w3.org/TR/WCAG21/) / [WCAG 2.2](https://www.w3.org/TR/WCAG22/) (W3C).
