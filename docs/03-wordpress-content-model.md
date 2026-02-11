# Camino del Dharma — WordPress Content Model

**Versión 2.0**

Modelo de contenido oficial para la implementación WordPress del sitio de la Comunidad Buddhista Camino del Dharma. Basado en Contenido_Web_Camino_del_Dharma.docx y Lluvia de ideas.

**Depende de:** `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `04-mapa-pantallas`

---

## 1. Esquema general

### Post Types

| Key | Label ES | Tipo | Slug | Uso principal |
|-----|----------|------|------|---------------|
| page | Páginas | Nativo | según cada página | Inicio, Comunidad, Linaje, Práctica, Eventos, Contacto |
| event | Eventos | Custom | /eventos/ | Eventos especiales vigentes (retiros, talleres, Vesak, etc.) |
| sangha | Sanghas | Custom | /sanghas/ | Contacto por sangha (Lluvia de ideas: conectar con cada sangha). Ver §3.1. |
| testimonial | Testimonios | Custom o bloque | /testimonios/ o bloque | Por defecto: bloque en página; si CPT, ver §3.2. |

### Contenido fijo vs. dinámico

| Tipo | Gestión |
|------|---------|
| Páginas estáticas | page (Inicio, Comunidad, Linaje, Práctica, Contacto) |
| Eventos | event (visible solo cuando hay evento vigente); **cronograma** = listado/archive de eventos |
| Meditación semanal | Bloque fijo en Inicio y Práctica (Lunes 7:30 p.m., Zoom) |
| Formulario de contacto | Página Contacto |
| **Videos** | Embed YouTube/Vimeo en páginas y bloques (conferencias, enseñanzas, indicaciones para meditar) |
| **Cómo hacer parte / formación** | Página o sección: espacios de formación, alcance y propósito de cada uno (Lluvia de ideas). |

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
| event_modality | select | presencial / virtual / híbrido |
| event_description | WYSIWYG | Descripción, sentido, a quién va dirigido |
| event_signup_url | url | Enlace inscripción |
| event_status | select | vigente / finalizado / cancelado |
| event_signup_payment | url o boolean | Inscripción/pagos por la web (Lluvia de ideas: gestión de eventos) |

**Regla de visibilidad:** Solo eventos con `event_status = vigente` aparecen en la sección Eventos del sitio y en la ruta `/eventos/` (template Eventos). *En el mapa de pantallas (04) corresponde a la página de Eventos.*

**Definición de «vigente»:** `event_status` es la fuente de verdad (manual). Opcionalmente, si `event_date` es anterior a hoy, el sistema puede sugerir marcar como «finalizado» para evitar eventos antiguos visibles por error.

**Cronograma de eventos (Lluvia de ideas):** Listado/archive en `/eventos/`. Por defecto: **listado cronológico con agrupación por mes**. Vista de calendario solo si hay masa crítica de eventos en el tiempo.

---

## 3.1. Sangha (Custom Post Type, si se implementa)

Campos mínimos recomendados:

| Campo | Tipo | Uso |
|-------|------|-----|
| sangha_name | text | Nombre de la sangha |
| sangha_city | text | Ciudad o zona |
| sangha_contact_name | text | Nombre de contacto |
| sangha_contact_whatsapp | text o url | WhatsApp o teléfono |
| sangha_schedule | text | Horario o frecuencia (texto corto) |
| sangha_map_url | url | Enlace a mapa (opcional) |

---

## 3.2. Testimonial: estándar del modelo

**Por defecto (recomendado para este sitio):** No CPT. Bloque «Testimonios» editable en página Comunidad o Inicio; contenido gestionado en la propia página o en un bloque reutilizable.

**Si se implementa CPT testimonial:** Definir campos mínimos (p. ej. testimonial_text, testimonial_author, testimonial_photo opcional); decidir si se publica como listado `/testimonios/` o solo se usa en bloques internos.

---

## 4. Taxonomías

| Key | Label ES | Tipo | Aplica a |
|-----|----------|------|----------|
| event_type | Tipo de evento | Jerárquica | event |

Valores posibles: Retiro, Taller, Celebración (Vesak, etc.), Conferencia, Encuentro nacional.

---

## 5. Plantillas mínimas

- `front-page.php`: Inicio
- `page-comunidad.php`: La comunidad
- `page-linaje.php`: El linaje
- `page-practica.php`: Práctica y actividades
- `page-eventos.php` o `archive-event.php`: Eventos (condicional)
- `page-contacto.php`: Contacto
- `single-event.php`: Evento individual (si aplica)
- `page.php`: Fallback para páginas

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

## 8. Requisitos de accesibilidad del contenido en WordPress

Sin reemplazar lo definido en `16-accesibilidad-estandares`, este modelo exige dos reglas mínimas que afectan al contenido editado en WordPress:

- **Imágenes:** Siempre rellenar texto alternativo (`alt`). Si la imagen es decorativa, usar `alt=""`.
- **Videos informativos:** Si el video transmite información (conferencias, enseñanzas, indicaciones), debe disponer de subtítulos o transcripción.

Detalle y criterios ampliados en `16-accesibilidad-estandares`.

---

## 9. Principio rector

Todo en este modelo existe para: **orientar, inspirar confianza y facilitar el primer contacto con la práctica buddhista**. No hay capas de marketing ni funnels. Solo acogida y claridad.

---

## Referencia: Lluvia de ideas cubierta en este modelo

| Idea | Dónde |
|------|--------|
| Cronograma de eventos | event CPT + archive; listado con fechas |
| Videos conferencias / YouTube | §6 Videos (embed) |
| Testimonios | §3.2 (bloque por defecto; CPT opcional) |
| Botón donaciones | §7 Integraciones |
| Gestión eventos (inscripción, pagos) | event_signup_url, event_signup_payment |
| Sanghas con contacto | CPT sangha |
| Contenido formación / cómo hacer parte | Página o sección en Práctica o dedicada |
| Manual de marca | `02-identidad-corporativa` |
| Link WhatsApp | §7 Integraciones |

Otras ideas (estilo sobrio, paleta, accesibilidad, animación camino, Paramitas, El buda responde) están en `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `05-arquitectura-informacion-navegacion`, `15-tendencias-ux-ui-sistema-editorial`, `16-accesibilidad-estandares`.

---

## Cierre

Este documento define el **modelo de contenido oficial** del sitio: post types (nativos y custom), campos por página, eventos condicionales, testimonios (bloque o CPT), sangha (esquema mínimo si se implementa), videos (embed), integraciones externas y requisitos mínimos de accesibilidad del contenido. Está alineado con el plan (01), mapa de pantallas (04), árbol de URLs (10) y estructura del theme (11). Las rutas oficiales están en 10; las plantillas en 11.

---

**Versión:** 2.0
