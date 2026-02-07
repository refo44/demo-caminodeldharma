# Camino del Dharma — WordPress Content Model

**Versión 1.0**

Modelo de contenido para la web de la Comunidad Buddhista Camino del Dharma. Basado en Contenido_Web_Camino_del_Dharma.docx y Lluvia de ideas.

**Depende de:** `02-identidad-corporativa`, `04-mapa-pantallas`

---

## 1. Esquema general

### Custom Post Types

| CPT (key) | Label ES | Slug | Uso principal |
|-----------|----------|------|---------------|
| page | Páginas | por página | Inicio, Comunidad, Linaje, Práctica, Eventos, Contacto |
| event | Eventos | /eventos/ | Eventos especiales vigentes (retiros, talleres, Vesak, etc.) |
| sangha | Sanghas | /sanghas/ | Contacto por sangha (si se implementa) |

### Contenido fijo vs. dinámico

| Tipo | Gestión |
|------|---------|
| Páginas estáticas | page (Inicio, Comunidad, Linaje, Práctica, Contacto) |
| Eventos | event (visible solo cuando hay evento vigente) |
| Meditación semanal | Bloque fijo en Inicio y Práctica (Lunes 7:30 p.m., Zoom) |
| Formulario de contacto | Página Contacto |

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

**Regla:** Solo eventos con `event_status = vigente` aparecen en la página 5.

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

## 6. Integraciones externas

| Recurso | Implementación |
|---------|----------------|
| Meditación semanal | Botón "Participar" → WhatsApp (3206627608) |
| Formulario contacto | Envío a caminodeldharma1@gmail.com o plugin |
| Donaciones | Datos bancarios en footer (Banco Popular, cuenta 220065151425) |
| Pausa Profunda | Enlace externo, nueva pestaña |
| Redes sociales | Facebook, Instagram en footer |

---

## 7. Principio rector

Todo en este modelo existe para: **orientar, inspirar confianza y facilitar el primer contacto con la práctica buddhista**. No hay capas de marketing ni funnels. Solo acogida y claridad.

---

**Versión del documento:** 1.0
