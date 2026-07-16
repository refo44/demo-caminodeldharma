# Camino del Dharma — Sistema editorial

> **Estado: borrador en análisis**  
> Este documento **no es política editorial final**. Registra el marco en discusión para escribir, editar y presentar textos en el sitio. No debe citarse como lineamiento oficial hasta su revisión y aprobación.

Define **cómo habitan la pantalla** los distintos tipos de contenido: niveles de texto, formatos, estructura, extensión orientativa y reglas de presentación (listado vs. detalle).

**Complementa:** `01-plataforma-comunidad-plan` (Capa 6), `02-identidad-corporativa`, `03-wordpress-content-model`, `04-mapa-pantallas`, `06-wireframes`  
**Referencia:** `18-tendencias-ux-ui-sistema-editorial`, `19-accesibilidad-estandares`, `20-layout-principles`

La maqueta estática valida diseño; **no es insumo** de este marco.

---

## Relación con copy y voz

Este documento y los de copy **trabajan juntos**; no se sustituyen ni se duplican.

| Documento | Responsabilidad |
|-----------|-----------------|
| **`21-manual-voz-copywriting-editorial`** | **Documento rector de lenguaje.** Principios de escritura, ritmo, sintaxis, registro emocional, terminología buddhista, checklist de redacción (§17). Aplica a **todo** contenido editorial: blog, eventos, notas, descripciones. |
| `07-guia-voz-microcopy-ux` | Voz del sistema: atributos (sobria, acogedora, clara), personas gramaticales, CTAs, tono por sección. |
| `08-voice-dictionary` | Léxico permitido y prohibido. |
| `09-ui-copy-sheet` | Copy fijo de interfaz: botones, menús, estados, mensajes de compartir. |
| **`23-sistema-editorial` (este doc)** | Tipos de contenido, niveles de texto, estructura por formato, extensión orientativa, presentación listado/detalle, checklist de **pantalla** (§12). |

### Cómo se aplican en conjunto

Al publicar contenido editorial (p. ej. una entrada del blog):

1. **`23`** — define el **formato**: artículo breve o estándar, estructura con `h2`, extracto de una o dos oraciones, detalle con superficie de lectura.
2. **`21`** — define la **redacción**: orientar antes que atraer, decir menos pero mejor, ritmo pausado, checklist §17.
3. **`07` / `08`** — acotan **voz y léxico** en título, cuerpo y CTA.
4. **`09`** — aplica solo si hay copy de interfaz (Compartir, navegación).

El principio de extensión de este documento (§2, §4) **se apoya** en `21` §6.3 (*decir menos, pero mejor*): la longitud responde al tema, no a una meta de palabras, porque cada palabra debe justificar su presencia.

---

## 1. Niveles de texto

En el sitio conviven tres niveles claramente separados:

### 1.1 Contenido canónico

Textos fijos definidos en `content-source/.../Contenido_Web_Camino_del_Dharma`.

- Inicio, Comunidad, Linaje, Práctica, footer, CTAs canónicos.
- Se implementan **tal cual**; no se parafrasea ni se adapta sacrificando sentido o jerarquía.
- Inventario en `16-content-source-inventario`.

### 1.2 Contenido editorial

Textos que la comunidad produce o actualiza periódicamente.

- Entradas del blog.
- Descripciones de eventos.
- Notas de contexto, crónicas, actualizaciones.

- Estructura y extensión flexibles según propósito (este documento, §4 y siguientes).
- Redacción según `21-manual-voz-copywriting-editorial` (principios §6, ritmo §8, checklist §17) y voz del sistema (`07`, `08`).

### 1.3 Texto de sistema

Interfaz y estados del sitio.

- Navegación, botones, formularios, mensajes de error, estados vacíos.
- Definido en `09-ui-copy-sheet` y `07-guia-voz-microcopy-ux`.

**Este documento regula principalmente los niveles 1.2 y la presentación en pantalla del 1.1. El canónico no se altera; el sistema (1.3) se remite a los docs de copy.**

---

## 2. Principio rector

La extensión y la estructura responden a la **experiencia de lectura** y al **propósito editorial**, no a métricas arbitrarias ni convenciones genéricas de blogs o SEO.

La longitud **no es el eje** de la política editorial. Es una **consecuencia** de la claridad y de la intención del texto (`21` §6.3: *decir menos, pero mejor*).

---

## 3. Páginas institucionales (contenido canónico)

| Página | Reglas de presentación |
|--------|------------------------|
| Inicio | Hero, bloques en orden de Contenido_Web; meditación semanal como bloque fijo |
| La comunidad | Biografía del fundador con divisor suave; enlace Pausa Profunda en tipografía menor, nueva pestaña |
| El linaje | Texto + imágenes; jerarquía H1 → H2 sin saltos |
| Práctica | Bloques de profundización; meditación semanal repetida; superficies especiales (§7) |
| Contribuir | Marco de generosidad; datos bancarios; no transaccional |
| Contacto | Formulario + redes; sin lógica de CRM |

