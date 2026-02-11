# Camino del Dharma — Tendencias UX/UI aplicadas al sistema editorial

**Qué tendencias adoptar y cuáles evitar para proteger calma, claridad y acogida.**  
**Versión 1.1**

Este documento actúa como **filtro** (no como driver): refuerza lectura, accesibilidad y performance; evita personalización, ruido visual y comportamiento invasivo. Referencia obligatoria para validar la maqueta estática (véase `17-orden-implementacion`, Fase 2).

**Sobre artículos tipo "tendencias 2026":**  
Suelen mezclar tendencias reales (tokens, performance, accesibilidad) y empuje comercial. Para Camino del Dharma conviene quedarse con lo **estructural** y filtrar todo lo que compite con la orientación y la práctica.

**Regla práctica del proyecto:**

> Se adopta todo lo que mejora **lectura, claridad, accesibilidad y performance**.  
> Se evita todo lo que introduce **personalización, ruido visual o comportamiento invasivo**.

El sitio no es una app, ni una landing, ni un producto interactivo; es un **espacio de acogida** centrado en orientar hacia la práctica buddhista.

---

## 1. Principio rector

El diseño contemporáneo se mueve en dos direcciones:

- Interfaces espectaculares: 3D, animación, hiperpersonalización.
- Interfaces humanas: tipografía, ritmo, silencio, claridad.

Camino del Dharma pertenece deliberadamente al segundo grupo.

La prioridad es: **calma, claridad, coherencia** (Contenido_Web), **tipografía legible**, **espacios amplios**, **ritmo visual pausado**, performance y **coherencia visual a largo plazo**.

---

## 2. Filosofía de diseño: minimalismo como estructura, no como estética

Para Camino del Dharma el minimalismo no es estilo ni moda: es una **disciplina de edición visual** para proteger el contenido y la experiencia. El sitio no necesita *verse* minimalista; necesita **comportarse como un espacio de acogida**: estructura limpia, navegación simple, tipografía dominante, mucho aire, cero espectáculo. El ritmo visual es parte del espacio contemplativo. Sensación objetivo: calma, orientación, sala de práctica (no producto digital).

---

### 2.1 Principios

#### Minimalismo como sistema de decisiones

> El minimalismo no consiste en quitar cosas. Consiste en que **cada elemento tenga una función**.

En un sitio de comunidad las funciones reales son pocas y claras:

- orientarse
- conocer la comunidad
- llegar a la práctica
- contactar

Todo lo demás es ruido.

#### Objetivo de cada página evidente

| Página | Objetivo |
|--------|----------|
| Inicio | Orientar al visitante |
| La comunidad | Contextualizar quiénes somos |
| El linaje | Explicar la tradición |
| Práctica | Acercar a la meditación y actividades |
| Contacto | Facilitar el primer contacto |

### 2.2 Navegación y estructura

Menús de 4 a 6 elementos: decisión estructural correcta (ver `05-arquitectura-informacion-navegacion`).

- Inicio
- La comunidad
- El linaje
- Práctica
- Eventos (condicional)
- Contacto

Menos opciones = menos fricción cognitiva.

### 2.3 Tipografía + espacio en blanco como sistema

La **tipografía** es la voz; el **espacio en blanco** es el ritmo. No se necesitan gráficos ni recursos decorativos. Se necesitan: jerarquía, aire, continuidad visual.

**Contenido_Web:** "Diseño sobrio, con amplios espacios en blanco, tipografías legibles y un ritmo visual pausado."

### 2.4 Anti-patrones

| Riesgo | Regla para este proyecto |
|--------|---------------------------|
| **Minimalismo sin personalidad** | La identidad se sostiene en tipografía, ritmo, tono y composición. **No en efectos.** |
| **Simplificar y perder información** | **La claridad nunca puede sacrificar el acceso al contenido.** |
| **CTAs agresivos** | Contenido_Web: "evitando los llamados a la acción agresivos". |

### 2.5 Reglas operativas

| Regla | Contenido |
|-------|-----------|
| **1. Función evidente** | Cada elemento en pantalla debe cumplir una función evidente. |
| **2. Navegación corta** | Menú de 4 a 6 ítems. |
| **3. Contenido visual justificado** | Las imágenes acompañan el texto. Si no aportan sentido, mejor no usarlas. |
| **4. Mobile first** | Cuerpos cómodos, botones grandes, márgenes generosos. |
| **5. Minimalismo bien hecho** | Velocidad, legibilidad, estabilidad visual. CSS único, JS mínimo. |

### 2.6 Criterios verificables

| # | Criterio | Debe cumplirse |
|---|----------|----------------|
| 1 | **Tipografía** | Máximo 2 familias; según manual de marca; jerarquías fijas H1–H3. |
| 2 | **Color** | Ningún componente usa hex directo; solo roles semánticos. |
| 3 | **Lectura** | Ancho de columna objetivo (60–70ch); ritmo vertical consistente. |
| 4 | **Motion** | En páginas de contenido: cero animaciones decorativas; solo focus y hover. |
| 5 | **WordPress** | `theme.json` bloquea paleta y fuentes; el editor no habilita estilos libres. |

---

## 3. Tendencias: tabla de adopción

