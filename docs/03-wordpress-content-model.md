# Camino del Dharma — WordPress Content Model

Modelo de contenido oficial para la implementación WordPress del sitio de la Comunidad Buddhista
Camino del Dharma. El contenido pre-corte se extrae del HTML/JSON publicado y se valida contra
`https://caminodeldharma.org` (OWN-007, ADR 0034/0040); inventario en
`inventario-contenido-produccion-static`.

**Depende de:** `01-plataforma-comunidad-plan`, `02-identidad-corporativa`, `04-mapa-pantallas`. **Referencia:** `09-ui-copy-sheet` (textos de interfaz), `11-arbol-urls-final` (rutas), `12-theme-file-structure` (plantillas)

---

## 1. Esquema general

### Post Types

| Key | Label ES | Tipo | Slug | Uso principal |
|-----|----------|------|------|---------------|
| page | Páginas | Nativo | según cada página | Inicio, Comunidad, Linaje, Práctica, Eventos, Galería, Contribuir (donaciones), Contacto, Blog |
| event | Eventos | Custom | /eventos/ | Eventos especiales vigentes (retiros, talleres, Vesak, etc.) |
| blog_author | Autores (blog) | Custom | /author/ | Ficha de quien firma una entrada. URL `/author/{slug}`. No es el usuario WP (ADR 0037). |
| sangha | Sanghas | Custom | /sanghas/ | Contacto por sangha (antecedente histórico: conectar con cada sangha). Ver §3.1. |
| testimonial | Testimonios | Custom o bloque | /testimonios/ o bloque | Por defecto: bloque en página; si CPT, ver §3.2. |

### Contenido fijo vs. dinámico

| Tipo | Gestión |
|------|---------|
| Páginas estáticas | page (Inicio, Comunidad, Linaje, Práctica, Eventos, Galería, Contribuir, Contacto, Blog) |
| Eventos | event (visible solo cuando hay evento vigente); **cronograma** = listado/archive de eventos |
| Meditación semanal | Bloque fijo en Inicio y Práctica (Lunes 7:30 p.m., Zoom) |
| Formulario de contacto | Página Contacto |
| **Videos** | Embed YouTube/Vimeo en páginas y bloques (conferencias, enseñanzas, indicaciones para meditar) |
| **Cómo hacer parte / formación** | Página o sección: espacios de formación, alcance y propósito de cada uno (antecedente histórico). |

**Edición editorial:** El contenido textual principal (comunidad, linaje, práctica, narrativas) se gestiona directamente en el editor Gutenberg; no se crean campos personalizados para textos espirituales o narrativos.

---

## 2. Campos por página

### Inicio (front-page)

- Hero: título, subtítulo, botón principal
- Bloque comunidad (texto) + nota de un evento vigente a la derecha en escritorio (rótulo encima del cartel, cartel `medium`, alineada al borde derecho del hero; ver §3 regla Inicio)
- Cómo practicamos (3 columnas: Estudio consciente, Práctica vivencial, Vida cotidiana)
- Meditación semanal (texto, horario, botón Participar → WhatsApp)
- Caminos de participación (Iniciarse, Profundizar, Practicar en comunidad)

### Comunidad (page)

- Quiénes somos (texto)
- Fundador: Venerable Maestro Zheng Gong (biografía, foto, enlace al blog)
- *(OWN-016, en WP no en el estático: enlace del fundador a `/author/zheng-gong`; enlace de la comunidad a su ficha `/author/{slug}`.)*
- Experiencia y propósito (texto)

### Linaje (page)

- Tradición viva (texto)
- Mahāyāna (texto)
- Chan y Tierra Pura (texto + imágenes)
- Práctica en contexto actual (texto)

### Práctica (page)