Copy exacto: `content-source`. Estructura de bloques: `06-wireframes`.

---

## 4. Blog (artículos)

Los artículos del blog tienen naturaleza **circunstancial** y estructura **flexible** (`04-mapa-pantallas`). Pueden ser breves o extensos según el propósito; el cuerpo siempre usa la **superficie de lectura** (`20-layout-principles`).

### 4.1 Principios de extensión

- Una **sola idea principal** por artículo.
- Lectura en **una sola sesión**, sin fatiga.
- **Evitar acumulación** de información.
- **Claridad sobre exhaustividad**.
- Extensión según **necesidades del tema**, no según meta de palabras.

### 4.2 Lo que se evita

- Clasificaciones basadas **únicamente** en longitud.
- Categorías que la arquitectura editorial **no reconoce** (`03`, `04`).
- Rangos motivados por SEO o convenciones de blogs contemporáneos.
- Máximos rígidos que contradigan la flexibilidad del proyecto.

### 4.3 Orientación de extensión (propuesta)

| Tipo de publicación | Extensión recomendada | Caracteres aprox.* | Lectura aprox.** |
|---------------------|------------------------|--------------------|------------------|
| **Artículo breve** | 150 – 400 palabras | 825 – 2 200 | 1 – 2 min |
| **Artículo estándar** | 400 – 1 000 palabras | 2 200 – 5 500 | 2 – 5 min |
| **Artículo en profundidad** | 1 000 – 2 000 palabras | 5 500 – 11 000 | 5 – 10 min |
| **Más de 2 000** | Evaluar formato | — | 10+ min |

\* ~5,5 caracteres por palabra en español.  
\** ~200 palabras por minuto.

Rangos **orientativos**. La longitud **nunca sustituye** al criterio editorial.

**Excepciones:** fuera del rango cuando el propósito lo exija (noticia muy breve, crónica de evento, desarrollo que requiera más espacio).

### 4.4 Criterios de extensión

- Lectura **fluida**, sin repeticiones.
- **> ~2 000 palabras:** revisar estructura más amplia, **serie de artículos** o **ensayo** (§5).

### 4.5 Estructura según extensión

| Extensión orientativa | Estructura sugerida |
|-----------------------|---------------------|
| Artículo breve (150 – 400) | Título + párrafos continuos |
| Artículo estándar (400 – 1 000) | Introducción + 2 – 3 secciones (`h2`) + cierre |
| Artículo en profundidad (1 000 – 2 000) | Introducción + secciones (`h2`/`h3`) + cierre |

La estructura responde al **tema**, no a un mínimo de palabras por sección. Headings: `19-accesibilidad-estandares` §9.

### 4.6 Presentación

**Listado (`/blog/`):**

- Título + extracto (una o dos oraciones; resume la idea principal).
- Sin acciones secundarias en el listado (`04-mapa-pantallas`).
- Imagen destacada opcional en card.

**Detalle (`/blog/{slug}/`):**

- Cuerpo completo en superficie de lectura.
- Botón «Compartir» solo aquí (`09-ui-copy-sheet` §6).
- Metadatos sociales: `03-wordpress-content-model` §8 (título, extracto ~155 caracteres, imagen, URL canónica).
- Copy al compartir por plataforma: templates en el detalle (`09-ui-copy-sheet` §6, mensaje de blog). Línea de contexto obligatoria: `Reflexión · [Autor] · Camino del Dharma`.

Redacción de título, extracto y cuerpo: `21-manual-voz-copywriting-editorial` (rector), `07-guia-voz-microcopy-ux`, `08-voice-dictionary`, `09-ui-copy-sheet` (copy de interfaz y compartir).

---

## 5. Artículo vs. ensayo

Los artículos y los ensayos **pueden compartir rangos de extensión**. La diferencia **no depende únicamente** del número de palabras, sino de la **intención editorial**:

| | Artículo | Ensayo |
|---|----------|--------|
| **Función** | Informar, divulgar o reflexionar sobre un tema concreto | Desarrollar una tesis o una argumentación sostenida |
| **Extensión típica** | 150 – 2 000 palabras (orientativo) | 800 – 3 000+ palabras (orientativo) |
| **Territorio** | Blog del sitio | **Pausa Profunda** (proyecto vinculado, enlace externo); casos puntuales en blog |

Un texto de 1 000 palabras puede ser artículo o ensayo breve. Lo define la **profundidad argumentativa**, no el conteo.

### 5.1 Ensayos en Pausa Profunda

