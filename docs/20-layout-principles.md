# Camino del Dharma — Principios de layout

Este documento cierra el sistema visual del sitio: define ancho de lectura, ritmo vertical, uso del blanco, relación tipografía/imagen, sistema de grid y comportamiento responsive. Se aplica en la maqueta estática (Fase 2) y se mantiene en WordPress (Fase 3).

**Depende de:** `02-identidad-corporativa`, `06-wireframes`, `14-css-architecture`  
**Referencia:** `17-orden-implementacion` (Fase 2), `18-tendencias-ux-ui-sistema-editorial`

---

## 1. Ancho de lectura

- **Objetivo:** 60–70 caracteres por línea (`ch`) en bloques de texto corrido.
- **Implementación:** Contenedor de contenido con `max-width` en `ch` o equivalente (p. ej. `65ch`).
- **Contenedor general:** El contenido textual vive dentro de un contenedor centrado. No usar texto corrido de borde a borde. Hero puede usar ancho completo; lectura no.
- **Excepciones:** Hero, pies de foto o citas pueden salirse del ancho estándar si el wireframe lo define.
- **Regla:** No líneas interminables; el sitio es sobrio y legible, no editorial denso.

---

## 2. Ritmo vertical

- **Consistencia:** Márgenes y padding entre secciones siguen una escala (p. ej. múltiplos de una base: `1rem`, `1.5rem`, `2rem`, `3rem`).
- **Respiración:** Espaciado generoso entre bloques; el contenido no va “pegado”. Coherente con el tono contemplativo (01, 07).
- **Jerarquía:** Más espacio antes/después de `h1`/hero que entre párrafos; el ritmo refuerza la jerarquía de 06-wireframes.
- **Regla:** Un solo sistema de espaciado en `main.css`; no valores arbitrarios por página.
- **Consistencia inter-pantalla:** El ritmo vertical debe ser consistente entre páginas (Inicio, Comunidad, Práctica, etc.), no redefinido por plantilla.

---

## 3. Uso del blanco

- **Blanco activo:** El espacio vacío es parte del diseño. No rellenar por rellenar.
- **Agrupación:** El blanco separa grupos lógicos (cabecera / hero / sección / pie). Agrupar lo que va junto; separar lo distinto.
- **Contraste con contenido:** Zonas de mayor densidad (texto, listas) se equilibran con zonas más abiertas (hero, entre secciones).
- **Pausa narrativa:** El blanco también define pausa narrativa entre bloques. Conecta con el carácter contemplativo del sitio.
- **Regla:** No sacrificar blanco para “meter más contenido”. Si algo no cabe, está en el contenido o en el wireframe, no en reducir respiración por defecto.

---

## 4. Relación tipografía / imagen

- **Tipografía primero:** La identidad es tipografía + color (02). Las imágenes apoyan, no dominan. En hero y bloques destacados, texto e imagen deben coexistir sin competir.
- **Evitar competencia:** No crear bloques donde imagen y texto compitan por protagonismo simultáneo. Muy común en sitios comunitarios; aquí se evita.
- **Proporción:** Cuando texto e imagen comparten bloque, definir proporción clara (50/50, 2/3–1/3, etc.) según 06-wireframes. No improvisar.
- **Nivel editorial:** El layout no debe imponer dramatismo visual. Coherente con el espíritu sobrio del sitio.
- **Legibilidad sobre imagen:** Si hay texto sobre imagen, asegurar contraste (overlay, sombra o zona sólida). Criterios de contraste en `19-accesibilidad-estandares`.
- **Alt y contexto:** Toda imagen con `alt` significativo; la relación texto/imagen también es semántica (contenido editorial en content-source).

---

## 5. Sistema de grid

- **Base:** El sitio se organiza sobre un grid simple que alinea todos los bloques principales.
- **Función:** Mantener coherencia horizontal entre cabecera, contenido y pie.
- **Centro estructural:** El contenido vive dentro de un contenedor alineado y centrado.
- **Lectura:** El ancho de lectura (60–70ch) se ubica dentro de ese contenedor, no directamente sobre el viewport. Estructura: viewport → grid/contenedor → columna de lectura.
- **Proporciones:** Cuando haya combinaciones texto/imagen, usar proporciones estables (1/2–1/2, 2/3–1/3), definidas en wireframes.
- **Regla:** No crear grids distintos por página. El sistema es único en todo el sitio.
- **Responsive:** El grid se simplifica en móvil (una sola columna), manteniendo el ritmo vertical y la legibilidad.

El grid no es visible; su función es sostener el orden y la calma del sitio.

---

## 6. Comportamiento responsive

- El sitio está diseñado como experiencia de lectura. La adaptación a pantallas pequeñas prioriza legibilidad, orden y calma.
- **Regla clave:** Responsive no significa rediseñar; significa mantener la misma experiencia con menos ancho. *En móvil no cambia el sitio. Cambia el espacio disponible.*
- La estructura base pasa de múltiples columnas a una sola columna vertical.
- El ancho de lectura deja de medirse en `ch` y pasa a ocupar el ancho disponible con márgenes cómodos.
- El ritmo vertical se mantiene o se amplía para facilitar la lectura táctil.
- El orden de los bloques no cambia respecto a los wireframes. El contenido más importante aparece primero; no se reordena para “optimizar marketing”.
- Las imágenes se adaptan al ancho del contenedor sin recortar información esencial.
- Elementos interactivos: fáciles de tocar, con suficiente espacio alrededor. Menú simplificado para navegación táctil, mismos accesos que en 05.
- **Contenido completo:** El contenido no se oculta en móvil. No se crean versiones reducidas de páginas ni se eliminan bloques importantes. El sitio sigue siendo el mismo territorio, solo que más estrecho.
- Texto sobre imagen en móvil debe priorizar legibilidad.

---

## 7. Resumen ejecutivo

| Principio           | Regla corta                                                  |
| ------------------- | ------------------------------------------------------------ |
| Ancho de lectura    | 60–70ch; contenedor centrado; hero puede usar ancho completo |
| Ritmo vertical      | Escala única; generoso; consistente entre páginas            |
| Uso del blanco      | Blanco activo; pausa narrativa; no rellenar                  |
| Tipografía / imagen | Tipografía primero; sin competencia; sin dramatismo          |
| Grid                | Un sistema; contenedor → lectura; no grid por página         |
| Responsive          | Simplificar, no rediseñar; mismo territorio, menos ancho     |

### Invariantes

Estos principios:

- se validan en la maqueta estática
- se mantienen en WordPress
- no se modifican por página ni por contenido

(17, §2.5 Invariantes de diseño)