- Aprende en la práctica (introducción)
- Meditación semanal (bloque repetido)
- Recitación práctica de la comida (texto + PDF descargable)
- Mantras para la práctica (extensible; Amitābha y Guān Shì Yīn Púsà con audio local)
- Caminos de profundización: talleres vivenciales, retiro de iniciación, retiros de meditación
- Videos y enseñanzas (embeds + enlace a subpágina de videos)
- Vida comunitaria (Encuentro nacional, Vesak, Conferencias)

### Eventos (page o archive-event)

- **Condicional:** visible solo cuando hay evento vigente
- **Estructura por evento:** tipo/categoría (event_type, ver §4), nombre, imagen, fecha, lugar, modalidad, descripción, enlace a la ficha si existe («Ver evento →»), botón Inscribirme / Preinscribirme. En la maqueta: etiqueta de tipo encima del título (Curso, Taller, Retiro, Conferencia, Encuentro); título y cartel enlazan a `/eventos/{slug}/` cuando hay página propia; separación visual entre eventos (card + hr); enlaces «Práctica · Contacto» una sola vez en la página
- Campos: event_type (taxonomía), nombre, fecha, lugar, modalidad, descripción, botón Inscribirme (o Preinscribirme), URL de la ficha cuando el evento tiene `single-event`

*(Nota 2026-08-28, ADR 0035 / OWN-004: **todo** evento tiene `single` `/eventos/{slug}`. Inscribirme / Preinscribirme **solo** si está vigente y hay inscripción real. Los finalizados no muestran ese CTA. OWN-012 / OWN-013: finalizados **sin** calendario ni `.ics`; el paso a finalizado es automático al vencer la fecha de fin.)*

### Contacto (page)

- Formulario: Nombre, Correo, Mensaje
- Botón: Enviar
- El formulario debe incluir protección anti-spam básica (captcha o plugin simple).

---

## 3. Event (Custom Post Type)

| Campo | Tipo | Uso |
|-------|------|-----|
| event_name | text | Nombre del evento |
| event_date | date | Fecha de inicio |
| event_end | date | Fecha de fin (si no se rellena, vale `event_date`). OWN-013 compara contra esta |
| event_place | text | Lugar |
| event_modality | select | presencial / virtual / híbrido |
| event_description | WYSIWYG | Descripción, sentido, a quién va dirigido |
| featured_image | image | Imagen del evento (listados, portada del evento) |
| event_signup_url | url | Enlace a inscripción (formulario externo, WhatsApp, plataforma o pasarela externa) |
| event_status | select | vigente / finalizado / cancelado |
| event_signup_payment | boolean o url | Indica si hay pago/contribución; el pago siempre es externo (redirección vía event_signup_url) |
| event_featured | boolean | Candidato para el Inicio. **Solo cuenta si el evento está vigente.** Un destacado finalizado se ignora. Marcar como máximo uno. |
| share_whatsapp / share_x / share_threads | textarea | Mensaje que el diálogo «Compartir» propone en cada red (WU-08A). Texto plano; `{{SHARE_URL}}` se sustituye por la URL del evento al abrir el diálogo. Vacío = el diálogo comparte título + URL. Las mismas tres claves existen en `post` (entradas del blog). |

**Prioridad a campos nativos:** Se prioriza usar campos nativos de WordPress cuando sea posible: **Title** → nombre del evento, **Content** → descripción, **Featured image** → imagen principal. Los campos custom (`event_date`, `event_place`, `event_modality`, `event_status`, `event_featured`, `event_signup_url`, etc.) complementan lo que el core no ofrece.

**Sin pagos internos:** El sitio no procesa pagos, no tiene checkout ni lógica financiera. Si un evento requiere inscripción económica, se usa `event_signup_url` para redirigir a la plataforma externa correspondiente.

**Regla de visibilidad:** `/eventos/` muestra **dos bloques diferenciados**: los eventos con `event_status = vigente` (acción posible) y, debajo, el **archivo de eventos finalizados** (memoria y evidencia de actividad). *En el mapa de pantallas (04) corresponde a la página de Eventos.*

