# Camino del Dharma — User Journey

Describe cómo se mueve una persona real dentro del sitio. No es un flujo de conversión; es una serie de recorridos de encuentro con la comunidad y la práctica.

Sirve para validar: arquitectura de información, navegación, microcopy, jerarquía de pantallas.

**Depende de:** `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`. **Referencia:** `07-guia-voz-microcopy-ux`, `09-ui-copy-sheet`, `19-accesibilidad-estandares`

---

## 1. Persona llega y quiere conocer

**Objetivo:** entrar en la comunidad sin fricción.

**Recorrido:**

- Inicio
- → Lee el hero y el mensaje de la comunidad
- → "Practica con nosotros" o "Participar" (meditación)
- → Contacto o WhatsApp
- → Primer contacto

La mayoría de las personas llega primero al Inicio, donde ve una invitación clara; quienes entran por enlace directo o menú encuentran siempre menú y footer para orientarse.

---

## 2. Persona explora la comunidad

**Objetivo:** comprender quiénes son y qué ofrecen.

**Recorrido:**

- Inicio
- → La comunidad
- → Lee quiénes somos y biografía del fundador
- → "Visitar el blog" (si le interesa)
- → "Practica con nosotros" o Contacto

La persona entiende el contexto antes de actuar.

---

## 3. Persona quiere entender el linaje

**Objetivo:** saber en qué tradición se inscribe la comunidad.

**Recorrido:**

- Inicio o menú
- → El linaje
- → Lee tradición viva, Mahāyāna, Chan y Tierra Pura
- → Práctica o Contacto

---

## 4. Persona quiere practicar

**Objetivo:** unirse a la meditación o a un evento.

**Recorrido:**

- Inicio o menú
- → Práctica
- → Lee meditación semanal, talleres, retiros
- → "Participar" (WhatsApp)
- → O "Inscribirme" si hay evento vigente

---

## 5. Persona busca evento específico

**Objetivo:** inscribirse a un retiro o taller.

**Recorrido:**

- Menú o Inicio
- → Eventos (si visible)
- → Lee descripción, fecha, lugar
- → "Inscribirme"
- → Formulario externo o contacto

---

## 5b. Persona consulta el archivo de eventos

*(Añadido 2026-07-21: el recorrido §5 cubría solo eventos vigentes; el archivo de eventos finalizados sirve a intenciones distintas que no estaban descritas.)*

**Dos objetivos, ninguno es inscribirse:**

**a) Evaluar si la comunidad está activa**

- Llega desde Inicio, buscador o una mención externa
- → Eventos
- → Recorre el archivo buscando **ritmo**: cuántos, dónde, con qué frecuencia
- → Decide si vale la pena acercarse

**b) Saber si la comunidad llega a su ciudad**

- Busca «budismo» + su ciudad, o llega al sitio y quiere confirmarlo
- → Eventos
- → Busca su ciudad en el archivo
- → Encuentra encuentros previos allí (o no) y su frecuencia
- → Contacto o práctica en línea

**Requisitos de este recorrido:**

- Debe poder **escanearse**, no leerse: quien busca ritmo o ciudad no lee descripciones completas
- La ciudad debe ser visible **sin abrir cada evento**
- Distinción inequívoca respecto de lo vigente: nadie debe intentar asistir a algo terminado
- Agrupación por año con encabezados reales, para saltar con lector de pantalla (§9)

**Nota:** con encuentros presenciales 2–3 veces al año por ciudad y práctica semanal **en línea**, el archivo es la principal evidencia de presencia territorial del sitio. Ver `informes-seo/`.

---

## 6. Persona quiere contactar

**Objetivo:** escribir o recibir información.

**Recorrido:**

- Cualquier página
- → Contacto
- → Completa formulario (Nombre, Correo, Mensaje)
- → "Enviar"
- → Confirmación

---

## 7. Persona llega desde afuera

**Objetivo:** no perderse si llega por enlace directo.

**Recorrido:**

- Llega a una página desde Google o enlace
- → Lee el contenido
- → Ve menú y footer
- → Navega a Inicio, La comunidad o Práctica

Ninguna página es un callejón sin salida.

---

## 8. Regla de validación (Lluvia de ideas)

La idea de la Lluvia de ideas —"llevar de la mano" a la persona hacia formación o meditación— se interpreta como **orientación clara**, no como animación visual. El proyecto evita animaciones decorativas (06, 18); la “mano” es la estructura del sitio: menú, bloques y CTAs que guían sin perderse.

**Un recorrido es correcto si:**

1. La persona puede entender la comunidad en menos de tres clics.
2. Siempre hay un "siguiente" o un "volver".
3. Nunca se queda en una pantalla muerta.
4. Nunca siente que entró en una app o una tienda.

---

## 9. Accesibilidad del recorrido

El recorrido descrito en este documento debe ser posible para cualquier persona, independientemente de sus capacidades físicas, sensoriales o cognitivas.

La accesibilidad no define caminos alternos. Garantiza que el mismo recorrido pueda ser transitado por:

- Personas con discapacidad visual
- Personas con baja visión
- Personas que navegan con teclado
- Personas con dificultades auditivas
- Personas con carga cognitiva alta

Cada pantalla, acción y contenido debe poder ser comprendido y navegado sin barreras.

Los criterios técnicos y editoriales se definen en `19-accesibilidad-estandares`.

---

## Cierre

Este documento es la **guía de validación de recorridos** del sitio. Alineado con 04 (mapa de pantallas), 03 (modelo de contenido), 05 (navegación), 07 (voz), 08 (léxico), 09 (copy) y 19 (accesibilidad). No define diseño ni copy; valida que cada persona pueda moverse sin perderse, con la práctica como centro.

---

**Versión:** 1.1
