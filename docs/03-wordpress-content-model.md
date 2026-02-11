# Camino del Dharma — WordPress Content Model

Modelo de contenido oficial para la implementación WordPress del sitio de la Comunidad Buddhista Camino del Dharma. Basado en Contenido_Web_Camino_del_Dharma.docx y Lluvia de ideas. Contenido canónico: seguir estrictamente `content-source/.../Contenido_Web_Camino_del_Dharma` (docx/md); inventario en 13.

**Depende de:** `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `04-mapa-pantallas`. **Referencia:** `09-ui-copy-sheet` (textos de interfaz), `11-arbol-urls-final` (rutas), `12-theme-file-structure` (plantillas)

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

**Edición editorial:** El contenido textual principal (comunidad, linaje, práctica, narrativas) se gestiona directamente en el editor Gutenberg; no se crean campos personalizados para textos espirituales o narrativos.

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
- El formulario debe incluir protección anti-spam básica (captcha o plugin simple).

---

## 3. Event (Custom Post Type)

| Campo | Tipo | Uso |
|-------|------|-----|
| event_name | text | Nombre del evento |
| event_date | date | Fecha |
| event_place | text | Lugar |
| event_modality | select | presencial / virtual / híbrido |
| event_description | WYSIWYG | Descripción, sentido, a quién va dirigido |
| featured_image | image | Imagen del evento (listados, portada del evento) |
| event_signup_url | url | Enlace a inscripción (formulario externo, WhatsApp, plataforma o pasarela externa) |
| event_status | select | vigente / finalizado / cancelado |
| event_signup_payment | boolean o url | Indica si hay pago/contribución; el pago siempre es externo (redirección vía event_signup_url) |

**Prioridad a campos nativos:** Se prioriza usar campos nativos de WordPress cuando sea posible: **Title** → nombre del evento, **Content** → descripción, **Featured image** → imagen principal. Los campos custom (`event_date`, `event_place`, `event_modality`, `event_status`, `event_signup_url`, etc.) complementan lo que el core no ofrece.

**Sin pagos internos:** El sitio no procesa pagos, no tiene checkout ni lógica financiera. Si un evento requiere inscripción económica, se usa `event_signup_url` para redirigir a la plataforma externa correspondiente.

**Regla de visibilidad:** Solo eventos con `event_status = vigente` aparecen en la sección Eventos del sitio y en la ruta `/eventos/` (template Eventos). *En el mapa de pantallas (04) corresponde a la página de Eventos.*

**Definición de «vigente»:** `event_status` es la fuente de verdad (manual). Opcionalmente, si `event_date` es anterior a hoy, el sistema puede sugerir marcar como «finalizado» para evitar eventos antiguos visibles por error.

**Eventos finalizados:** Pueden mantenerse accesibles vía `single-event.php` para memoria histórica, pero no aparecen en listados principales (archive, sección Eventos del sitio).

**Cronograma de eventos (Lluvia de ideas):** Listado/archive en `/eventos/`. Por defecto: **listado cronológico con agrupación por mes**. Vista de calendario solo si hay masa crítica de eventos en el tiempo.

---

## 3.1. Sangha (Custom Post Type, si se implementa)

CPT opcional según crecimiento de la comunidad. **No implementar en fase inicial salvo necesidad real;** evita scope creep.

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
- `404.php`: Página no encontrada (estado; copy en 08)

---

## 6. Videos (YouTube / Vimeo)

Los videos acompañan la enseñanza; no constituyen el contenido central del sitio (evitar que el sitio derive hacia una videoteca).

- **Embed:** Las páginas y bloques deben permitir incrustar videos por URL (YouTube, Vimeo).
- **Uso:** Conferencias, enseñanzas del maestro, indicaciones para meditar, canal de YouTube conectado a la página.
- **Lista canónica de URLs:** `content-source/Pagina web Camino del Dharma/Link-videos-youtube.md` (4 videos: Encontrando la Plenitud…, La sabiduría del no hacer, ¿Cómo el Budismo puede ayudarnos…?, La Sabiduría del Buddhismo Chan). Misma lista en `Link videos subidos en Youtube.docx`. Implementación debe usar estas URLs para los embeds de conferencias/enseñanzas.
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

Sin reemplazar lo definido en `19-accesibilidad-estandares`, este modelo exige dos reglas mínimas que afectan al contenido editado en WordPress:

- **Imágenes:** Siempre rellenar texto alternativo (`alt`). Si la imagen es decorativa, usar `alt=""`.
- **Videos informativos:** Si el video transmite información (conferencias, enseñanzas, indicaciones), debe disponer de subtítulos o transcripción.

**Estructura editorial (SEO mínimo):** Cada página debe tener un H1 único y jerarquía H2/H3 clara. No saltar niveles (p. ej. H1 → H3).

Detalle y criterios ampliados en `19-accesibilidad-estandares`.

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

Otras ideas (estilo sobrio, paleta, accesibilidad, animación camino, Paramitas, El buda responde) están en `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `05-arquitectura-informacion-navegacion`, `18-tendencias-ux-ui-sistema-editorial`, `19-accesibilidad-estandares`.

---

## Cierre

Este documento define el **modelo de contenido oficial** del sitio: post types (nativos y custom), campos por página, eventos condicionales, testimonios (bloque o CPT), sangha (esquema mínimo si se implementa), videos (embed), integraciones externas y requisitos mínimos de accesibilidad del contenido. Está alineado con el plan (01), mapa de pantallas (04), árbol de URLs (11) y estructura del theme (12). Las rutas oficiales están en 11; las plantillas en 12.

---

**Versión:** 2.0
