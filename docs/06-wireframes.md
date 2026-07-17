# Camino del Dharma — Wireframes. Estructura de pantallas

**Jerarquía, bloques y flujo de lectura por pantalla**

Define la arquitectura visible de cada pantalla: bloques en orden vertical, jerarquía y función editorial. No define diseño visual ni componentes concretos; eso queda en 12 (theme) y 14 (CSS).

Los bloques definidos aquí se organizan dentro de un **grid editorial flexible** descrito en `20-layout-principles` (Principios de layout). En los wireframes se indica dónde hay 2 columnas, texto + imagen o ancho completo; la implementación del grid es responsabilidad de 14 (CSS). El grid no redefine el orden de bloques; solo organiza su disposición cuando se presentan en columnas.

**Depende de:** `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`. **Referencia:** `02-identidad-corporativa`, `09-ui-copy-sheet`, `18-tendencias-ux-ui-sistema-editorial`, `20-layout-principles` (grid). **Contenido canónico:** estructura y orden de bloques alineados con `content-source/.../Contenido_Web_Camino_del_Dharma` (Páginas 1–6 y Footer).

**Regla (18):** El objetivo de cada página debe ser evidente. Experiencia contemplativa: ritmo pausado, cero animaciones decorativas en área de lectura.

---

## 1. Inicio (front-page)

**Función editorial:** acoger y orientar hacia la práctica.

Bloques en orden vertical:

- **Cabecera** (logo, menú principal 4–6 ítems + subnav: Galería, Blog, Contribuir, Contacto; ver 05)
- **Hero** (mensaje de acogida, CTA “Practica con nosotros”; posible imagen)
- **Del blog** (opcional: fila de entradas destacadas + “Ver todas las entradas”; según implementación)
- **Comunidad** (breve: quiénes somos, enlace a La comunidad; según Contenido_Web “Un poco de nuestra comunidad”)
- **Cómo practicamos** (Estudio consciente, Práctica vivencial, Vida cotidiana; según Contenido_Web)
- **Linaje breve** (micro-bloque opcional: “Nuestro linaje”, 2 líneas, enlace a /linaje/)
- **Meditación semanal** (horario, modalidad, CTA “Participar” → WhatsApp)
- **Caminos de participación** (Iniciarse, Profundizar, Practicar en comunidad; según Contenido_Web)
- **Eventos** (si hay evento vigente: próximo evento + enlace a Eventos; si no: no mostrar bloque o mensaje amable)
- **Galería** (fila de imágenes + enlace “Ver galería completa”; según 04)
- **Contacto / Practica con nosotros** (CTA principal hacia Contacto o WhatsApp)
- **Pie** (identidad, contacto, redes, donaciones, Blog)

Todo en Inicio debe llevar hacia práctica o contacto. Sin listas largas ni ruido.

---

## 2. La comunidad

**Función:** identidad, fundador, propósito.

Bloques:

- Cabecera
- Título / introducción
- Quiénes somos (texto)
- Fundador (biografía; divisor suave; enlace “Visitar el blog” en tipografía menor)
- Experiencia y propósito
- CTA “Practica con nosotros” o equivalente
- Pie

---

## 3. El linaje

**Función:** marco espiritual y tradición.

Bloques:

- Cabecera
- Título / introducción
- Tradición viva (Mahāyāna, Chan, Tierra Pura según Contenido_Web)
- Enlaces secundarios: Práctica, La comunidad
- Pie

---

## 4. Práctica y actividades

**Función:** puertas de entrada reales a la práctica.

Bloques (orden en la maqueta):

- Cabecera
- Título / **Aprende en la práctica** (introducción)
- **Meditación semanal** (horario, modalidad, CTA “Participar” → WhatsApp)
- **Recitación práctica de la comida** (texto + enlace «Descargar PDF»)
- **Mantras para la práctica** (uno o más mantras; Amitābha y Guān Shì Yīn Púsà con texto breve y reproductor `<audio controls>`)
- **Caminos de profundización:** talleres vivenciales, retiro de iniciación, retiros de meditación
- **Videos y enseñanzas** (embeds + enlace «Ver más videos» → `/practica/videos/`)
- **Vida comunitaria y celebraciones** (Encuentro nacional, Vesak, conferencias; según Contenido_Web P4)
- Enlaces «Eventos · Contacto»
- Pie

**Maqueta estática:** `.recitation-section` (recitación comida); `.mantra-section` con `.mantra-text` y `.mantra-audio` por mantra. Audio en `assets/audio/amitabha.mp3` (19: controles visibles, `preload="metadata"`, sin autoplay).

**Nota editorial:** Recitación de la comida y mantras son **extensiones de la maqueta** no listadas en Contenido_Web canónico; se mantienen como recursos de práctica en `/practica/`.

---

## 5. Eventos especiales (condicional)

**Función:** mostrar solo cuando hay evento vigente (04, 05).

Bloques:

- Cabecera
- Título (ej. “Eventos” o “Próximos eventos”)
- **Calendario estático** (un mes; días con evento marcados; según 04)
- **Por cada evento próximo (tarjeta):** etiqueta de **tipo de evento** (Taller, Retiro, Conferencia, Encuentro) encima del título; título; imagen (si aplica); fecha, lugar, modalidad; descripción; CTA “Inscribirme”; acción secundaria “Compartir”. Separación clara entre tarjetas (p. ej. borde/`hr` entre eventos).
- **Una sola vez en la página:** enlaces “Práctica · Contacto” (bloque común al final del listado), no repetidos en cada tarjeta.
- Pie

