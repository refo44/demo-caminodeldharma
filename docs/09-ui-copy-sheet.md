# Camino del Dharma — UI Copy Sheet

**Repertorio de microcopy, navegación y sistema**

Fuente única de verdad para botones, menús, mensajes, formularios y estados. Criterios de voz en `07-guia-voz-microcopy-ux`; términos permitidos/prohibidos en `08-voice-dictionary`.

**Depende de:** `07-guia-voz-microcopy-ux`, `08-voice-dictionary`. **Referencia:** `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `06-wireframes`

**Regla central:** El sitio no vende. Invita a practicar.

---

## 1. Navegación global

### Menú principal (cabecera)

- Inicio
- La comunidad
- El linaje
- Práctica
- Eventos *(condicional: solo si hay evento vigente)*
- Contacto

### Footer

- Contacto
- Pausa Profunda *(enlace externo)*
- Facebook
- Instagram
- Sostener la comunidad / Donar

---

## 2. Inicio

### Hero

- **Título:** Transforma tu vida.
- **Subtítulo:** Asume los desafíos de la vida cotidiana mediante la práctica buddhista.
- **Botón:** Practica con nosotros

*Tono:* El título es deliberadamente sobrio (sin "YA", sin exclamación). Si se prefiere un tono más neutro y contemplativo, alternativa: **"La práctica en la vida cotidiana."**

### Meditación semanal

- **Título:** Meditación semanal en línea
- **Texto:** La meditación es el corazón de nuestra comunidad…
- **Horario:** Todos los lunes 7:30 p. m. (hora Colombia)
- **Modalidad:** Virtual (Zoom)
- **Botón:** Participar

### Caminos de participación

- Iniciarse
- Profundizar
- Practicar en comunidad

---

## 3. Práctica y actividades

### Recitación práctica de la comida

- **Título de sección:** Recitación práctica de la comida
- **Fuente:** Recitaciones para la práctica — Comunidad Buddhista Camino del Dharma
- **Subtítulo:** Práctica de la comida
- **Botón:** Descargar PDF

### Mantras para la práctica

- **Título de sección:** Mantras para la práctica
- **Mantra (implementado):** Amitābha · Guān Shì Yīn Púsà
- **Amitābha**
  - **Texto del mantra:** Amitābha
  - **Pronunciación:** A-MI-TA-BA · A · MI · TĀ · BHA (aproximación al sánscrito)
  - **Descripción:** Amitābha es el Buda de la Luz Infinita, una de las figuras centrales del Budismo Mahāyāna y, especialmente, de la tradición de la Tierra Pura. Su nombre expresa la luz ilimitada de la sabiduría que ilumina a todos los seres sin distinción.
  - **Origen:** Sánscrito · **Significado:** Luz Infinita (o Luz Inconmensurable) · **Forma:** Nombre del Buda Amitābha · **Escritura:** Alfabeto latino (Amitābha; Amitabha sin signos diacríticos)
  - **Leyenda del audio:** Recitación de Amitābha.
- **Guān Shì Yīn Púsà**
  - **Texto del mantra:** Guān Shì Yīn Púsà
  - **Pronunciación:** NA-MO GUAN SHI YIN PU-SA · NA-MO · GUAN · SHI · YIN · PU-SA
  - **Descripción:** Guān Shì Yīn Púsà es el nombre chino del Bodhisattva de la Compasión, conocido en sánscrito como Avalokiteśvara. En la tradición del Budismo Chan su recitación expresa confianza, refugio y el cultivo de la compasión hacia todos los seres.
  - **Origen:** Chino · **Significado:** "Homenaje al Bodhisattva Guān Shì Yīn" o "Me refugio en el Bodhisattva que escucha los sonidos (clamores) del mundo." · **Forma:** Pronunciación china (mandarín) · **Escritura:** Alfabeto latino (Namo Guan Shi Yin Pusa)
  - **Leyenda del audio:** Recitación de Guān Shì Yīn Púsà.
- **Extensible:** la sección admite uno o más mantras; cada uno con texto y reproductor de audio local.

*Nota:* No incluir enlaces externos de contexto teórico en esta sección.

---

## 4. Formulario de contacto

- **Campos:** Nombre, Correo electrónico, Mensaje
- **Botón:** Enviar
- **Confirmación:** "Mensaje enviado. Nos pondremos en contacto contigo."

---

## 5. Eventos

- **Botón:** Inscribirme
- **Acción secundaria por evento próximo:** Compartir
- **Calendario (eventos próximos):** Añadir al calendario — abre un panel con Google Calendar, Outlook, Apple Calendar y descarga del archivo `.ics`
- **Sin eventos:** "No hay eventos programados en este momento."

Cada evento próximo con fechas definidas expone un botón **Añadir al calendario** que abre un panel (mismo patrón que Compartir):

1. **Google Calendar** — abre el formulario del evento en el navegador.
2. **Outlook** — abre Outlook en el navegador con el evento prellenado.
3. **Apple Calendar** — abre el archivo `.ics` para importarlo en la app Calendario (iPhone, Mac).
4. **Descargar archivo .ics** — descarga el archivo para importarlo manualmente en cualquier calendario.

Los datos del evento viven en atributos `data-calendar-*` del botón; el archivo `.ics` vive en `eventos/ical/{slug}.ics`. Solo aplica a eventos próximos.

---

## 6. Compartir eventos y entradas del blog

- **Eventos próximos:** Botón “Compartir” en el listado y en la página de detalle cuando exista.
- **Blog:** Botón “Compartir” únicamente dentro de la página de detalle de cada entrada.
- **Título del panel:** Compartir
- **Opciones:** WhatsApp, Facebook, X, Threads, Copiar enlace
- **Cierre accesible:** Cerrar opciones para compartir
- **Confirmación de copia:** Enlace copiado.
- **Fallback de copia:** No fue posible copiar automáticamente. Copia este enlace: [URL]

El panel usa lenguaje funcional y sobrio. No incluye contadores, invitaciones promocionales ni SDKs de seguimiento.

### Mensaje de evento al compartir

Cada evento próximo define copy de compartir **por plataforma**, en `<template>` asociados al botón `.share-trigger` mediante:

- `data-share-whatsapp-template`
- `data-share-x-template`
- `data-share-threads-template`

Además: `data-share-title`, `data-share-description` (extracto social / meta tags) y `data-share-url`.

**Principio:** el texto prellenado es un **borrador editable**. Quien comparte puede añadir su mensaje, tags o reemplazarlo por completo.

**Atribución obligatoria (X y Threads):** la primera línea identifica la pieza como invitación de la comunidad, no como anuncio personal de quien comparte.

Formato fijo de la línea de contexto:

`[Tipo de evento] · Camino del Dharma`

Donde `[Tipo de evento]` es la etiqueta editorial del encuentro: Encuentro, Taller, Retiro o Conferencia.

#### WhatsApp (eventos)

Los eventos usan un mensaje editorial propio, no la combinación genérica título + URL. Orden estándar:

1. Apertura personal, válida para un contacto o un grupo: “Comparto esta invitación de Camino del Dharma:”.
2. Invitación y nombre del evento.
3. Propósito en uno o dos párrafos breves.
4. Fecha, hora, lugar, aporte y modalidad según corresponda.
5. Qué incluye, únicamente cuando sea relevante.
6. Disponibilidad de cupos.
7. Un único enlace oficial del evento, donde se encuentra la acción de inscripción.
8. Cierre breve relacionado con la práctica.

Se permiten únicamente emojis funcionales para facilitar el escaneo (`📅`, `🕘`, `📍`, `💰`). No se usan emojis decorativos en saludos, títulos, cupos ni cierres. Las inclusiones emplean viñetas (`•`). Los datos ausentes se omiten; no se muestran etiquetas vacías.

**Vista previa en WhatsApp:** El mensaje contiene una sola URL y debe ser la página pública del evento. No se incluye la URL directa de Google Forms u otro formulario externo, porque WhatsApp podría usarla para generar la vista previa. La página del evento contiene el botón de inscripción.

#### X y Threads (eventos)

1. Línea de contexto: `[Tipo] · Camino del Dharma`
2. Nombre del evento
3. **X:** fecha y lugar escaneables (emojis funcionales `📅`, `📍`); dejar ~160 caracteres libres para comentario personal
4. **Threads:** propósito breve + fecha y lugar si cabe (~500 caracteres totales con URL)

La URL **no** va dentro del template; `share.js` la añade en el intent. No usar `Comparto…` en templates de X/Threads (lo añade quien comparte si lo desea).

**Implementación estática:** ver `eventos/index.html` (Encuentro Nacional 2026) como referencia.

#### Por plataforma (eventos)

| Plataforma | Qué envía el botón | Copy del sitio |
|------------|-------------------|----------------|
| **WhatsApp** | Template completo (incluye URL) | Invitación editorial completa |
| **Facebook** | Solo URL | Tarjeta desde meta tags OG |
| **X** | Template (sin URL) + URL en intent | Contexto + nombre + fecha/lugar |
| **Threads** | Template (sin URL) + URL en intent | Contexto + nombre + propósito + datos |
| **Copiar enlace** | Solo URL | Tarjeta desde meta tags OG |

### Mensaje de blog al compartir

Cada entrada del blog define copy de compartir **por plataforma**, en `<template>` asociados al botón `.share-trigger` mediante:

- `data-share-whatsapp-template`
- `data-share-x-template`
- `data-share-threads-template`

Además: `data-share-title`, `data-share-description` (extracto social / meta tags) y `data-share-url`.

**Principio:** el texto prellenado es un **borrador editable**. Quien comparte puede añadir su mensaje, tags o reemplazarlo por completo antes de publicar. El sitio no impone hashtags ni frases promocionales.

**Atribución obligatoria:** la primera línea identifica la pieza como reflexión publicada, con autor y fuente. Evita que el título suelto se lea como opinión personal de quien comparte.

Formato fijo de la línea de contexto:

`Reflexión · [Autor] · Camino del Dharma`

No usar `Comparto…` en el borrador del sitio (lo añade quien comparte si lo desea).

#### Por plataforma

| Plataforma | Qué envía el botón | Copy del sitio | Editable por quien comparte |
|------------|-------------------|----------------|-----------------------------|
| **WhatsApp** | Template completo (incluye URL) | Contexto + título + subtítulo + URL | Sí |
| **Facebook** | Solo URL | Tarjeta desde meta tags OG | Escribe su propio post |
| **X** | Template (sin URL) + URL en intent | Contexto + título; ~160 caracteres libres para lo personal | Sí |
| **Threads** | Template (sin URL) + URL en intent | Contexto + título + subtítulo | Sí |
| **Copiar enlace** | Solo URL | Tarjeta desde meta tags OG | Control total al pegar |

#### Estructura del template WhatsApp (blog)

1. Línea de contexto: `Reflexión · [Autor] · Camino del Dharma`
2. Título del artículo
3. Subtítulo (si existe)
4. URL única del artículo

Sin emojis, sin cierre editorial, sin hashtags. Mensaje breve: orienta sin ocupar todo el espacio del chat.

#### Estructura de templates X y Threads (blog)

1. Línea de contexto
2. Título
3. Subtítulo solo en Threads si cabe dentro del límite (~500 caracteres totales con URL)

La URL **no** va dentro del template; `share.js` la añade en el intent. En X, priorizar espacio libre para comentario personal (~280 caracteres totales con URL).

#### Extracto social (meta tags)

`og:description` y `twitter:description` usan el extracto editorial del artículo (~155 caracteres): subtítulo + autor + tema. Fuente para la vista previa cuando se pega solo el enlace (Facebook, WhatsApp, copiar enlace).

**Vista previa en WhatsApp (blog):** una sola URL del artículo; la vista previa sale de los metadatos del sitio.

**Implementación estática:** ver `blog/sangha-refugio-hiperconexion/index.html` como referencia. Detalle técnico en `06-wireframes` y `assets/js/share.js`.

---

## 7. Donaciones

- **Título:** Sostener la comunidad
- **Texto:** La Comunidad Buddhista Camino del Dharma se sostiene gracias a la práctica, la participación y la generosidad consciente…
- **Botón:** Donar (o enlace a datos bancarios)

### Datos bancarios (footer)

- Titular: Camino del Dharma
- Banco: Banco Popular
- Tipo: Ahorros
- Cuenta: 220065151425
- NIT: 901333226

---

## 8. Estados y errores

| Situación | Texto |
|-----------|-------|
| Sin eventos | "No hay eventos programados en este momento." |
| Error envío | "No fue posible enviar el mensaje. Intenta de nuevo o escríbenos por WhatsApp." |
| 404 | "Esta página no existe." |
| Error técnico | "No fue posible mostrar este contenido." |

---

## 9. Enlaces externos

- **Pausa Profunda:** Abre en nueva pestaña
- **Redes sociales:** Facebook, Instagram
- **WhatsApp:** +57 320 662 7608
- **Correo:** caminodeldharma1@gmail.com

---

## 10. Accesibilidad

**Alt text:** Describir imágenes de forma clara (comunidad, buda, meditación, etc.). Imágenes decorativas: `alt=""`.

**Avisos:** "Abre en nueva pestaña" para enlaces externos.

Criterios completos en `19-accesibilidad-estandares`.

---

**Versión:** 1.6
