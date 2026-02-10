# Camino del Dharma — WordPress Content Model

**Versión 2.0**

Modelo de contenido para la web de la Comunidad Buddhista Camino del Dharma. Basado en Contenido_Web_Camino_del_Dharma.docx y Lluvia de ideas.

**Depende de:** `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `04-mapa-pantallas`

---

## 1. Esquema general

### Custom Post Types

| CPT (key) | Label ES | Slug | Uso principal |
|-----------|----------|------|---------------|
| page | Páginas | por página | Inicio, Comunidad, Linaje, Práctica, Eventos, Contacto |
| event | Eventos | /eventos/ | Eventos especiales vigentes (retiros, talleres, Vesak, etc.) |
| sangha | Sanghas | /sanghas/ | Contacto por sangha (Lluvia de ideas: conectar con cada sangha) |
| testimonial | Testimonios | /testimonios/ o bloque | Incluir testimonios (Lluvia de ideas) |

### Contenido fijo vs. dinámico

| Tipo | Gestión |
|------|---------|
| Páginas estáticas | page (Inicio, Comunidad, Linaje, Práctica, Contacto) |
| Eventos | event (visible solo cuando hay evento vigente); **cronograma** = listado/archive de eventos |
| Meditación semanal | Bloque fijo en Inicio y Práctica (Lunes 7:30 p.m., Zoom) |
| Formulario de contacto | Página Contacto |
| **Videos** | Embed YouTube/Vimeo en páginas y bloques (conferencias, enseñanzas, indicaciones para meditar) |
| **Cómo hacer parte / formación** | Página o sección: espacios de formación, alcance y propósito de cada uno (Lluvia de ideas) |

---

## 2. Campos por página

### Inicio (front-page)

- Hero: título, subtítulo, botón principal
- Bloque comunidad (texto)
- Cómo practicamos (3 columnas: Estudio consciente, Práctica vivencial, Vida cotidiana)
- Meditación semanal (texto, horario, botón Participar → WhatsApp)
- Caminos de participación (Iniciarse, Profundizar, Practicar en comunidad)

### Comunidad (page)

- Quiénes somos (texto)
- Fundador: Venerable Maestro Zheng Gong (biografía, foto, enlace Pausa Profunda)
- Experiencia y propósito (texto)

### Linaje (page)

- Tradición viva (texto)
- Mahāyāna (texto)
- Chan y Tierra Pura (texto + imágenes)
- Práctica en contexto actual (texto)

### Práctica (page)

- Meditación semanal (bloque repetido)
- Talleres vivenciales
- Retiro de iniciación
- Retiros de meditación
- Vida comunitaria (Encuentro nacional, Vesak, Conferencias)

### Eventos (page o archive-event)

- **Condicional:** visible solo cuando hay evento vigente
- Campos: nombre, fecha, lugar, modalidad, descripción, botón Inscribirme

### Contacto (page)

- Formulario: Nombre, Correo, Mensaje
- Botón: Enviar

---

## 3. Event (Custom Post Type)

| Campo | Tipo | Uso |
|-------|------|-----|
| event_name | text | Nombre del evento |
| event_date | date | Fecha |
| event_place | text | Lugar |
| event_modality | text | Presencial / Virtual |
| event_description | WYSIWYG | Descripción, sentido, a quién va dirigido |
| event_signup_url | url | Enlace inscripción |
| event_status | select | vigente / finalizado / cancelado |
| event_signup_payment | url o boolean | Inscripción/pagos por la web (Lluvia de ideas: gestión de eventos) |

**Regla:** Solo eventos con `event_status = vigente` aparecen en la página 5.

**Cronograma de eventos (Lluvia de ideas):** Listado/archive de eventos con fechas; puede ser la misma vista `/eventos/` mostrando todos los eventos con filtro o vista de calendario.

---

## 4. Taxonomías

| Key | Label ES | Tipo | Aplica a |
|-----|----------|------|----------|
| event_type | Tipo de evento | Jerárquica | event |

Valores posibles: Retiro, Taller, Celebración (Vesak, etc.), Conferencia, Encuentro nacional.

---

## 5. Plantillas mínimas

- `front-page.php` — Inicio
- `page-comunidad.php` — La comunidad
- `page-linaje.php` — El linaje
- `page-practica.php` — Práctica y actividades
- `page-eventos.php` o `archive-event.php` — Eventos (condicional)
- `page-contacto.php` — Contacto
- `single-event.php` — Evento individual (si aplica)
- `page.php` — Fallback para páginas

---

## 6. Videos (YouTube / Vimeo)

- **Embed:** Las páginas y bloques deben permitir incrustar videos por URL (YouTube, Vimeo).
- **Uso:** Conferencias, enseñanzas del maestro, indicaciones para meditar, canal de YouTube conectado a la página.
- **Lluvia de ideas:** "Espacio para subir videos de las conferencias, conectar el canal de youtube a la página", "Video sobre indicaciones para meditar".

Implementación: bloque o campo de tipo «video embed» (URL); editor no sube archivo de video, solo pega enlace.

---

## 7. Integraciones externas

| Recurso | Implementación |
|---------|----------------|
| Meditación semanal | Botón "Participar" → WhatsApp (3206627608) |
| **WhatsApp** | Link en cabecera/footer (Lluvia de ideas: "Link para ir al whatsapp de Camino del Dharma") |
| Formulario contacto | Envío a caminodeldharma1@gmail.com o plugin |
| **Donaciones** | Botón + datos bancarios en footer (Banco Popular, cuenta 220065151425) — Lluvia de ideas: "Incluir botón de donaciones" |
| Pausa Profunda | Enlace externo, nueva pestaña |
| Redes sociales | Facebook, Instagram en footer |

---

## 8. Principio rector

Todo en este modelo existe para: **orientar, inspirar confianza y facilitar el primer contacto con la práctica buddhista**. No hay capas de marketing ni funnels. Solo acogida y claridad.

---

## Referencia: Lluvia de ideas cubierta en este modelo

| Idea | Dónde |
|------|--------|
| Cronograma de eventos | event CPT + archive; listado con fechas |
| Videos conferencias / YouTube | §6 Videos (embed) |
| Testimonios | CPT testimonial o bloque |
| Botón donaciones | §7 Integraciones |
| Gestión eventos (inscripción, pagos) | event_signup_url, event_signup_payment |
| Sanghas con contacto | CPT sangha |
| Contenido formación / cómo hacer parte | Página o sección en Práctica o dedicada |
| Manual de marca | `02-identidad-corporativa` |
| Link WhatsApp | §7 Integraciones |

Otras ideas (estilo sobrio, paleta, accesibilidad, animación camino, Paramitas, El buda responde) están en `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `05-arquitectura-informacion-navegacion`, `15-tendencias-ux-ui-sistema-editorial`.

---

**Versión del documento:** 2.0