**Maqueta estática:** clase `.evento-type` para la etiqueta; `.evento-card` por evento; `.eventos-card-divider` o `hr` entre tarjetas; `.eventos-section-links` para Práctica · Contacto. Cada evento próximo usa un botón `.share-trigger` con título, descripción y URL en atributos `data-share-*`. Los mensajes editoriales viven en `<template>`: WhatsApp (`data-share-whatsapp-template`), X (`data-share-x-template`) y Threads (`data-share-threads-template`); así conservan saltos de línea y datos específicos sin incrustar copy largo en JavaScript.

**Compartir:** el panel propio está disponible tanto en las tarjetas del listado como dentro de cada detalle y ofrece WhatsApp, Facebook, X, Threads y copiar enlace. No genera imágenes para Instagram. Los eventos con página propia comparten su detalle; si todavía no existe un detalle, se comparte el ancla estable de su tarjeta en `/eventos/`. En el blog, cada detalle de entrada usa `.share-trigger` con templates por plataforma (`data-share-whatsapp-template`, `data-share-x-template`, `data-share-threads-template`); ver `09-ui-copy-sheet` §6. Los eventos próximos incluyen botón **Añadir al calendario** (panel con Google Calendar, Outlook y `.ics`) y enlace **Compartir**; ver `09-ui-copy-sheet` §5 y §6.

**Estado sin eventos:** ocultar ítem menú o mostrar página con mensaje amable (ej. “No hay eventos programados en este momento”) y salida a Inicio/Práctica (05 §9).

---

## 6. Galería

**Función:** organizar la memoria visual de la comunidad sin presentar todas las imágenes como una secuencia única.

Bloques:

- Cabecera
- Título “Galería comunitaria” e introducción breve
- Uno o más álbumes, cada uno como `<section>` con título propio
- Grid de imágenes por álbum
- Paginación independiente por álbum (12 imágenes por página)
- Enlace “Volver al inicio”
- Pie

Los álbumes pueden responder a criterios editoriales distintos: **General** (memoria comunitaria; imágenes destacadas al inicio), año (`2023`, `2021`) u otro criterio editorial. Cada álbum conserva una ancla estable y su página activa en la URL mediante un parámetro propio, por ejemplo `?galeria-2023-page=2#galeria-2023`.

**Maqueta estática:** `#gallery-data` mantiene el inventario de imágenes y `#gallery-albums-data` define los álbumes demostrativos, sus títulos y rangos. `gallery.js` crea una sección `.gallery-album` por definición, con `.gallery-grid`, `.gallery-pagination` y una región de estado accesible independientes.

---

## 7. Contacto

**Función:** formulario y puente directo. El formulario es un puente, no un proceso; mantiene el tono del sistema.

Bloques:

- Cabecera
- Título
- Formulario (Nombre, Correo, Mensaje) + botón “Enviar”
- Información de contacto (WhatsApp, correo si aplica)
- Enlace “Volver” a Inicio o Práctica
- Pie

---

## 8. Single evento (si se implementa)

**Función:** detalle de un evento.

Bloques:

- Cabecera
- Etiqueta tipo de evento (Taller, Retiro, Conferencia, Encuentro) si se muestra en listado
- Imagen destacada del evento (si existe)
- Título del evento
- Fecha, lugar, descripción
- CTA inscripción o enlace
- Navegación: Volver a Eventos, Práctica, Contacto
- Pie

---

## 9. 404

**Función:** salida amable sin callejón sin salida (05 §9).

Bloques:

- Cabecera (reducida o completa)
- Mensaje claro (“Página no encontrada” o equivalente)
- Enlaces: “Volver al inicio”, “Explorar la comunidad”, “Practicar con nosotros” (hacia Contacto o práctica; reduce abandono)
- Pie

---

## Reglas transversales

- **Progresión:** La lectura de cada página debe llevar naturalmente hacia la práctica o el contacto. Refuerza la intención del sistema.
- **Bloques estructurales:** Cabecera y Pie (presentes en todas las pantallas; enmarcan la vista). **Bloques editoriales:** el resto (Hero, Comunidad, contenido por página); su orden define la jerarquía de lectura.
- **Pie:** presente en todas las pantallas (identidad, Personería, Contacto | redes, Blog, redes sociales, contacto directo WhatsApp/correo, sostener la comunidad/donaciones; según Contenido_Web “Footer en todas las páginas”). Definido en 04.
- **Cabecera:** consistente; menú alineado con 05 (4–6 ítems, Eventos condicional).
- **Sin:** carruseles innecesarios, “lo más visto”, bloques de marketing. Experiencia sobria y orientada (01, 18).

---

## Cierre

Este documento define la **estructura en bloque** de cada pantalla para Camino del Dharma. Orden de bloques y función editorial; no layout pixel a pixel. **Coherente con content-source:** Contenido_Web (Páginas 1–6 y Footer) es la fuente canónica para nombres de secciones y orden de bloques; FOTOS PAGINA WEB (Pestañas 1–8) mapea imágenes por sección (16). Alineado con 04 (mapa de pantallas), 05 (navegación) y 09 (copy). La implementación en plantillas y partes corresponde a 12.

---

**Versión:** 1.3