| Tendencia | Estado | Por qué entra o no entra | Implementación |
|-----------|--------|---------------------------|----------------|
| Design tokens (roles semánticos) | Adoptar | Consistencia, control editorial | `:root` + roles semánticos; `theme.json` bloquea paleta y tipografías |
| Performance-first | Adoptar | Lectura rápida, menos fricción | 1 CSS, JS mínimo con defer, imágenes con dimensiones |
| Accesibilidad-first | Adoptar | Lectura inclusiva; **Lluvia de ideas:** "Accesibilidad para personas con discapacidad visual (Sandra tiene información)" | Contraste AA, focus visible, headings correctos, teclado completo, reduced motion |
| Micro-interacciones | Adoptar con cuidado | Solo feedback, no espectáculo | Hover, focus, estados de botón, menú móvil |
| Dark mode | Opcional | Útil si no rompe contraste | `prefers-color-scheme` reasigna roles |
| Parallax, 3D, AR/VR | Evitar | Compite con calma y claridad | No aplica |
| Personalización por IA | Evitar | Rompe estabilidad y añade complejidad | No como sistema base |
| Popups y smart triggers | Evitar | Compiten con la orientación | Evitar |

---

## 4. Performance-first

**Checklist (maqueta estática):**

- 1 CSS principal.
- JS mínimo con `defer`.
- Imágenes optimizadas y con `width`/`height`.
- `loading="lazy"` donde aplique.
- Fuentes auto-hospedadas cuando sea posible.
- Sin frameworks pesados.

**Equivalente en WordPress:**

- Un único `style.css` o `theme.json` + estilos del theme.
- Scripts encolados solo si son necesarios; `defer` por defecto.
- Imágenes optimizadas desde el CMS; `width`/`height` en markup.
- Evitar plugins que inyecten CSS/JS masivo.

---

## 5. Accesibilidad (regla fija)

**Lluvia de ideas:** "Accesibilidad para personas con discapacidad visual (Sandra tiene información completa del tema)."

**Aplicación concreta:**

- Contraste real AA en: texto, links, botones.
- Navegación por teclado completa.
- `:focus-visible` claro.
- Alt text en imágenes.
- Jerarquía semántica correcta: h1 → h2 → h3 ordenados.
- Formularios con labels reales.
- Respeto a `prefers-reduced-motion`.
- Enlaces con estados claros.

---

## 6. Micro-interacciones funcionales

Solo donde **confirman una acción**.

**Dónde aplicarlas:** Hover de links, focus de inputs, estados de botones, menú móvil, validación de formularios.

**Dónde NO:** En lectura continua, en bloques de texto largos.

**Regla:** Si la animación no explica una acción, sobra.

---

## 7. Tendencias que NO aportan al proyecto

| Tendencia | Motivo |
|-----------|--------|
| AR/VR, 3D | Fuera de escala; distrae y castiga performance. |
| Parallax, efectos profundos | Compiten con la calma y la lectura. |
| Tipografía animada | Peligrosa para lectura. |
| Popups y smart triggers | Casi siempre compiten con la orientación. |

---

## 8. Checklist de maqueta estática

- [ ] 1 CSS principal; sin fragmentos dispersos.
- [ ] JS mínimo; todo con `defer`.
- [ ] Imágenes con `width` y `height`; optimizadas; `loading="lazy"` donde aplique.
- [ ] Fuentes auto-hospedadas; sin CDN innecesario.
- [ ] Tokens en `:root`: `--brand-*` y roles semánticos; componentes solo usan roles.
- [ ] Contraste AA; `:focus-visible` visible.
- [ ] Jerarquía de headings correcta; enlaces con estado claro.
- [ ] Micro-interacciones solo funcionales.
- [ ] Sin popups ni triggers invasivos por defecto.
- [ ] Sin animación en texto de lectura.

---

## 9. Equivalencia en WordPress

| Concepto | En el theme |
|----------|------------|
| Tokens y roles | `theme.json`: paleta y tipografías bloqueadas; `style.css` con `--brand-*` y roles. |
| Un CSS principal | `style.css` del theme; evitar múltiples hojas de plugins. |
| JS mínimo | Enqueue solo scripts necesarios; `defer`. |
| Imágenes | Responsive/optimizadas; `width`/`height` en markup. |
| Accesibilidad | Contraste y focus en CSS; headings y landmarks en templates. |

---

## 10. Conclusión

Para un espacio de acogida como Camino del Dharma:

- la claridad es identidad
- la tipografía es diseño
- el ritmo es experiencia
- la calma es prioridad

El diseño no exige más efectos. Exige más intención.

---

## Cierre

Este documento es el **filtro oficial de tendencias UX/UI** del proyecto: adopta lo que mejora lectura, claridad, accesibilidad y performance; evita lo que compite con la orientación y la calma. Está alineado con identidad (02), mapa de pantallas (04), arquitectura de navegación (05), theme (11) y orden de implementación (14). El checklist §8 se usa en Fase 2 de 14 antes de pasar a WordPress.

---

**Versión:** 1.1  
**Referencias:** `02-identidad-corporativa`, `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `12-theme-file-structure`, `17-orden-implementacion`, content-source (Contenido_Web, Lluvia de ideas)