**Definición de «vigente» (OWN-013, 2026-08-29):** la fecha de fin manda, no un interruptor manual.
En `America/Bogota`, si `hoy > event_end` (o `event_date` si no hay fin), el evento es **finalizado**:
bloque archivo, sin inscripción, sin «Añadir al calendario», `.ics` **410** y se borra cualquier
archivo `.ics` huérfano (OWN-012). El **último día** sigue vigente. `cancelado` lo pone el editor
y la fecha no lo cambia. El visitante lo ve al cargar la página (el plugin calcula; un cron puede
persistir `event_status` para el listado de wp-admin, pero no es lo que ve el público). Si se
alarga `event_end`, vuelve a vigente y el `.ics` generado (OWN-009) responde otra vez. La
meditación semanal no es `event`. En wp-admin, **Eliminar huérfanos** (OWN-015) fuerza a mano
la misma limpieza de `.ics`; no toca imágenes.

**Un evento en el Inicio:** la portada muestra **como máximo un** evento **vigente**, en nota junto al texto de «Un poco de nuestra comunidad» (no es un segundo listado). Un evento **finalizado nunca aparece aquí**, aunque tenga `event_featured = true`: la marca de destacado se ignora si el evento ya terminó. Selección:

1. Candidatos: solo `event_status = vigente`.
2. Si entre ellos hay uno con `event_featured = true`, se muestra ese.
3. Si no hay ninguno marcado, se muestra el vigente con fecha de inicio más cercana.
4. Si hay más de un destacado vigente, se muestra el de fecha de inicio más cercana. La guía editorial: marcar solo uno.
5. Si no hay ningún vigente, **no se renderiza el módulo** (ni caja vacía ni mensaje). El texto de comunidad queda a ancho de lectura, sin columna derecha.

**Maqueta actual (2026-08-13):** `event_featured = true` en Círculos de Presencia Consciente. Un vigente con fecha de inicio más cercana no sustituye esta nota mientras esa marca siga activa.

La UI no dice «destacado». El visitante ve, en este orden: rótulo «Próximo evento · {tipo}» (encima del cartel, alineado al `h2` de comunidad), cartel completo a tamaño WordPress `medium` (~300 px en escritorio, al ras del borde derecho del hero; ancho de lectura en móvil; atajo de puntero a la ficha, igual que el listado), nombre (`h3`), fecha, lugar y «Ver evento». No hay enlace al listado, caja, Preinscribirme ni calendario. El cartel a tamaño de lectura amplio vive también en la ficha del evento.

**Eventos finalizados: SÍ aparecen en el listado, en su propio bloque.**

> **Actualizado 2026-07-21.** La versión anterior decía que los eventos finalizados «no aparecen en listados principales». **Queda revocado.** El motivo del cambio:
>
> 1. **Son la vía legítima de relevancia geográfica.** La comunidad no tiene sede física y por tanto no es elegible para Google Business Profile (ver `informes-seo/`). Los encuentros reales en Cali, Bogotá, Medellín y Barranquilla son la única forma honesta de asociar la comunidad a esas ciudades ante los buscadores. Ocultarlos del listado desperdicia esa señal.
> 2. **Son prueba de actividad sostenida.** Quien evalúa si acercarse necesita ver que la comunidad se mueve con un ritmo real —2 a 3 encuentros al año por ciudad—, y eso solo se percibe viendo el conjunto.
> 3. **La proporción lo hace inevitable.** Con encuentros puntuales y pocos eventos vigentes a la vez, un listado que solo muestre lo vigente está vacío la mayor parte del año.
>
> **Requisito que se mantiene:** la distinción entre lo vigente y lo finalizado debe ser **inequívoca** —insignia, tratamiento visual y texto para lectores de pantalla—. El fallo grave de esta página es que alguien intente asistir a algo que ya terminó.

**Agrupación del listado:** distinta por bloque, porque responden a intenciones distintas.

| Bloque | Agrupación | Motivo |
|---|---|---|
| **Vigentes** | Por **mes** | Lógica de calendario: cuándo es lo próximo |
| **Archivo** | Por **año** | Lógica de memoria: agrupar por mes daría grupos de uno |