- Desarrollo argumentativo de mayor alcance.
- No compite con el blog del sitio en función ni extensión habitual.
- Enlace desde footer y biografía del fundador; nueva pestaña.

---

## 6. Eventos

| Vista | Contenido editorial |
|-------|---------------------|
| Listado | Tipo, nombre, imagen, fecha, lugar, modalidad; «Compartir» en próximos |
| Detalle | Descripción completa, inscripción, compartir si aplica |

- Visible solo con `event_status = vigente` en menú y listados (`03` §3).
- Descripción: orientación y contexto; no funnel de marketing.
- Estructura de campos: `03-wordpress-content-model` §3.

---

## 7. Práctica (superficies especiales)

| Superficie | Reglas de presentación |
|------------|------------------------|
| Meditación semanal | Bloque fijo repetido (Inicio + Práctica); horario y modalidad canónicos |
| Recitación de la comida | Texto + enlace PDF; no sustituir PDF por HTML extenso |
| Mantras | Texto + audio local por mantra; extensible |
| Videos | Embed (YouTube/Vimeo); acompañan, no constituyen el centro del sitio (`03` §6) |
| Subpágina videos | Listado de embeds; enlace desde Práctica |

---

## 8. Galería, testimonios y donaciones

| Superficie | Reglas |
|------------|--------|
| **Galería** | Álbumes con título propio; grid + paginación por álbum; `alt` significativo |
| **Testimonios** | Bloque en página (por defecto); no archivo independiente salvo decisión explícita (`03` §3.2) |
| **Donaciones** | Texto ético de sostenimiento; datos bancarios; botón/enlace sin lógica de checkout |

---

## 9. Jerarquía visual y lectura

Reglas editoriales de pantalla (detalle en `02`, `19`, `20`):

- Un `h1` por página.
- `h2` y `h3` solo para estructura real; nunca como decoración.
- Superficie de lectura: ancho ~60–70ch (`20-layout-principles`).
- Sin interrupciones en flujo de lectura: popups, cajas promocionales, triggers invasivos (`18-tendencias-ux-ui-sistema-editorial`).
- Imágenes acompañan el texto; no compiten con él en bloques de lectura continua.

---

## 10. Idiomas

- Estructura del sitio igual en todos los idiomas activos.
- Contenido canónico y páginas fijas traducidas con criterio comunitario.
- Términos buddhistas: consistencia según `21-manual-voz-copywriting-editorial` §7.
- Un mismo texto no mezcla idiomas.

---

## 11. Qué decide la comunidad

El sistema editorial **no decide**:

- qué publicar;
- cuánto publicar;
- en qué orden;
- el tono doctrinal de las enseñanzas.

Solo asegura que el contenido **no se rompa al pasar por la web** y que cada tipo de texto tenga **forma y presentación coherentes**.

---

## 12. Checklist editorial (pantalla)

Antes de publicar contenido editorial, verificar:

1. ¿El texto respira en pantalla?
2. ¿Se distingue canónico, editorial y sistema?
3. ¿La estructura corresponde al tipo de contenido (blog, evento, práctica)?
4. ¿El listado y el detalle muestran lo correcto?
5. ¿Se entiende en móvil sin perder ritmo?
6. ¿Los extractos indican claramente de qué trata la pieza?

Checklist de **voz y redacción** (obligatorio para todo contenido editorial): `21-manual-voz-copywriting-editorial` §17.

Checklist de **pantalla** (este documento, §12): forma, estructura y presentación. **Ambos** aplican antes de publicar; ninguno sustituye al otro.

---

## 13. Pendiente de decisión

- [ ] Validar rangos de extensión del blog (§4.3) con quienes producirán contenido
- [ ] Confirmar artículo vs. ensayo por intención (§5)
- [ ] Definir subtítulo del blog (`09-ui-copy-sheet`)
- [x] Mensaje de blog al compartir por plataforma (`09-ui-copy-sheet` §6)
- [ ] Confirmar que el alcance de `21-manual-voz-copywriting-editorial` §2 cubre explícitamente entradas del blog
- [x] Añadir fila «Blog» en tono por sección (`07-guia-voz-microcopy-ux` §8)
- [ ] Aprobar o descartar este documento como lineamiento oficial

---

## Cierre

Este documento concentra el **sistema editorial** del sitio: niveles de texto, reglas por formato y orientación de extensión del blog. Sustituye el borrador `22-lineamientos-extension-blog` (abandonado).

---

**Versión:** 0.2 (borrador)  
**Estado:** en análisis — no es lineamiento final  
**Referencias de copy:** `07-guia-voz-microcopy-ux`, `08-voice-dictionary`, `09-ui-copy-sheet`, `21-manual-voz-copywriting-editorial`
