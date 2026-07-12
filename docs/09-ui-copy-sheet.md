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
- **Mantra (implementado):** OM AMI DEWA HRIH
- **Texto del mantra:** OM AMI DEWA HRIH
- **Descripción:** Este es el mantra corto de Amitābha, el Buda de la Luz Infinita. 
- **Leyenda del audio:** Recitación del mantra de Amitābha.
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
- **Sin eventos:** "No hay eventos programados en este momento."

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

### Mensaje de evento para WhatsApp

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

**Versión:** 1.2