Los encabezados de agrupación deben ser **encabezados reales** (`h3`, con los títulos de evento en `h4`), no separadores decorativos: quien usa lector de pantalla navega saltando entre encabezados. Ver `19-accesibilidad-estandares`.

**Densidad:** los eventos finalizados usan **tarjeta compacta** (miniatura, título, ciudad, fecha, enlace al detalle); los vigentes conservan el tratamiento completo. Motivo: con carteles verticales a ancho de lectura, cada tarjeta completa ocupa cerca de una pantalla —más en escritorio que en móvil—, y el archivo se vuelve intransitable. El detalle extenso vive en `single-event.php`, donde alguien llega a propósito. En el listado, quien desea ese detalle entra por «Ver evento →», por el título o por el cartel; el listado no duplica cronograma ni condiciones operativas.

**Escalado:** con el ritmo actual (~8–12 eventos/año) el listado único con agrupación por año es suficiente hasta unos 25–30 eventos. A partir de ahí, archivos por año (`/eventos/2025/`) usando los archivos de fecha nativos de WordPress. **No usar paginación numerada ni carga por JavaScript:** la primera entierra el contenido y la segunda no se indexa de forma fiable, lo que anularía el punto 1.

**Cronograma de eventos (antecedente histórico):** vista de calendario solo si hay masa crítica de
eventos vigentes simultáneos.

**Calendario del mes:** los días de un `event` se marcan rellenos (`.has-event`, `brand-2-deep`) y enlazan a esa tarjeta. Cada lunes **sin otro evento ese día** se marca con el mismo fondo que un día vacío y borde `brand-2` (`.has-practice`) y enlaza a `/practica/meditacion-semanal-en-linea`. Tooltip propio (`data-tooltip`) al hover, al foco de teclado y, con puntero grueso, al primer toque (bajo la cuadrícula aparece «Toca de nuevo para ver el evento.» con `aria-hidden`; el segundo sigue el enlace; Enter no se intercepta); no usar `title` nativo (duplicaría el aviso). No es un evento: no va al listado, no tiene ficha ni JSON-LD `Event`. Si ese lunes ya tiene un evento, solo se marca el evento. El mes mostrado es el del próximo vigente (en la maqueta: septiembre 2026).

**WordPress (Fase 3):** no instalar un plugin de calendario de terceros ni crear un segundo plugin. La selección de celdas (qué día es evento, qué lunes es práctica, URL y tooltip) vive en `camino-del-dharma-core` (ADR 0024). El theme pinta un bloque dinámico propio (p. ej. `camino-del-dharma/eventos-calendar`) en `archive-event.html`, con el CSS de la maqueta. `get_calendar()` del núcleo no sirve. La meditación semanal no se da de alta como `event`.

**Datos estructurados (SEO):** JSON-LD `Event` en `single-event.php` **y en el listado** para los eventos que no tengan página de detalle propia. `organizer` = Camino del Dharma; `performer` solo si hay facilitador nombrado; `offers` solo con inscripción real; `eventStatus = EventCompleted` y `location.address.addressLocality` **obligatorio** en finalizados — es lo que convierte el archivo en señal geográfica. Detalle en `15-assets-strategy` §12.3.

**Taxonomía de ciudad — advertencia de implementación:** si se añade una taxonomía `ciudad`, WordPress generará automáticamente un archivo por cada término (`/eventos/ciudad/medellin/`). Con una sola entrada, esas páginas son *doorway pages* según las políticas de spam de Google — creadas por el CMS sin que nadie las escriba. **Controlar su indexación** y abrirlas solo donde haya volumen real de eventos.

---

## 3.0. Autores del blog (CPT `blog_author`)

