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
- **Pie** (identidad, contacto, redes, donaciones, Pausa Profunda)

Todo en Inicio debe llevar hacia práctica o contacto. Sin listas largas ni ruido.

---

## 2. La comunidad

**Función:** identidad, fundador, propósito.

Bloques:

- Cabecera
- Título / introducción
- Quiénes somos (texto)
- Fundador (biografía; divisor suave; enlace “Visitar Pausa Profunda” en tipografía menor; según Contenido_Web P2)
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

Bloques:

- Cabecera
- Título / introducción
- Meditación semanal (horario, modalidad, CTA “Participar” → WhatsApp)
- Talleres vivenciales, retiros (iniciación, meditación), vida comunitaria y celebraciones (Encuentro nacional, Vesak, conferencias; según Contenido_Web P4)
- Eventos (si hay vigentes: enlace a Eventos)
- Contacto
- Pie

---

## 5. Eventos especiales (condicional)

**Función:** mostrar solo cuando hay evento vigente (04, 05).

Bloques:

- Cabecera
- Título (ej. “Eventos” o “Próximos eventos”)
- **Calendario estático** (un mes; días con evento marcados; según 04)
- **Por cada evento (tarjeta):** etiqueta de **tipo de evento** (Taller, Retiro, Conferencia, Encuentro) encima del título; título; imagen (si aplica); fecha, lugar, modalidad; descripción; CTA “Inscribirme”. Separación clara entre tarjetas (p. ej. borde/`hr` entre eventos).
- **Una sola vez en la página:** enlaces “Práctica · Contacto” (bloque común al final del listado), no repetidos en cada tarjeta.
- Pie

**Maqueta estática:** clase `.evento-type` para la etiqueta; `.evento-card` por evento; `.eventos-card-divider` o `hr` entre tarjetas; `.eventos-section-links` para Práctica · Contacto.

**Estado sin eventos:** ocultar ítem menú o mostrar página con mensaje amable (ej. “No hay eventos programados en este momento”) y salida a Inicio/Práctica (05 §9).

---

## 6. Contacto

**Función:** formulario y puente directo. El formulario es un puente, no un proceso; mantiene el tono del sistema.

Bloques:

- Cabecera
- Título
- Formulario (Nombre, Correo, Mensaje) + botón “Enviar”
- Información de contacto (WhatsApp, correo si aplica)
- Enlace “Volver” a Inicio o Práctica
- Pie

---

## 7. Single evento (si se implementa)

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

## 8. 404

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
- **Pie:** presente en todas las pantallas (identidad, Personería, Contacto | redes, Pausa Profunda, redes sociales, contacto directo WhatsApp/correo, sostener la comunidad/donaciones; según Contenido_Web “Footer en todas las páginas”). Definido en 04.
- **Cabecera:** consistente; menú alineado con 05 (4–6 ítems, Eventos condicional).
- **Sin:** carruseles innecesarios, “lo más visto”, bloques de marketing. Experiencia sobria y orientada (01, 18).

---

## Cierre

Este documento define la **estructura en bloque** de cada pantalla para Camino del Dharma. Orden de bloques y función editorial; no layout pixel a pixel. **Coherente con content-source:** Contenido_Web (Páginas 1–6 y Footer) es la fuente canónica para nombres de secciones y orden de bloques; FOTOS PAGINA WEB (Pestañas 1–8) mapea imágenes por sección (16). Alineado con 04 (mapa de pantallas), 05 (navegación) y 09 (copy). La implementación en plantillas y partes corresponde a 12.

---

**Versión:** 1.3