*(ADR 0037 / OWN-010, 2026-08-29.)* El «Por…» de las **entradas** sale de fichas CPT, no de
copy ni de `post_author`. El usuario que publica no aparece en el sitio. Todas las fichas son
el mismo tipo (Comunidad Camino del Dharma = una ficha más). Relación: meta `authors` (IDs,
orden = byline). Publicar exige ≥1 ficha publicada. `query_var` = `blog_author` (**nunca**
`author`). Rewrite slug `author`. Archivos de usuarios WP = 404. No Page slug `author`.
Eventos no usan este CPT. `/comunidad` no se sustituye por `/author/zheng-gong`. En WP se
**enlaza** desde esa Page a las fichas (OWN-016); el HTML estático no se toca ahora.

*(OWN-020 / D-08, 2026-09-01; implementación pendiente [#5](https://github.com/refo44/demo-caminodeldharma/issues/5).)*
Las fichas son páginas de entidad **indexables**. Bio y `seo_*` reutilizan copy corto y fotos
ya publicados (JSON-LD del fundador; primer párrafo / meta de `/comunidad`). No duplicar el
ensayo largo del fundador. No `noindex` en el single. Payload `blog_authors` debe llevar `seo`,
contenido y thumbnail cuando se implemente.

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

**Nota sobre sedes (2026-07-31):** ninguna ciudad tiene sede fija ni dirección física propia — la
misma razón por la que se descartó Google Business Profile (`.audit/decisions.md`, 2026-07-21).
`sangha_map_url` seguirá vacío en la práctica para la mayoría o todas las ciudades; no se debe rellenar
con una dirección genérica o supuesta. Algunas actividades recurrentes usan de hecho el mismo lugar por
temporadas (p. ej. la meditación al parque), pero eso también cambia (en Bogotá ese mismo evento se ha
hecho en parques distintos): la ubicación es un dato **por evento**, no de la sangha. Vive en
`event_place` (§3, texto libre por evento), nunca como dirección fija heredada de la página de ciudad.
El contenido de cada página de sangha (cuando se implemente, ver TASK-0020) debe describir la actividad
y cómo confirmar el lugar de cada convocatoria (WhatsApp), no afirmar una ubicación permanente.

---

## 3.2. Testimonial: estándar del modelo

**Por defecto (recomendado para este sitio):** No CPT. Bloque «Testimonios» editable en página Comunidad o Inicio; contenido gestionado en la propia página o en un bloque reutilizable.

**Si se implementa CPT testimonial:** Definir campos mínimos (p. ej. testimonial_text, testimonial_author, testimonial_photo opcional); decidir si se publica como listado `/testimonios/` o solo se usa en bloques internos.

---

## 4. Taxonomías

| Key | Label ES | Tipo | Aplica a |
|-----|----------|------|----------|
| event_type | Tipo de evento | Jerárquica | event |
| event_city | Ciudad del evento | Plana | event |
| post_tag | Tags | Nativa, plana | post (blog) |

**Valores (términos):** Curso, Taller, Retiro, Conferencia, Encuentro (etiqueta corta para «Encuentro nacional»), Celebración (Vesak, Diwali, etc.).

En la maqueta estática estos valores se muestran como etiqueta encima del título de cada evento (clase `.evento-type`). En WordPress se asignan vía la taxonomía `event_type`; el theme debe mostrar el término como label en single y en listados.

**Ninguna de las dos taxonomías de evento tiene archivo público.** Se usan como dato y como etiqueta, nunca como página: no existe `/eventos/taller` ni `/eventos/cali`. `event_city` sirve para asociar cada evento con su sangha y listarlo dentro de `/sanghas/{ciudad}`. En la maqueta estática es un atributo en el marcado del evento. Ver **ADR 0022**.

**`post_tag` (tags del blog) es distinta: sí tiene archivo, pero condicionalmente indexable (ADR 0031).**
Los editores asignan tags libremente a cada entrada del blog desde el editor, sin vocabulario cerrado.
El archivo de cada tag existe en `/blog/tag/{slug}/` (`docs/11-arbol-urls-final.md`), pero se sirve
`noindex, follow` por defecto hasta que ese tag reúna suficiente contenido para ser un hub temático útil
— criterio cualitativo revisado caso por caso, no un umbral automático (mismo aprendizaje que la
revisión de ADR 0022). Si la entrada tiene tags, el `BlogPosting` puede incluir `keywords`
(`docs/15-assets-strategy.md` §12.4).

---

## 5. Plantillas mínimas

- `templates/front-page.html`: Inicio
- `templates/page-comunidad.html`: La comunidad
- `templates/page-linaje.html`: El linaje
- `templates/page-practica.html`: Práctica y actividades
- `templates/page-eventos.html` o `templates/archive-event.html`: Eventos (condicional)
- `templates/page-galeria.html`: Galería — ver §5.1
- `templates/page-donaciones.html`: Contribuir (donaciones)
- `templates/page-contacto.html`: Contacto
- `templates/home.html` / `templates/single.html`: Blog (§4, §12 §2.2)
- `templates/single-event.html`: Evento individual (si aplica)
- `templates/page.html`: Fallback para páginas — cubre **Privacidad** (`/privacidad`), que no necesita plantilla propia
- `templates/404.html`: Página no encontrada (estado; copy en 08)

*(Plantillas de bloques — theme de bloques / Full Site Editing, ADR 0029. Ver `12-theme-file-structure` §5–§6.)*

### 5.1 Galería — bloque de Gutenberg, con lightbox nativo

La galería **no se reimplementa** en el tema. Decisión registrada en **ADR 0021**.

| | Maqueta estática (hoy) | WordPress (destino) |
|---|---|---|
| Grid y paginación | `assets/js/gallery.js` + JSON embebido | **Bloque de galería de Gutenberg** |
| Miniaturas | `assets/images/galeria/thumbs/` (300w/600w, generadas a mano) | Tamaños derivados que genera la biblioteca de medios |
| Visor ampliado | **No existe** | **Lightbox nativo** del bloque ("Ampliar al hacer clic", WP 6.4+) |

**Consecuencias para la migración:**

- `gallery.js` **no viaja al tema** (ver `12-theme-file-structure` §11.5).
- Los **36 originales** de `assets/images/galeria/` se suben a la biblioteca de medios; WordPress genera sus propios tamaños. Las miniaturas manuales de `thumbs/` **no se migran**: son una solución provisional de la etapa estática.
- **No se instala plugin de lightbox.** El núcleo ya lo cubre; un plugin añadiría superficie de mantenimiento para algo que el núcleo ya resuelve.
- El bloque de galería renderiza en servidor, lo que **resuelve de paso AEO-001** (hoy la galería estática es invisible sin JavaScript).

**Álbumes: creados libremente por el editor, sin lista fija.** En la maqueta estática los álbumes
("General", por año, etc. — ver `06-wireframes` §6) están fijados en `#gallery-albums-data`, editable
solo por quien toca código. En WordPress, `page-galeria.html` renderiza el contenido de la Page
(`/galeria/`) a través del bloque **Contenido de la entrada**, y cada álbum es simplemente un bloque
**Encabezado** (título, cualquier texto que el editor escriba) seguido de un bloque **Galería** con las
imágenes de ese álbum — o Query por término. **ADR 0036 / OWN-008:** taxonomía de álbum
(`/galeria/{slug}`), no CPT ni Page hija. El hub `/galeria` es la Page (KEEP, indexable). Los
archivos de término nacen `noindex, follow` hasta volumen. El plugin evita que el rewrite robe
`/galeria`. Crear un álbum = término + asignar fotos (+ bloques en el hub si se desea). Coherente
con ADR 0021 y ADR 0029.

**Paginación (OWN-011, 2026-08-29):** el bloque Galería nativo **no pagina** — muestra todas las
imágenes del álbum (lazy-loading nativo). En el corte FSE **no** se recrea la paginación de 12 de la
maqueta (`06-wireframes` §6, `gallery.js`). Tampoco `/galeria/{slug}/page/2`. Revisar solo si un
álbum crece mucho; entonces, si acaso, solo en la URL del término, no en el hub.

**Accesibilidad:** el lightbox del núcleo ya trae gestión de foco, cierre con `Esc` y ARIA. Si en algún momento se sustituyera por uno propio, debería cumplir `19-accesibilidad-estandares` y seguir el patrón `.share-dialog` que ya existe en el proyecto.

---

## 6. Videos (YouTube / Vimeo)

Los videos acompañan la enseñanza; no constituyen el contenido central del sitio (evitar que el sitio derive hacia una videoteca).

- **Embed:** Las páginas y bloques deben permitir incrustar videos por URL (YouTube, Vimeo).
- **Uso:** Conferencias, enseñanzas del maestro, indicaciones para meditar, canal de YouTube conectado a la página.
- **Lista vigente de URLs:** embeds publicados en `practica/videos/index.html`. La implementación debe
  extraer esas URLs y compararlas con la página publicada antes de importarlas.
- **Antecedente histórico ya incorporado:** "Espacio para subir videos de las conferencias, conectar
  el canal de youtube a la página", "Video sobre indicaciones para meditar".

Implementación: bloque o campo de tipo «video embed» (URL); editor no sube archivo de video, solo pega enlace.

---

## 7. Integraciones externas

| Recurso | Implementación |
|---------|----------------|
| Meditación semanal | Botón "Participar" → WhatsApp (+57 320 662 7608) |
| **WhatsApp** | Link en cabecera/footer (antecedente histórico: "Link para ir al whatsapp de Camino del Dharma") |
| Formulario contacto | Envío a caminodeldharma1@gmail.com o plugin |
| **Donaciones** | Botón + datos bancarios en footer (Banco Popular, cuenta 220065151425) — antecedente histórico: "Incluir botón de donaciones" |
| Blog | Enlace interno a `/blog/` |
| Redes sociales | Facebook, Instagram en footer |

---

## 8. Requisitos de accesibilidad del contenido en WordPress

Sin reemplazar lo definido en `19-accesibilidad-estandares`, este modelo exige dos reglas mínimas que afectan al contenido editado en WordPress:

- **Imágenes:** Siempre rellenar texto alternativo (`alt`). Si la imagen es decorativa, usar `alt=""`.
- **Videos informativos:** Si el video transmite información (conferencias, enseñanzas, indicaciones), debe disponer de subtítulos o transcripción.

**Estructura editorial (SEO mínimo):** Cada página debe tener un H1 único y jerarquía H2/H3 clara. No saltar niveles (p. ej. H1 → H3).

**Metadatos sociales:** Entradas del blog y eventos compartibles deben generar en servidor URL canónica, Open Graph y Twitter Card con título, descripción e imagen propios. La fuente es `get_the_title()`, extracto editorial, `get_permalink()` e imagen destacada (o campo social específico). **No hay plugin SEO aprobado** (ADR 0025 vetea por defecto las suites todo-en-uno); esto se construye como código first-party en `camino-del-dharma-core` o el theme (ADR 0025, preferencia 2), no se instala un plugin de terceros para resolverlo. El `<title>` y el canonical siguen las reglas de §12.1/§12.2 de `docs/15-assets-strategy.md`; JSON-LD de `Event` según §12.3 del mismo documento. Lo mismo aplica a cualquier pieza de SEO dinámico que WordPress no resuelva de forma nativa: se construye en PHP propio (plugin/theme) o JS propio, nunca vía plugin de terceros por comodidad.

Detalle y criterios ampliados en `19-accesibilidad-estandares`.

---

## 9. Principio rector

Todo en este modelo existe para: **orientar, inspirar confianza y facilitar el primer contacto con la práctica buddhista**. No hay capas de marketing ni funnels. Solo acogida y claridad.

---

## Trazabilidad histórica ya incorporada en este modelo

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

**Versión:** 2.23 — Fuente editorial pre-corte alineada con ADR 0040.
