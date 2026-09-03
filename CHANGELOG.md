# Changelog

<!-- markdownlint-disable MD024 -->

Historial de versiones publicadas en producción. Hostinger no conserva un registro de despliegues cuando se sube un ZIP manualmente; este archivo es la referencia canónica de qué está en vivo.

La versión actual del repositorio está en [`VERSION`](VERSION).

Formato de paquete de despliegue: `camino-del-dharma-vX.Y.Z.zip`

**Antes de incrementar la versión:** actualizar `<lastmod>` en [`sitemap.xml`](sitemap.xml) para cada página HTML modificada (ver checklist en [`README.md`](README.md#despliegue-en-hostinger)).

## [Unreleased]

### WordPress Fase 3 — META-001 / OWN-019: panel «Autores del blog» en Gutenberg (sin cambio del artefacto desplegado)

Plugin `camino-del-dharma-core` **0.7.4** ([#18](https://github.com/refo44/demo-caminodeldharma/issues/18)).
El estático de producción no se toca.

- **El editor ya puede firmar una entrada.** Hasta ahora la relación `authors` (ADR 0037 §6)
  solo llegaba por migración o WP-CLI: `post.php` no tenía dónde asignar Zheng Gong ni
  Comunidad Camino del Dharma, y el panel «Autor» de la barra lateral es el **usuario de
  WordPress** que entró, no la firma pública. El propietario adelantó META-001 al pre-staging
  (2026-09-02): la UI de autores va **antes** de Hostinger.
- **Panel nativo, no metabox clásico** (ADR [0025](docs/adr/0025-politica-plugins-terceros.md) /
  [0042](docs/adr/0042-gutenberg-meta-sin-metabox-clasico-sin-sync.md)). `includes/editor.php`
  registra `assets/js/authors-panel.js` —handwritten sobre `wp.plugins`, `wp.editor`,
  `wp.data`, `wp.apiFetch`, `wp.element`, `wp.components`, `wp.i18n`; sin `@wordpress/scripts`,
  sin webpack, sin JSX (ADR [0038](docs/adr/0038-pruebas-tdd-phpunit-sonar.md))— y lo encola
  **solo** en `post.php` / `post-new.php` cuando el tipo es `post`.
- **El transporte es el punto.** El panel escribe con
  `dispatch( 'core/editor' ).editPost( { meta } )`, así que Publicar/Actualizar envía
  `meta.authors` **en el mismo cuerpo REST** que lee `rest_pre_insert_post`. Eso es META-001 —el
  fallo de revistalogos #30—: un picker que solo llena el DOM publica un `400
  cdd_core_missing_authors` aunque se vea lleno. **El guard no se relaja:** un borrador puede ir
  sin ficha, publicar sin ficha publicada sigue siendo 400, y una entrada publicada no puede
  quedar en cero autores.
- **Buscador, no catálogo.** `GET /wp/v2/blog_author?status=publish` desde dos caracteres, con
  debounce; nunca precarga la colección y no da de alta fichas en línea (ADR 0037 §6). Las
  fichas en borrador o privadas no aparecen ni para un administrador. Varias fichas por entrada,
  en el orden del byline, reordenables y quitables desde el panel.
- **La firma pública no es el usuario de la sesión** (ADR 0037 §4). WordPress 7.1 rinde el
  control «Autor» como una fila del panel Resumen, no como un panel propio: no hay nombre que
  pasar a `removeEditorPanel()`, así que lo que se retira es el enlace REST
  `wp:action-assign-author` del que el editor hace depender esa fila. `post_author` **no** se
  toca —el tipo conserva el soporte `author`, con su columna en el listado, la edición rápida y
  las revisiones— y sigue siendo el rastro de quién creó y guardó. El «Por…» del front y el
  `author` del JSON-LD siguen leyendo `authors`, nunca `post_author`.
- Cubierto por `tests/WordPress/Editor_AuthorsPanelTest.php` (dependencias del script, encolado
  solo en el editor de `post`, ausencia del enlace de asignación, `post_author` intacto),
  `tests/WordPress/Post_AuthorsRelationTest.php` (PUT con `meta.authors` persiste en orden;
  cambiar `post_author` no cambia la firma; la búsqueda excluye borradores para un usuario con
  todas las capacidades) y `tests/Unit/Editor_Authors_PanelTest.php` (el script sincroniza por
  `core/editor`, busca solo publicadas desde dos caracteres, y el plugin no envía ningún
  `add_meta_box`). QA 4 manual en el Docker local: `TEST` (post 134) se firmó con Zheng Gong
  desde el editor y publicó a la primera; el front rinde «Por Zheng Gong» → `/author/zheng-gong`
  y el JSON-LD `BlogPosting.author` es la ficha; publicar sin ficha muestra «Para publicar una
  entrada asigna al menos una ficha de autor publicada».

### WordPress Fase 3 — D-04 / OWN-026: `/practica` deja de desbordar a 320 px (sin cambio del artefacto desplegado)

Theme `camino-del-dharma` **0.5.2** ([#12](https://github.com/refo44/demo-caminodeldharma/issues/12)).
El estático de producción no se toca.

- **La única regresión visual frente a lo publicado.** WU-10 midió `/practica` a 320 px y encontró
  `scrollWidth` 324 vs `clientWidth` 320. Producción publicada no desborda: rinde el reproductor a
  272 px. El resto de las 19 rutas ya estaba limpio.
- **Causa real, no el `padding`.** El núcleo sirve
  `.wp-block-audio audio { width: 100%; min-width: 300px }` en `wp-block-audio-inline-css`. Un
  suelo de 300 px gana sobre un ancho preferido, así que el `width: min(100%, 32rem)` del theme
  nunca llegaba a aplicarse y el reproductor estiraba toda la columna de contenido.
- **Corrección en la capa que le corresponde.** Presentación en el theme (ADR
  [0024](docs/adr/0024-plugin-dominio-theme-presentacion.md)): la regla del reproductor levanta el
  suelo (`min-width: 0`), lo topa en su columna (`max-width: 100%`, `box-sizing: border-box`) y
  conserva el tope publicado `width: min(100%, 32rem)`. El selector lleva las dos clases
  (`.wp-block-audio.mantra-audio audio`) para **ganar por especificidad**, no por el orden en que
  WordPress imprime la hoja del bloque. No se toca el conversor, no se sustituye `core/audio`, no
  se oculta el desbordamiento en `html`/`body` y no se ensancha el maquetado.
- **D-07 y D-09 siguen como los cerró el owner.** El bloque nativo sigue sin la línea «Tu navegador
  no permite reproducir este audio.» (OWN-029) y el desbordamiento heredado de
  `/blog/sangha-refugio-hiperconexion` por una URL larga sigue ahí (OWN-021 / `POST-008` /
  [#7](https://github.com/refo44/demo-caminodeldharma/issues/7)): esa página no tiene ningún
  `<audio>`, así que la regla no la alcanza.
- Cubierto por `tests/Unit/Theme_Audio_ContainmentTest.php`: la hoja del theme se parsea y se
  comprueba el contrato — una regla alcanza el reproductor convertido, **gana en especificidad** a
  la del núcleo, levanta el suelo y lo topa al 100 %, el tope publicado sobrevive y ninguna regla
  esconde el desbordamiento en `html`/`body`. Sin aserciones de píxeles en CI
  ([`docs/guia-pruebas-plugin-theme-fse.md`](docs/guia-pruebas-plugin-theme-fse.md)); la medida a
  320 px es comprobación manual documentada.

### WordPress Fase 3 — D-03 / OWN-025: los feeds nativos responden 404 (sin cambio del artefacto desplegado)

Plugin `camino-del-dharma-core` **0.7.3** (ADR [0044](docs/adr/0044-feeds-nativos-404.md),
[#11](https://github.com/refo44/demo-caminodeldharma/issues/11)). El estático de producción no
se toca.

- **Paridad de rutas con producción.** El estático publicado responde **404** en `/feed`,
  `/blog/feed` y `/comments/feed`, y ninguna de esas URLs está en
  [`docs/11-arbol-urls-final.md`](docs/11-arbol-urls-final.md) — si una URL no está en el árbol,
  no existe. WordPress las servía con **200**. Ahora cualquier petición que llegue con `feed` en
  la query es un **404 real** antes de la consulta principal, así que responde la plantilla 404
  del theme. Una sola guarda cubre todos los alias que registra el núcleo —`feed`, `rdf`, `rss`,
  `rss2`, `atom`, en URL bonita o como `?feed=`— para el sitio, la página de entradas, los
  comentarios de una entrada, los archivos y los CPT.
- **Nada anuncia lo que ya no existe.** Se retiran `feed_links` y `feed_links_extra` del `head`,
  así que no queda autodiscovery RSS/Atom. El `rel=alternate` `text/calendar` que publica un
  evento vigente hacia su `.ics` generado (OWN-014) **se mantiene**: es otra representación del
  recurso, no un feed.
- **Sin atajos.** No hay 301 a `/blog` (el estático no tiene esa redirección), ni 200 con
  `noindex` (el documento seguiría existiendo), ni cuerpo RSS con estado 404.
- **RSS futuro, no RSS nunca.** Un feed público es decisión posterior (`POST-010`) y exige su
  fila en el árbol de URLs y en el ledger de redirecciones. Reabrirlo es soltar dos hooks.
- Cubierto por `tests/WordPress/Feed_RoutingTest.php`: rutas entrantes vía `go_to()`, estado 404
  enviado, ausencia de `rel=alternate` RSS/Atom en el `head` de una Página y de una entrada, y
  el alternate de calendario intacto en un evento vigente.

### WordPress Fase 3 — D-02 / OWN-024: fuera el contenido demo del instalador (sin cambio del artefacto desplegado)

Plugin `camino-del-dharma-core` **0.7.2** ([#10](https://github.com/refo44/demo-caminodeldharma/issues/10)).
El estático de producción no se toca.

- **El instalador ya no puede publicar demo.** Un WordPress recién instalado publica «Hello
  world!» y «Sample Page» y deja un borrador «Privacy Policy». En el entorno local esa entrada
  demo aparece en «Del blog» del Inicio y en `/blog`, y **desplaza** a la entrada real «Estamos
  conectados, pero seguimos solos». Ahora activar el plugin —y cada actualización de versión—
  **despublica** ese contenido, así que un staging provisionado sin el paso manual tampoco lo
  muestra.
- **Borrarlo es una acción explícita.** `wp cdd-core demo purge` es dry-run por defecto,
  `--apply` borra, y en `production` exige `--confirm-production` + `--backup-evidence`
  (ADR 0033). Si el borrador borrado era el de `wp_page_for_privacy_policy`, la opción se
  limpia para no dejar una referencia colgada.
- **Reconocimiento por contrato, no por ID.** `Cdd_Core_Installer_Demo_Content` decide por tipo
  de contenido, slug por defecto (incluidos los traducidos de una instalación en español) y el
  estado en que los deja el instalador. Cualquier objeto con `_cdd_source_key` es contenido
  importado y queda intacto: la Página real `/privacidad` (ADR 0039/0041) y las entradas del
  blog no se tocan. El runbook §2.2 deja de recomendar `wp post delete 1 2 3 --force`.
- **El importador no cambia:** sigue siendo create-missing-only, sin backfill (ADR 0033,
  OWN-023). Un segundo `import --apply` sigue creando 0.

### Gobernanza — cierre WU-10 (OWN-021–OWN-035, ADR 0044/0045)

Registro 2026-09-01: D-01–D-12, seed Hostinger, correo CF7, FABLE5, timing de staging. Código
pre-staging: issues [#10](https://github.com/refo44/demo-caminodeldharma/issues/10)–[#12](https://github.com/refo44/demo-caminodeldharma/issues/12).
Backlog v1.28. Los prompts FABLE5 se retiran de `docs/` (OWN-034).

### Gobernanza — D-08 / OWN-020: SEO de fichas de autor (pendiente de implementar)

El propietario cierra D-08 (2026-09-01): `/author/{slug}` sigue **indexable** (ADR 0037). Las
fichas reutilizan copy corto y fotos ya publicados; no se inventa texto ni se duplica el ensayo
largo de `/comunidad`. Código **no** entra en este registro: cola
[#5](https://github.com/refo44/demo-caminodeldharma/issues/5). Cerrado en v1.26;
el backlog vigente es v1.28.

### WordPress Fase 3 — revisión Copilot en el PR (sin cambio del artefacto desplegado)

- **Destacado del Inicio:** un evento vigente sin `event_date` ya no gana por
  `strcmp('')` frente a uno con fecha; las fechas vacías ordenan al final. El
  marcado editorial featured sigue ganando. Plugin `camino-del-dharma-core` **0.7.1**.
- **Payload:** `_source_hash` excluye `_source_key` además de sí mismo (el contrato
  del builder). `migration/payload.json` se regeneró; create-missing-only no
  reescribe contenido ya importado. `json_encode` lanza `JsonException` si el
  canónico no se puede producir (no escribe el literal `false`).

### Gobernanza — META Gutenberg no es defecto de corte (ADR 0042 / OWN-019)

El propietario decide (2026-09-01) que `META-001`–`META-005` (auditoría metabox clásico vs
REST) son **restricciones de diseño** para UI wp-admin futura, no bugs ni trabajo de
pre-staging. Sin `add_meta_box` hoy; el corte conserva meta importada. Ver
[`docs/adr/0042-gutenberg-meta-sin-metabox-clasico-sin-sync.md`](docs/adr/0042-gutenberg-meta-sin-metabox-clasico-sin-sync.md).

### WordPress Fase 3 — BUG-001: el `.ics` de Círculos incluye todas las sesiones (sin cambio del artefacto desplegado)

Plugin `camino-del-dharma-core` **0.7.1** y theme `camino-del-dharma` **0.5.1**. El estático de
producción no se toca: `static/eventos/ical/circulos-de-presencia-consciente.ics` sigue
publicando su VEVENT único de la sesión de bienvenida hasta el corte.

- **Un VEVENT por sesión.** El exportado de WordPress emitía una sola entrada del rango 3 sep →
  25 oct, que ningún calendario puede distinguir de un curso de 52 días seguidos. Ahora emite una
  entrada por cada fecha de `event_calendar_dates` —las diez del cronograma publicado— con UID
  propio (`slug-Ymd@host`) y su fin exclusivo de día completo, dentro del mismo sobre VCALENDAR
  de producción. Un evento sin cronograma conserva el rango `event_date`/`event_end` y el UID que
  producción ya publicó.
- **El diálogo y el archivo no pueden divergir.** Un enlace profundo de Google o Outlook lleva una
  sola entrada, así que «Añadir al calendario» pasa a nombrar la **próxima sesión** —una fecha que
  el archivo contiene— en lugar de un rango que no aparece en ningún VEVENT, y una nota nueva lo
  dice: «El archivo .ics incluye las 10 sesiones del curso…». Apple Calendar y la descarga siguen
  entregando las diez.
- **Sin cambios en OWN-012**: un evento finalizado sigue devolviendo 410 sin `.ics`, sin
  inscripción y sin calendario.

### WordPress Fase 3 — WU-09: Contact Form 7 y los párrafos del formulario en `/privacidad` (sin cambio del artefacto desplegado)

Plugin `camino-del-dharma-core` **0.7.0** y theme `camino-del-dharma` **0.5.0**. El estático de
producción no se toca: sigue sirviendo `action="#"` y su aviso sin modificar (ADR 0041 punto 4).

- **El formulario de contacto envía** (FUNC-001 / TASK-0003, abierto desde la auditoría de
  producción). Contact Form 7 6.1.7 es el único plugin de terceros aprobado (ADR 0025/0026) y su
  código **no** viaja en Git: el repositorio versiona la *definición* que el plugin propio
  provisiona — el maquetado publicado con los tres controles como form-tags, el correo a
  `caminodeldharma1@gmail.com` con `Reply-To` del visitante, y los mensajes en español.
- **El botón publicado sobrevive**: `[submit]` de CF7 solo imprime un `<input>`, así que se
  conserva el `<button>` con su icono de envío, que acciona CF7 igual por el evento `submit`.
- **`wp cdd-core contact provision`**: create-missing-only e idempotente. Rehúsa mientras CF7 esté
  inactivo o mientras `/privacidad` no describa un envío real — el gate de ADR 0041 punto 3 es
  código, no una nota.
- **Delta de copy de `/privacidad` (solo WordPress, ADR 0041)**: recuadro provisional sin la
  cláusula del formulario, viñeta del resumen, §2.2 reescrita a los hechos, el disparador ya
  cumplido fuera de §8 y la fecha del cambio. El sello «Documento provisional» se conserva y el
  resto del aviso —cookies, analítica, embeds, donaciones, derechos, Ley 1581— no se toca.
- **Fallback operativo implementado** (ADR 0041 punto 5): con CF7 apagado, `/contacto` imprime los
  canales WhatsApp y correo en vez de un shortcode en crudo.
- **La entrega real sigue sin verificar**: en Docker `wp_mail()` falla por falta de MTA. La
  validación está probada de extremo a extremo con datos sintéticos; la recepción en
  `caminodeldharma1@gmail.com` se comprueba en staging Hostinger antes del release.

### WordPress Fase 3 — WU-08B: SEO, redirects, huérfanos y a11y (sin cambio del artefacto desplegado)

Plugin `camino-del-dharma-core` **0.6.0** y theme `camino-del-dharma` **0.4.0**. El estático de
producción no se toca.

- **SEO first-party, sin plugin de terceros** (ADR 0025/0030, doc 15 §12): el plugin resuelve
  título, description, keywords, canonical, Open Graph, Twitter y JSON-LD de cada petición; el
  theme lo imprime y lo escapa. El copy publicado deja de ser HTML congelado y pasa a meta
  editable (`seo_title`, `seo_description`, `seo_keywords`, `og_title`, `og_description`) en
  `page`, `post` y `event`; la cabeza del archivo `/eventos`, los defaults sociales y el `@graph`
  del Inicio viven en la opción `cdd_core_seo_site`. Todo URL guardada se rebasa a `home_url()`.
- **JSON-LD con datos reales**: los 10 eventos emiten `Event`; los finalizados usan
  `EventCompleted` y **no** anuncian inscripción. Las entradas emiten `BlogPosting` con autores
  `Thing` de la relación `authors` (ADR 0037) y la Organization del sitio como publisher; las
  fichas emiten `Thing`. Ningún campo opcional se inventa.
- **noindex, follow** en `/author`, términos de álbum, tags del blog y el 404 (ADR 0031/0036/0037).
  El `.ics` mantiene `X-Robots-Tag: noindex, nofollow` y se enlaza `rel="alternate"
  type="text/calendar"` solo mientras el evento es vigente (OWN-014).
- **Sitemap nativo** `/wp-sitemap.xml` sin proveedor de usuarios ni taxonomías, con la URL del
  archivo `/eventos` añadida.
- **`wordpress/.htaccess`**: ledger de redirecciones portado encima del bloque que WordPress
  reescribe, verificado sobre Apache real. Añade el 301 de `sitemap.xml`; no porta las reglas
  solo-estáticas ni el bucle latente de la condición HTTPS.
- **wp-admin «Eliminar huérfanos»** (OWN-015): lista los `.ics` que ningún evento vigente respalda
  y los borra tras confirmar, con nonce y capacidad. Fotos, audios y carteles nunca entran.
- **Accesibilidad (docs/19)**: `<html lang="es-CO">` en cualquier entorno, `h1` en los archivos
  `/author` y `/blog/tag/{slug}`, un solo skip link (se retira el del núcleo) y `focusable="false"`
  en los SVG decorativos.
- `migration/payload.json` regenerado: mismos `counts` y misma fuente (VERSION 1.0.35, commit
  `bfb6dc0`); `seo` sustituye a `head_title`/`meta_description`, los eventos ganan
  `attendance_mode` y `jsonld_extra`, y aparece la sección no contada `site`.

### WordPress Fase 3 — WU-08A: comportamiento front (sin cambio del artefacto desplegado)

Plugin `camino-del-dharma-core` **0.5.0** y theme `camino-del-dharma` **0.3.0**. El estático de
producción no se toca.

- **Diálogo «Compartir»**: `share.js` portado literal al theme; nuevos bloques dinámicos
  `camino-del-dharma/evento-acciones` y `camino-del-dharma/entrada-compartir`. El copy
  hand-written de WhatsApp/X/Threads que publica el estático deja de ser HTML congelado y pasa
  a meta editable `share_whatsapp`/`share_x`/`share_threads` en `event` y `post`, extraída al
  payload y sembrada por el importador (al crear) o por `migrate convert --payload=<path>`
  (add-only, nunca pisa una edición de wp-admin).
- **Diálogo «Añadir al calendario»**: mitad restante de `calendar.js` portada como
  `calendar-dialog.js`. Diálogo y `.ics` leen el mismo `cdd_core_event_calendar_payload()`, así
  que los enlaces de Google/Outlook y el archivo descargado no pueden divergir. Ambos controles
  solo aparecen en eventos vigentes (OWN-012).
- **Audio de mantras**: `/practica` convierte sus dos reproductores hechos a mano en bloques
  `core/audio` nativos ligados a la biblioteca; el nombre accesible se restaura en presentación
  desde el `figcaption`.
- `migration/payload.json` regenerado: mismos `counts` y misma fuente (VERSION 1.0.35, commit
  `bfb6dc0`); el único delta es el nuevo campo `share`.

### Gobernanza — WU-08 partido en 08A / 08B (FABLE5 v2.5, sin cambio del artefacto desplegado)

WU-08 se parte: 08A comportamiento front (Opus, sin pegar FABLE5); 08B SEO/redirects/OWN-015/a11y
(Opus + FABLE5 §9.5 y §10 solamente). No mezclar en el mismo chat.

### Gobernanza — CF7 en el corte sin espera legal (ADR 0041 / OWN-018, sin cambio del artefacto desplegado)

Contact Form 7 es elegible en el corte a WordPress. El disclaimer publicado en `/privacidad`
basta para lanzar; la revisión legal queda como trabajo posterior, no como gate. En WordPress
(WU-09) se actualizan solo los párrafos del formulario. El HTML estático no cambia: allí el
formulario sigue sin enviar. FABLE5 v2.5. Backlog v1.21.

### Fase 3 — WU-07: plantillas FSE reales, bloques dinámicos y conversión de contenido (sin cambio del artefacto desplegado)

Theme `camino-del-dharma` 0.1.0 → **0.2.0** y `camino-del-dharma-core` 0.3.0 → **0.4.0**,
con TDD (RED en ambas suites antes del primer archivo de vistas, ADR 0038). Theme: 16
plantillas de bloques (docs/12 §5–§6), parts header/footer vía patterns PHP con el markup
publicado, 11 bloques dinámicos (calendario de eventos con paridad byte a byte contra el
grid publicado; listado vigentes/finalizados con tarjeta compacta doc 03 §3; nota destacada
del Inicio con su estado vacío; tipo/meta/CTA de evento; byline «Por …» enlazada a las
fichas ADR 0037 con bio y tiempo de lectura; listados del blog; ficha de autor con sus
entradas vía relación `authors`; galería nativa por álbum con lightbox), CSS estático
portado íntegro a presets (`--wp--preset/custom/style--*`), fuentes autohospedadas como
`fontFace` (incluye subset MarloweEscapade), `main.js` portado y tooltips del calendario.
Plugin: `wp cdd-core migrate convert` (edición field-scoped del contenido importado,
dry-run por defecto, idempotente, guard de producción — inicio dinámico + `<picture>`
desenvueltos, galerías Gutenberg por álbum ADR 0021/0036, enlaces OWN-016) y queries de
presentación (`cdd_core_past_events`, `cdd_core_posts_by_blog_author`,
`cdd_core_album_attachments`). Dos bugs latentes corregidos con regresión:
`event_modality` pasa a texto libre (el select doc 03 descartaba el copy publicado,
OWN-007) y las imágenes del Inicio rotas por `<source srcset>`/thumbs sin reescribir.
QA local verde: unit 105/105, wp-phpunit 60/60 (theme activo en el harness), PHPCS y
stylelint limpios, rutas y render verificados, `debug.log` limpio. Sustituciones y deltas
registrados en `.audit/fase3-validation-matrix.md` § WU-07. Sin despliegue: el estático
publicado no cambia.

### Fase 3 — WU-06: extractor, payload versionado, importador WP-CLI y reconciliación (sin cambio del artefacto desplegado)

`camino-del-dharma-core` 0.2.0 → **0.3.0**, con TDD (RED en ambas suites antes de
`includes/migration/`, ADR 0038). Extractor determinista de solo lectura sobre `static/`
(ADR 0032 §8.1): fechas en español, 10 eventos con los slugs ADR 0035 (JSON-LD publicado →
texto de card; cronograma de Círculos → `event_calendar_dates`; excerpt = copy `.ics` de
producción), 2 posts con bylines→fichas (ADR 0037) y hero→featured, 3 álbumes + 35 imágenes
con alt (OWN-001), 11 páginas con URLs raíz-relativas, inventario de 81 medios (71 públicos,
10 ocultos OWN-003; thumbs/PDF/`.ics` excluidos) y 5 embeds. `migration/payload.json`
versionado y determinista con `_source_key`/`_source_hash` (fuente VERSION 1.0.35, commit
`bfb6dc0`). Importador `wp cdd-core migrate validate|plan|import|verify` + `wp cdd-core
seed` (ADR 0033): dry-run por defecto, `--apply` explícito, idempotente,
create-missing-only, ediciones wp-admin intactas, guard de producción con
`--confirm-production` + `--backup-evidence`, settings de lectura y permalinks
`/blog/%postname%` (ADR 0008). Pipeline verificado contra el entorno local (109 objetos,
verify 0 missing, rutas 200/301/404/410, conteos reconciliados) y **paridad
repo↔producción verificada byte a byte (17/17 superficies, OWN-006/007: delta 0)**.
Suites: 74 unit + 43 wp-phpunit. Evidencia: `.audit/fase3-validation-matrix.md` § WU-06.

### Fase 3 — WU-05: modelos de dominio, routing y datos de calendario/ICS (sin cambio del artefacto desplegado)

`camino-del-dharma-core` 0.1.0 → **0.2.0**, nacido con TDD (RED documentado en unit y
wp-phpunit antes del primer archivo de `includes/`, ADR 0038). Dominio puro: política de
estado del evento a tiempo de request en `America/Bogota` (OWN-013: el día final sigue
vigente, `cancelado` es editorial e inmutable, extender la fecha revierte), generador
`.ics` con paridad con los archivos de producción, datos del calendario mensual (celdas de
evento con URL/tooltip, lunes de meditación semanal, mes del próximo vigente), selección
del evento del Inicio (doc 03 §3) y normalización de la relación `authors`. Registro
WordPress: CPT `event` (`/eventos`, singles sin barra final, ADR 0035/0008), taxonomías
`event_type`/`event_city` sin archivo público (ADR 0022), `gallery_album` sobre la
biblioteca de medios con `/galeria/{slug}` sin robar la Page `/galeria` (ADR 0036), CPT
`blog_author` con `query_var` aislado, rewrite `author` y capacidades propias (ADR 0037),
archivos de usuario WP en 404, meta de evento saneado (+ `event_calendar_dates` para las
sesiones que marca el calendario publicado), relación `authors` con guard de publicación
(≥1 ficha publicada; un post publicado nunca queda a cero; los legados no se despublican),
ruta generada `/eventos/ical/{slug}.ics` (200 vigente · 410 finalizado · 404 desconocido,
`X-Robots-Tag: noindex, nofollow`) y flush de rewrites solo en activación/upgrade
versionado. Suites: 44 tests unit + 34 wp-phpunit, PHPCS/WPCS limpio. Evidencia:
`.audit/fase3-validation-matrix.md` § WU-05.

### Fase 3 — WU-04: scaffold del theme FSE y baseline de tokens visuales (sin cambio del artefacto desplegado)

Primer código del theme `camino-del-dharma`, nacido con TDD (RED documentado en unit y
wp-phpunit antes del primer archivo, ADR 0038): `style.css` (solo metadata, text domain
`camino-del-dharma`), `theme.json` v3 como **baseline de paridad visual** (ADR 0029) —
paleta de marca + tints AA, roles semánticos como `settings.custom.color` con la misma
indirección del estático, familias tipográficas, escala `--space-*`, `contentSize` 65ch /
`wideSize` 70rem, ritmo y line-heights, todo verificado por `Theme_TokensTest` contra el
`:root` de `static/assets/css/main.css` extraído programáticamente —, `templates/index.html`
(fallback técnico), `parts/header|footer.html` (placeholders), `functions.php` (bootstrap:
supports + encolado de `assets/css/main.css` complementario). Política paleta-only en el
editor (sin color libre). Test guard contra plantillas PHP clásicas. `lint:css` ahora cubre
los dos árboles CSS. Theme activo en el entorno local sin warnings/fatals. Evidencia
`Pass (local)` en `.audit/fase3-validation-matrix.md` § WU-04.

### Fase 3 — WU-03: scaffold del plugin y kit de calidad TDD (sin cambio del artefacto desplegado)

Primer PHP propio del proyecto, con TDD desde la primera línea (ADR 0038):
`wordpress/wp-content/plugins/camino-del-dharma-core/camino-del-dharma-core.php` (bootstrap
mínimo: guard `ABSPATH`, `CDD_CORE_VERSION`, `CDD_CORE_PLUGIN_FILE`) nació tras un test en
rojo. Kit de calidad en la raíz: `composer.json` (PHPUnit 9.6, wp-phpunit 7.1.0 —igual que el
WordPress del compose—, polyfills, WPCS 3.4; `platform.php` 8.3.30), `phpunit.xml.dist` +
`tests/Unit/`, `phpunit-wp.xml.dist` + `tests/WordPress/` (harness Docker **efímero**
`cdd-wp-phpunit`, tablas `wptests_`, `down -v` al salir), `phpcs.xml.dist` (WPCS, prefijo
`cdd_core`, ADR 0027), `tools/` (`php-lint.sh`, `run-phpunit.sh`, `run-phpunit-wp.sh`,
`wp-tests.env` desechable) y `.github/workflows/test.yml` solo-calidad (composer test + PHPCS +
Stylelint; sin deploy, sin secretos, sin SonarScanner). El theme FSE sigue sin código (WU-04).
Evidencia `Pass (local)` en `.audit/fase3-validation-matrix.md` § WU-03.

### Fase 3 — WU-02: entorno local Docker (sin cambio del artefacto desplegado)

Entorno WordPress local de 3 servicios según ADR 0023 y `docs/docker-wordpress-playbook.md`:
`docker-compose.yml` en la raíz (MariaDB 11.8 con healthcheck, WordPress PHP 8.3 en
`127.0.0.1:${WORDPRESS_PORT:-8080}`, `wpcli` como www-data) con bind-mount solo del theme y
plugin propios; core y BD en volúmenes Docker. `.env.example` versionado, `.env` gitignored,
variables fail-fast, `WP_ENVIRONMENT_TYPE=local` y debug log en ambos servicios PHP. Banco de
pruebas local únicamente; no participa en ningún despliegue (ADR 0015). Evidencia
`Pass (local)` en `.audit/fase3-validation-matrix.md` § WU-02.

### Fase 3 iniciada — WU-00/WU-01 (sin cambio del artefacto desplegado)

Reorganización monorepo (ADR 0014) en la rama `fase3-wordpress`: la superficie desplegable se
movió de la raíz a `static/` con renames puros; el contenido del ZIP de producción es idéntico,
solo cambia el directorio desde el que se genera (README actualizado). El PDF retirado por
OWN-002 (`assets/documents/recitacion-practica-comida.pdf`) quedó archivado en
`docs/archive/recitacion-practica-comida/`, fuera de la superficie desplegable. Harness durable
de Fase 3 en `.audit/fase3-execution-state.md` y `.audit/fase3-validation-matrix.md`; runbooks en
`docs/operations/`. Tag de rollback: `fase3-pre-reorg-v1.0.35`.

## [1.0.35] - 2026-08-29

### `/privacidad` — aviso de privacidad provisional

Página nueva en el estático, enlazada en el pie de todas las páginas. El texto describe el tratamiento real de este sitio (sin cookies propias, sin analítica, formulario de contacto que no envía, WhatsApp/correo, embeds, Google Forms/Zoom cuando una actividad los usa). Queda marcado como provisional hasta asesoría legal. ADR 0039 sustituye el aplazamiento de publicación de ADR 0028; Contact Form 7 sigue gated.

- `privacidad/index.html`; enlace «Privacidad» junto al copyright del pie (17 páginas + 404).
- `sitemap.xml`: `/privacidad` `<lastmod>` `2026-08-29`.
- Estilos `.privacy-notice` en `main.css`.
- ZIP de despliegue: incluir carpeta `privacidad/` (README).

### Documentación (sin cambio de artefacto de despliegue)

- ADR 0040 / OWN-017: `content-source/` retirado; producción publicada gobierna pre-corte.
- FABLE5 v2.3, orden v3.9, backlog v1.20 y docs de migración alineados.
- `package-lock.json` sincronizado a 1.0.35.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.34] - 2026-08-18

### `/eventos` — aviso de segundo toque bajo el calendario

Misma interacción que v1.0.33, acotada al puntero grueso: primer toque = nombre, segundo = destino. Teclado sigue el enlace al primer Enter. Tras el primer toque aparece «Toca de nuevo para ver el evento.» (`aria-hidden`). El aviso reserva altura para no correr el listado.

- `.eventos-calendar-hint` con `aria-hidden="true"`; visible con `.is-tooltip-visible`.
- `calendar.js`: no intercepta `event.detail === 0`; `matchMedia('(pointer: coarse), (hover: none) and (max-width: 767px)')`.
- `sitemap.xml`: `/eventos` `<lastmod>` `2026-08-18`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.33] - 2026-08-18

### `/eventos` — tooltip de la meditación semanal en vista móvil

En viewport estrecho el nombre «Meditación semanal en línea» no se veía: el tooltip solo respondía a hover, y un toque en un lunes salía de la página.

- Primer toque en un día con `data-tooltip` (hover nulo o `max-width: 767px`) revela el nombre; el segundo sigue el enlace. Clase `.is-tooltip-visible`.
- Lunes `.has-practice`: borde `2px`. Tooltip alineado al borde izquierdo en las columnas Dom/Lun; bajo la celda en móvil.
- `sitemap.xml`: sin cambio de `<lastmod>` — copy indexable sin cambio; solo CSS/JS y comentario de implementación.

### Estado

- Desarrollo: Finalizado
- Producción: **Desplegada** el 2026-08-18 (reemplazada en producción por v1.0.34 cuando se suba el siguiente ZIP)

## [1.0.32] - 2026-08-14

### Contacto — imagen rota

El HTML pedía `contacto-comunidad.jpg` (nombre canónico del inventario) y el archivo en disco se llamaba `contacto.jpg`, así que la foto de la página devolvía 404.

- Renombrado `assets/images/contacto/contacto.jpg` → `contacto-comunidad.jpg`.
- `sitemap.xml`: `/contacto` `<lastmod>` `2026-08-14`.

### Estado

- Desarrollo: Finalizado
- Producción: **Desplegada** el 2026-08-18

## [1.0.31] - 2026-08-13

### `/eventos` — leyenda del calendario retirada

La leyenda bajo la cuadrícula del mes duplicaba lo que ya explican los tooltips (`data-tooltip`) en cada día.

- Eliminado el párrafo `.eventos-calendar-legend` en `/eventos`.
- Eliminados estilos `.eventos-calendar-legend` en `main.css` / `main.min.css`.
- `sitemap.xml`: sin cambio de `<lastmod>` — solo ajuste visual en HTML/CSS.

### Estado

- Desarrollo: Finalizado
- Producción: **Desplegada** el 2026-08-14

## [1.0.30] - 2026-08-13

### `/eventos` — calendario y tooltips

- Lunes sin otro evento (7, 14, 21 y 28 de septiembre): borde `brand-2`, enlace a `/practica/meditacion-semanal-en-linea`. No son ítems del listado.
- Tooltip propio (`data-tooltip`) en días de evento y de meditación; hover y foco. Sin `title` nativo.
- Metadata SEO: description y Open Graph mencionan la meditación semanal; `rel="related"` a esa página.
- `llms.txt`: calendario de eventos incluye la meditación semanal.
- `sitemap.xml`: sin cambio de `<lastmod>` — `/eventos` ya en `2026-08-13`.

### Estado

- Desarrollo: Finalizado
- Producción: **Desplegada** el 2026-08-13

## [1.0.29] - 2026-08-13

### Inicio — refinamientos del módulo de evento vigente

- Cartel del evento enlazado como atajo de puntero (`evento-figure-link`, `tabindex="-1"`, `aria-hidden="true"`); teclado y lector siguen usando título y «Ver evento».
- `data-event-status="vigente"` y `data-event-featured="true"` en Inicio, `/eventos` y ficha de Círculos de Presencia Consciente.
- Círculos marcado `event_featured`: un vigente con fecha más cercana **no** lo sustituye en Inicio mientras siga vigente.
- CSS: margen del enlace del cartel separado de la miniatura.
- `sitemap.xml`: sin cambio de `<lastmod>` — fechas `2026-08-13` ya vigentes.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.28] - 2026-08-13

### Inicio — nota del evento vigente

Un solo evento vigente junto a «Un poco de nuestra comunidad»: atajo a esa ficha, no un segundo listado.

- Rótulo «Próximo evento · Curso» encima del cartel (`<p>`, alineado al `h2`); cartel completo a ancho de columna WordPress `medium` (~300 px); título, fecha, lugar y **Ver evento** (solo la ficha; sin enlace al listado).
- Sin caja; filete izquierdo en escritorio. El borde derecho del módulo coincide con el del hero.
- Miniatura decorativa (`alt=""`); teclado y lector usan el título y «Ver evento». Un destacado finalizado no aparece; si no hay vigentes, se omite el módulo.
- SEO/AEO de Inicio: descripción y Open Graph incluyen **cursos**; keywords institucionales (`cursos budistas`); JSON-LD `WebPage.mentions` y `rel="related"` a la ficha vigente. El JSON-LD `Event` sigue solo en `/eventos/circulos-de-presencia-consciente/`.
- `sitemap.xml`: `/` ya tenía `<lastmod>` `2026-08-13`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.27] - 2026-08-13

### Círculos de Presencia Consciente — «Taller» → «Curso»

Alineación editorial y SEO: el formato es un **curso**, no un taller.

- `/eventos/circulos-de-presencia-consciente`: tipo visible **Curso**; títulos, descriptions, Open Graph y JSON-LD `Event` con `additionalType` `Course` y `alternateName`.
- `/blog/circulos-de-presencia-consciente`: metadata SEO y JSON-LD actualizados; autor visible **Comunidad Camino del Dharma** (antes atribuido a Zheng Gong en esta entrada).
- `/eventos`, `/` («Del blog»), `llms.txt`, `.ics`: copy y descripciones alineadas.
- `sitemap.xml`: sin cambio de `<lastmod>` — fechas `2026-08-13` ya vigentes en las URLs modificadas.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.26] - 2026-08-13

### Corrección — enlaces de navegación en blog y eventos

Enlaces relativos (`href=".."`) en páginas anidadas resolvían mal la ruta del listado padre (p. ej. `/blog/circulos-de-presencia-consciente` → `/blog/circulos-de-presencia-consciente` en lugar de `/blog`).

- `blog/circulos-de-presencia-consciente/index.html`, `blog/sangha-refugio-hiperconexion/index.html`: **Blog** y «Ver más entradas del blog» → `/blog`.
- `eventos/circulos-de-presencia-consciente/index.html`, `eventos/encuentro-nacional-2026/index.html`, `eventos/pausa-profunda-cali/index.html`: **Eventos** → `/eventos`.
- `sitemap.xml`: `<lastmod>` `2026-08-13` en `/eventos/pausa-profunda-cali` y `/blog/sangha-refugio-hiperconexion`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.25] - 2026-08-13

### Evento — Círculos de Presencia Consciente

Ficha de evento operativa y enlazada al artículo del blog publicado en v1.0.24.

- `/eventos/circulos-de-presencia-consciente`: página completa con metadata SEO, JSON-LD `Event` (proceso híbrido septiembre–octubre 2026, Bogotá y Cali), cartel (`assets/images/eventos/evento-circulos-de-presencia-consciente.jpg`) y botón **Inscribirme** al formulario de Google.
- `/eventos/ical/circulos-de-presencia-consciente.ics`: descarga de calendario para la sesión de bienvenida.
- `/eventos`: evento vigente en primer lugar; calendario actualizado a **Septiembre 2026**; 7.º Encuentro Nacional movido a eventos pasados.
- `/blog/circulos-de-presencia-consciente`: URLs de preinscripción activas, metadata SEO refinada, JSON-LD `BlogPosting` enlazado al `Event`, botón **Ver evento** y enlace cruzado con la ficha.
- `llms.txt`: entradas del evento y del artículo como fuentes oficiales.
- `sitemap.xml`: `<lastmod>` `2026-08-13` en `/eventos`, `/eventos/circulos-de-presencia-consciente` y páginas del blog ya actualizadas en v1.0.24.

### Estado

- Desarrollo: Finalizado
- Producción: **Desplegada** el 2026-08-13

### Blog — Círculos de Presencia Consciente

Segunda entrada del blog, publicada el 13 de agosto de 2026. Nota de Zheng Gong sobre el proceso de formación en Bogotá y Cali (sesiones virtuales de septiembre y encuentros presenciales en octubre), con becas completas.

- `/blog/circulos-de-presencia-consciente`: artículo completo, metadata SEO (canonical, Open Graph, Twitter Card) y JSON-LD `BlogPosting` con el registro fijo de `Person` del Venerable Maestro Zheng Gong.
- Imagen destacada 3:2 (`assets/images/blog/circulos-de-presencia-consciente.jpg`, 1024×682).
- Listado `/blog` e inicio («Del blog»): la entrada nueva va primero.
- Tres botones de preinscripción (`Quiero preinscribirme` ×2, `Realizar mi preinscripción`) abren en pestaña nueva; el `href` queda vacío hasta que exista el formulario.
- `sitemap.xml`: `<lastmod>` `2026-08-13` en `/`, `/blog` y la URL nueva.

### Estado

- Desarrollo: Finalizado
- Producción: **No desplegada** — superseded por v1.0.25 (ficha de evento y formulario añadidos después del tag)

## [1.0.23] - 2026-07-28

### `llms.txt` — datos institucionales para agentes de IA

Al consultar a agentes de IA sobre la fundación seguían respondiendo **2012**, atribuyéndolo a «estudios académicos y directorios sobre el budismo en Colombia». Verificado que producción ya sirve el dato correcto (`foundingDate` `2019` y el texto visible), así que no es un fallo del sitio: los agentes responden desde índices rastreados antes del despliegue. **Pero las fuentes secundarias seguirán publicando 2012 aunque Google vuelva a rastrear**, y para datos institucionales los modelos les dan mucho peso.

`llms.txt` —el archivo dirigido precisamente a ese consumidor— **no mencionaba la fundación en absoluto**:

- Nuevo bloque **Datos institucionales**: fundación (Colombia, 2019, sin ciudad única), fundador, tradición y Personería Jurídica Especial.
- Fundación añadida también al párrafo de presentación.
- En *Guidance for AI Agents*: se declara este archivo y el sitio como fuente autorizada para datos institucionales frente a descripciones de terceros, y se advierte de forma explícita que **las fuentes secundarias que datan la fundación en 2012 en Cali son incorrectas**, con el equivalente legible por máquina (`Organization.foundingDate` / `foundingLocation`).

No afecta a `sitemap.xml`: `llms.txt` no figura en él.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.22] - 2026-07-28

### Corrección del dato de fundación (Colombia, 2019)

El sitio afirmaba que la comunidad fue **«fundada en Cali en 2012»**. Es falso. Corrección del Venerable Maestro Zheng Gong: **la comunidad se fundó en Colombia, en 2019**, y no en una sola ciudad.

- `index.html`: JSON-LD `Organization` — `foundingDate` `2012` → **`2019`**; `foundingLocation` pasa de `Place` «Cali, Colombia» a **`Country` «Colombia»**.
- `index.html`: párrafo de «Quiénes somos» — «nacida en Cali en 2012, con presencia en Colombia» → **«fundada en Colombia en 2019, con presencia en distintas ciudades del país»** (se evita además repetir «Colombia» en la misma frase).
- `comunidad/index.html`: «Fundada en Cali en 2012» → **«Fundada en Colombia en 2019»**.
- `sitemap.xml`: `<lastmod>` `2026-07-28` en `/` y `/comunidad`.

**De dónde venía el error.** No de la comunidad: el dato se tomó del artículo de Buddhistdoor (`EVID-0035`, «founded 2012, Cali») y se publicó sin confirmarlo. `working/seo-external.md` §6.3 lo justificaba precisamente como «señal local **verificable por terceros**» — pero eso medía que el dato era *citable*, no que fuera *cierto*. El contenido fuente del proyecto (`content-source/`) nunca afirmó ni la ciudad ni el año.

**Consecuencia para la estrategia SEO:** la fundación **deja de ser una señal local de ciudad**, y se cae la premisa que apoyaba «budismo cali» en el origen de la comunidad. Sin efecto sobre el plan vigente: las consultas locales ya habían dejado de contabilizarse como brecha alcanzable al descartarse Google Business Profile el 2026-07-21, y la vía local en pie son los encuentros presenciales reales por ciudad.

**Pendiente con terceros:** Buddhistdoor sigue publicando «2012, Cali». Pedir la corrección es una **gestión nueva** — las peticiones de enlace de TASK-0014 se enviaron el 2026-07-20.

### Documentación

- `docs/informes-seo/02-auditoria-seo-tecnica.md`: corregidos la descripción del sitio (§ El sitio), la tabla de datos estructurados y el apartado de decisiones deliberadas, con nota de corrección fechada. `.docx` regenerado.
- `.audit/manual-inputs-howto.md`: tabla de datos del proyecto y **plantilla de alta en el directorio budismo.com** — esta última habría propagado el dato erróneo a un tercero.
- `.audit/executive-summary.md`, `.audit/working/seo-external.md` (§4.3, §5, §6 y nueva §12), `.audit/working/url-hypotheses.md`, `TASK-0013` y `TASK-0020` (ficha y `tasks.jsonl`).
- `.audit/decisions.md`: decisión registrada con el origen del error y la lección — un dato institucional (fundación, sede, fundador) se confirma con la organización, nunca se publica desde una fuente de terceros.

### Consolidación de entidades en el JSON-LD

El Maestro Zheng Gong aparecía **8 veces como `Person` suelta con tres nombres distintos** («Venerable Maestro Zheng Gong» ×2, «Maestro Zheng Gong» ×5, «Zheng Gong» ×1 en el blog), ninguna con `@id`: para un buscador podían ser tres personas. Los `organizer`/`publisher` incrustados sumaban otros 8 nodos `Organization` con dos nombres, ninguno apuntando al `@id` que ya existía en la portada.

- **Un solo `@id` por entidad:** `#zhenggong` y `#organization`. Ahora los **10 nodos `Person`** y los **11 `Organization`** del sitio comparten identificador y nombre canónico. El nombre canónico es «Venerable Maestro Zheng Gong» —el que usa `/comunidad`, su página de biografía— con `alternateName` para las otras dos formas, de modo que las menciones visibles («Por Zheng Gong» en el blog, «Maestro Zheng Gong» en eventos) siguen respaldadas.
- **`founder` declarado.** `Organization` no decía quién la fundó, pese a que `/comunidad` tiene la sección «Nuestro fundador». Añadido `founder` → `#zhenggong`, y `worksFor` en sentido inverso.
- **Nodo `Person` completo** en `index.html` y en `comunidad/index.html`, con descripción, foto de la biografía y `knowsAbout`. Se define en cada página en lugar de referenciarse desde otra: Google evalúa los datos estructurados **página a página**, así que una referencia suelta a un `@id` externo no resolvería.
- **`comunidad/index.html`:** `about` pasa de un `Thing` genérico («comunidad budista en Colombia», una palabra clave) a referenciar la `Organization` real. Mismo criterio por el que la auditoría retiró el `alternateName` de keyword en la 1.0.12.
- `sitemap.xml`: `<lastmod>` `2026-07-28` en las cinco páginas restantes con JSON-LD modificado.

**Por qué importa:** conecta con **ASO-001** — el AI Overview de marca no cita el sitio pese a ser #1 orgánico. Con DA 2, que Google resuelva «una organización, un maestro» pesa más que cualquier retoque de texto. Verificado: los 15 bloques del sitio parsean, `Event` y `BlogPosting` conservan sus campos obligatorios, y el `logo` del `publisher` del blog sigue intacto.

### Evidencia de actividad por ciudad (TASK-0020) rehecha

La tabla de evidencia procedía del análisis del 19–20 de julio, **anterior al archivo de encuentros**, y estaba desfasada en las cuatro ciudades. Rehecha desde el inventario completo de `/eventos` (9 tarjetas):

| Ciudad | Encuentros presenciales documentados | Decía antes |
| --- | --- | --- |
| **Cali** | **2** — 6.º Encuentro Nacional (16–18 ago 2025, 3 días) · Pausa Profunda (15 feb 2026) | «fundación 2012» — dato falso |
| **Bogotá** | **2** — Vesak 2026 (9 may) · Festival Calma en la Ciudad (28 jun) | «**sin evidencia** en el sitio» |
| **Medellín** | **2** — «Ansiedad, agotamiento…» (22 may) · Pausa Profunda (23 may) | «solo mención en Facebook» |
| **Barranquilla** | **1 + 1 próximo** — Meditación Presencial (9 jul) · 7.º Encuentro Nacional (7–9 ago, Puerto Colombia) | solo el Encuentro Nacional |

Más una conferencia en línea sin ciudad («Buddhismo para tiempos de cansancio», 23 ene 2026), que no figuraba.

**«Bogotá sin evidencia» y «Medellín solo mención en Facebook» eran afirmaciones falsas:** ambas ciudades tienen dos encuentros con JSON-LD completo en el propio sitio. **Matiz nuevo:** los Encuentros Nacionales **rotan de ciudad** (Cali 2025 → Puerto Colombia 2026), luego son señal de actividad nacional itinerante, no de arraigo local en la ciudad que los aloja. **Nota sobre el recuento de ADR 0022:** decía «son 5» contando solo los recién archivados y omitiendo los dos de Cali — el total real de pasados es **8**, más uno próximo; su conclusión («ninguna ciudad pasa de dos») no cambia.

TASK-0020 sigue BLOCKED: lo que falta es la confirmación de la comunidad sobre qué ciudades tienen actividad **sostenida** — 2–3 encuentros al año no son sangha permanente —, no el inventario.

### Trazabilidad de Google Business Profile (dos huecos, detectados al revisar lo anterior)

- **`decisions.md` no tenía la decisión.** GBP se descartó el **2026-07-21** por inelegibilidad —la comunidad no tiene sede ni dirección física, y Google excluye a las entidades exclusivamente en línea— y se propagó a nueve documentos. Pero las cinco banderas de corrección que lo anuncian remiten a «Ver `decisions.md`», y ese archivo **nunca recibió la entrada**: sus dos únicas menciones a GBP son del 2026-07-20 y lo tratan como pendiente. Añadida ahora la entrada canónica, fechada en su día y marcada como registro retroactivo. **No es un ADR:** los ADR 0001–0022 no cubren GBP.
- **TASK-0020 seguía bloqueada por esa decisión ya tomada.** Su ficha exigía «resolver antes la decisión de GBP (TASK-0014)», con `depends_on: TASK-0014`, prerrequisito propio y `deployment_sequence: "Tras TASK-0014 (GBP)"`. Retirado todo: **`depends_on` queda en `TASK-0022`**, que es el bloqueo real (historial de encuentros por ciudad). Anotado también el apartado «Prioridad relativa: primero el perfil de empresa» de `working/url-hypotheses.md`, cuyo razonamiento se invierte: sin pack local alcanzable, las páginas por ciudad dejan de ser «la opción de debajo» y pasan a ser la vía local principal.

**No se reescribe la historia:** la entrada 1.0.12 se deja intacta (registra lo que se hizo entonces) y **`evidence-ledger.jsonl` no se toca** — `EVID-0035` es el registro fiel de lo que Buddhistdoor publica, y sigue siendo cierto que lo publica. Se respeta la convención de evidencia congelada declarada en 1.0.20.

### Estado

- Desarrollo: Finalizado
- Producción: **Desplegada** el 2026-07-28 (verificado en vivo: `foundingDate` `2019` y `@id` `#zhenggong` presentes en producción)

## [1.0.21] - 2026-07-23

### Meditación semanal — copy y metadatos

- `practica/meditacion-semanal-en-linea/index.html`: descripción más concisa (retirado «guiada» redundante); estructura de la sesión aclarada en el cuerpo; JSON-LD alineado.
- `llms.txt`: descripción de la meditación semanal sincronizada.
- `sitemap.xml`: `<lastmod>` `2026-07-23` en `/practica/meditacion-semanal-en-linea`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.20] - 2026-07-21

### Galería — miniaturas en el grid (cierra PERF-001)

- `assets/js/gallery.js`: el grid deja de servir los originales y usa `assets/images/galeria/thumbs/` mediante `<picture>` con `srcset` 300w/600w. Antes cada página de 12 imágenes entregaba **~2 MB** para teselas de ~285 px; ahora **~216 KB** en móvil.
- `assets/images/galeria/thumbs/`: miniaturas para las 36 imágenes (600 px jpg + webp, y 300 px webp).
- Si una miniatura no fuese derivable del nombre, `gallery.js` cae al original: peor peso, pero nunca un hueco roto.
- `sitemap.xml`: **sin cambio de `<lastmod>`** — cambio de assets y JS, no de contenido indexable en `/galeria`.

**Corrección de un diagnóstico previo:** en v1.0.17 se justificó conservar los originales porque `/galeria` los necesitaría para un visor ampliado. **Era falso** — `gallery.js` no tiene visor ni manejador de clic sobre las imágenes; los únicos son de paginación. Los originales se servían como simples teselas.

### Auditoría e informes

- `.audit`: **tres tareas figuraban COMPLETED sin cumplir su Definition of Done** (mismo patrón ya detectado en TASK-0017). TASK-0007 reatribuida a v1.0.19; **TASK-0010 y TASK-0011 revertidas a NOT DONE**. PERF-001 pasa a RESOLVED; PERF-002 y AEO-001 siguen abiertos. Detalle y evidencia en `.audit/README.md` y `.audit/implementation/results/DEPLOY-v1.0.14.md`.
- `docs/informes-seo/`: nueva §16 en el informe técnico con el trabajo de rendimiento, y corrección de estados en §8 y §12. Las cifras de Lighthouse del 20 de julio **se conservan fechadas**, sin recalcular: la cuota de la API de PageSpeed estaba agotada.
- **No se modificó `.audit/raw/` ni `evidence-ledger.jsonl`**: son la evidencia congelada de la auditoría del 2026-07-19.

### Documentación de WordPress — decisión sobre el visor ampliado

- **ADR 0021 (nuevo):** el lightbox de la galería será el **nativo del bloque de Gutenberg** (WP 6.4+). No se implementa visor propio en la maqueta estática ni se instalará plugin. Fija además la arquitectura de imágenes: miniaturas para el grid, originales para el visor — por eso los 36 originales **no se borran**.
- `03-wordpress-content-model.md` §5.1 (nueva): cómo se modela la galería en WordPress y qué se migra.
- `12-theme-file-structure.md`: `gallery.js` **no viaja al tema**; la galería pasa a bloque de Gutenberg.
- `15-assets-strategy.md`, `17-orden-implementacion.md`, `19-accesibilidad-estandares.md`, `migracion-static-wordpress.md`: alineados con la decisión.
- Nota de orden registrada: si alguna vez se añadiera un visor propio a la maqueta, **primero** hay que cerrar TASK-0010 (AEO-001), o se agrava la dependencia de JavaScript que ese hallazgo señala.

### Informes SEO — política de privacidad

- Precisado en ambos informes por qué **la política de privacidad sigue siendo recomendable aunque el sitio no use cookies**: la Ley 1581/2012 cubre el tratamiento de datos personales en general, y tras retirarse el formulario sin backend (FUNC-001) el contacto por WhatsApp y correo sigue recogiendo nombre, teléfono y mensajes. La recogida no desapareció con las cookies — cambió de canal.
- Nuevo apartado en §9 del informe técnico; entrada en §5 del informe general (donde se pide lo que depende de la comunidad); hallazgo **PRIV-001b** separado del de los vídeos; y fila propia en el plan de acción de §12, que antes solo recogía la mitad de PRIV-001.
- En los tres sitios queda explícito que **la conclusión jurídica corresponde a asesoría legal, no a la auditoría técnica**.
- **RGPD añadido al análisis.** Search Console documenta visitantes desde España (1 clic / 2 impresiones de 9 / 35 totales), así que los informes plantean también esa norma. Se separan las dos vías del art. 3.2: *observar el comportamiento* **no aplica y es demostrable** (cero cookies, sin analítica, sin perfilado — ADR 0019 protege también en este frente); *ofrecer servicios* se deja planteada con sus elementos, señalando que la meditación por Zoom no tiene restricción geográfica. **No se emite conclusión jurídica**: se aportan los hechos verificables para que la valoración se haga sobre datos.

### Documentación — `/privacidad` prevista, y un faltante detectado

- **`/privacidad` dada de alta en la documentación** (aún sin publicar): `11-arbol-urls-final` (páginas fijas, árbol y URL→plantilla), `04-mapa-pantallas` (páginas fijas, conjunto total y plantillas), `05-arquitectura-informacion-navegacion` (enlace en el pie, nunca en el menú) y `03-wordpress-content-model` (la cubre `page.php`, sin plantilla propia).
- **Faltante detectado de paso:** `/practica/meditacion-semanal-en-linea` **no estaba documentada** en `11-arbol-urls-final` ni en `04-mapa-pantallas`, pese a estar publicada desde el 21/07 y presente en `sitemap.xml` y `llms.txt`. Añadida a ambos.
- Informe 00 §5: la sección de privacidad se reescribió para el público de liderazgo — empieza explicando **qué es** una política de privacidad y **cómo se vería en el sitio** (página nueva, enlace en el pie, sin banners). Informe 02 §9: nueva subsección **«Dónde y cómo se publica»** con los criterios de aceptación de PRIV-001.

### Informe 00 — precisiones de redacción

- **§5 separa el requisito de la decisión.** Decía «¿En qué ciudades hay práctica real?» como si el dato resolviera el asunto. No lo resuelve: el dato es el **paso 1** (requisito, solo obtenible dentro de la comunidad) y la decisión —**a qué ciudades se les creará página**— es el paso 2. **Que haya práctica real habilita la página, no obliga a crearla.** Se añadió el riesgo inverso, antes ausente: una página sobre una ciudad con poco que decir queda vacía aunque la actividad sea real. §1 alineado con este cambio.
- **Registro impersonal.** «Se necesita de la comunidad» → «Se necesita»; «Lo que necesito de ustedes» → «Lo que se necesita»; encabezado de §5 e índice ajustados en consecuencia. §5 ya usaba la forma impersonal en su otra subsección.
- **Se eliminaron** el párrafo «Lo que cuesta» de §1 y toda la subsección «Lo que ya se corrigió» de §2.
- **Enlace a la fuente oficial de la Ley 1581 de 2012**, en la cita principal de cada documento (informe 00 §5, informe 02 §9 y ADR 0019). La fuente principal es el **PDF de la Superintendencia de Industria y Comercio**, autoridad de protección de datos, servido por HTTPS. El informe técnico enlaza además la versión de la **Secretaría del Senado**, que aporta vigencia expresa y control de constitucionalidad, con la advertencia de que ese sitio **solo responde por HTTP**.
  - *Procedencia de la verificación:* el enlace del Senado se comprobó automáticamente (HTTP 200, documento correcto). El de la SIC **lo confirmó el propietario de forma manual**: ese host no responde desde el entorno de trabajo. Una ruta anterior que se había supuesto para la SIC resultó incorrecta y se descartó por no poder verificarse.
- **§6 explica por qué el contenido nuevo mueve la visibilidad.** El informe señalaba el volumen de contenido como una de las dos causas de la baja visibilidad (§1, §3) y listaba acciones editoriales en §6, pero **nunca explicaba el mecanismo**: por qué un artículo más cambia algo. La nueva subsección lo cubre sin salir del documento — cada artículo es una puerta de entrada nueva, y conecta con la brecha ya medida en §2 (el sitio gana «budismo chan colombia» y pierde «dónde practicar budismo chan en Colombia»).
  - Incluye la precisión sobre **frecuencia**: publicar seguido **no es un factor de posicionamiento**; lo que cuenta es el acumulado de páginas útiles, y publicar de forma irregular no penaliza. Se añade la única restricción real de calendario: para que la medición del 17/08–14/09 diga algo sobre contenido, lo publicado debe llevar semanas arriba.
  - La fila «Depende de: *Ritmo editorial*» remitía a un concepto no definido en ninguna parte; ahora dice «Decisión de publicar y con qué ritmo».
  - **Sin remisión al brief editorial:** es un entregable para otra audiencia, y el `README` de la carpeta exige que cada informe se entienda por sí solo.
- **Autoría en los tres entregables.** `informes-seo/00`, `informes-seo/02` y `24-brief-editorial-blog-y-visibilidad` acreditan ahora a **Rafael Figueredo Oropeza**, con LinkedIn y correo, en la cabecera y en la nota de cierre. Los datos se tomaron del pie del sitio; se usaron los profesionales (LinkedIn y `refo44@gmail.com`) y **no** el Instagram personal.
- **Recomendación de frecuencia de publicación, con evidencia: una pieza cada 3–4 semanas.** El desarrollo completo —cuatro hechos con fuente, razonamiento y orden de las primeras piezas— va en el **brief editorial §6.1**, que es el documento de quien decide qué y cuándo escribir. El **informe 00 §6 conserva solo lo indispensable**: la cifra, por qué no conviene ir más rápido y la advertencia de plazos. **Las fuentes viven únicamente en el brief**, que es donde se decide el ritmo; el informe ejecutivo no las carga y atribuye a Google en el propio texto.
  - Evidencia: el ritmo no es señal de posicionamiento (Google/Mueller); el presupuesto de rastreo solo aplica desde ~10.000 páginas (documentación de Google — el sitio tiene 14); una página tarda **2–6 meses** en asentarse (Ahrefs/Semrush); el volumen sin valor está tipificado como *scaled content abuse* (políticas de spam de Google).
  - **§5 muestra ahora cómo quedarían las direcciones** de las páginas por ciudad, que era la parte que faltaba para poder decidir. Dos opciones con ejemplo real: artículo en `caminodeldharma.org/blog/budismo-en-cali` (liviano y reversible) o sección propia en `caminodeldharma.org/sanghas/cali` (sección que hoy no existe, con compromiso de mantenimiento indefinido). **Se recomienda empezar por el blog:** una dirección permanente promete una presencia estable que conviene demostrar antes de comprometer. Coherente con el brief, que acota su alcance a `/blog/` y aclara que no pide crear `/sanghas/`.
  - **Aclarado el plazo de «2 a 6 meses», que se malinterpretaba** (y luego recortado por sobreexplicado). Podía leerse como que el artículo tarda meses en estar disponible, cuando en la práctica se publica y se comparte por WhatsApp el mismo día sin problema. Ahora ambos documentos separan los dos canales: **difundir es inmediato y no depende del SEO**; lo que tarda de 2 a 6 meses es que **Google muestre el artículo a quien busca el tema sin conocer a la comunidad**. Compartir alcanza a quien ya está cerca; el buscador trae desconocidos, y ese es el camino lento.
  - **La tabla de acciones de §6 ahora lleva el ritmo y las búsquedas.** Antes solo decía «Editorial · Depende de: decisión de publicar», sin cifra ni destino: quien escaneaba la tabla no veía ninguna recomendación. Ahora cada fila nombra **qué búsqueda ataca** y bajo la tabla va el ritmo sugerido.
  - **Faltaba la acción más importante.** La prosa llamaba a la pieza sobre la meditación en línea «la mayor brecha», pero **no era una fila de la tabla**: solo estaban «pregunta y respuesta» y «por ciudad». Añadida como primera fila.
  - **Cierra el «calendario fantasma».** `23-sistema-editorial` remitía cuatro veces a un calendario del brief §6 que no existía, incluido un ítem de checklist imposible de marcar. Ahora existe (§6.1) y las referencias del doc 23 se precisaron para apuntar ahí.
  - **Corrige una afirmación previa mía.** Había escrito que bastaba publicar «con varias semanas de anticipación» para que la medición de septiembre dijera algo del contenido. Con un horizonte de 2 a 6 meses, **eso es falso**. §8 se ajustó: el hito de ~15 de septiembre pasa a medir **solo enlaces y autoridad**, y se añade un hito de **~diciembre–enero** para el contenido. Medir contenido en septiembre habría producido la conclusión falsa de que no funcionó.
- **Corregida una confusión conceptual en §5.** El texto presentaba la política de privacidad como «una página más del sitio». **No lo es:** la política es un **compromiso escrito** sobre cómo se tratan los datos personales; publicarla como página es solo la forma de darla a conocer. La distinción tiene consecuencia práctica — el compromiso cubre también los datos que llegan por WhatsApp y correo y quedan en un teléfono o una bandeja de entrada, es decir **fuera del sitio web**. Por eso no es una tarea del equipo web: es una decisión de la comunidad, y el equipo web solo publica el texto.
- **Aclarada la frase que explica la causa (§1).** Decía *«la comunidad recibe menciones […] pero ninguna recomendación enlazada»*: obligaba a desandar la metáfora (enlace = recomendación) para entenderla. Ahora separa las dos cosas — la comunidad **sí** recibe menciones, pero la nombran **sin poner la dirección de la web**, y una mención sin enlace no cuenta para Google. Es la frase que sostiene el diagnóstico central del informe.
- **Corregida una imprecisión de posicionamiento.** El informe afirmaba ser primero «en chan y tierra pura» (§2) y tener posiciones estables en «budismo chan» y «budismo tierra pura» (§9). Las consultas realmente medidas llevan el país: **«budismo chan colombia»** y **«budismo tierra pura colombia»**. Sin ese calificador la afirmación se lee como liderazgo en el tema a nivel general, que no es lo medido. El resumen ejecutivo del audit (`.audit/executive-summary.md`) sí lo decía bien; el calificador se perdió al adaptarlo al entregable.

### Documentación — la ciudad es taxonomía, no URL (ADR 0022)

- **ADR 0022 (nuevo):** no se crearán URLs de filtro por ciudad para eventos (`/eventos/cali`, `/eventos/ciudad/cali`). Queda **derogada** la condición de revisión del 2026-07-20 que las preveía al superar ~5 eventos por ciudad. Motivos: (1) contradecía `03-wordpress-content-model` §3, que ya define el escalado del listado **por año** — el volumen crece por fecha, no por ciudad; (2) el umbral era de **cantidad** cuando la decisión es de **contenido**; (3) es navegación facetada, que Google recomienda no rastrear, y la canibalización con `/sanghas/{ciudad}` no mejora con el volumen.
- Se mantiene lo ya aprobado: **`event_city` como taxonomía sin archivo público** —mismo criterio que `event_type`— y los encuentros de cada ciudad **dentro de `/sanghas/{ciudad}`**.
- Propagado a `11-arbol-urls-final` §3, `03-wordpress-content-model` §4 (se añade `event_city` a la tabla de taxonomías), `.audit/decisions.md`, `.audit/state.md`, `.audit/working/url-hypotheses.md` (derogación anotada, análisis original conservado) y la nota de exclusión de **TASK-0021**.
- **Índice de ADR corregido:** el 0021 no se había añadido a la tabla numerada al crearlo. Ahora están el 0021 y el 0022.
- **Los informes SEO no se tocaron.**

### Pendiente

- Relanzar PageSpeed Insights tras desplegar y actualizar §6 del informe técnico.
- **Deriva repo↔producción:** `assets/images/logo.png` es 7.423 b en el repo (grises+alfa) y 10.079 b en producción (RGBA). Ambos 240×240 y válidos, pero no son el mismo archivo.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.19] - 2026-07-21

### Rendimiento — fuente subsetada, CSS minificado e imágenes responsive

- `assets/fonts/marlowe-escapade/marlowe-escapade-subset.woff2`: nuevo. MarloweEscapade subsetada a los 13 caracteres de "Camino del Dharma", los únicos que dibuja (`.site-name`, `.site-title`): **52,1 KB → 3,4 KB**.
- `assets/css/main.css`: `--font-heading` pasa de `"Fjalla One", "MarloweEscapade", serif` a `"Fjalla One", serif`. Con la fuente subsetada, dejarla de fallback mezclaría glifos sueltos con serif sobre texto arbitrario.
- `scripts/build-fonts.sh`: nuevo. Regenera el subset (requiere `pyftsubset`).
- **Nuevo paso de build:** `npm run build:css` (clean-css) genera `assets/css/main.min.css`, que es lo que enlazan las 15 páginas. Servido con Brotli: **9,0 KB → 5,8 KB (−36 %)**. `main.css` sigue siendo el único archivo que se edita; `main.min.css` queda en `.stylelintignore`.
- `index.html`: `srcset` + `sizes` en `inicio-encuentro-comunidad` (768w/1280w) e `inicio-kuan-yin` (768w/945w). En móvil se sirve el 768w; en retina de escritorio, el grande (antes había un solo tamaño para todo).

**Medido (Lighthouse móvil):** rendimiento 93 → 97, LCP 2,9 s → 2,4 s, FCP 2,0 s → 1,8 s, página 879 KB → 333 KB desde el inicio de la serie. `unminified-css` y el árbol de dependencias de red pasan.

**No se tocó:** redirecciones (la URL canónica ya resuelve con 0 saltos; solo quedan http→https y www→no-www, ambos correctos y necesarios) ni la caché (el ahorro que estima Lighthouse es de 1 KiB y subir el TTL exige cache-busting, que hoy no existe).

- `sitemap.xml`: **sin cambio de `<lastmod>`** — cambios de assets y estilos, no de contenido indexable.

### Dependencias y manifiesto

- `npm audit`: 0 vulnerabilidades (antes 1 alta). `fast-uri` 3.1.3 → 3.1.4, dependencia transitiva de stylelint; solo desarrollo, nunca llegó al sitio.
- `clean-css-cli` añadido como `devDependency` (build del CSS).
- `package.json` corregido: `license` decía `ISC` cuando [`LICENSE`](LICENSE) y el README dicen **MIT**; `version` estaba en 1.0.11 y ahora sigue a [`VERSION`](VERSION); `main` apuntaba a un `index.js` inexistente (eliminado); `author` vacío → Rafael Figueredo Oropeza; añadido `private: true` (repo de sitio, no se publica en npm).
- `package-lock.json` sincronizado; `npm ci` reproduce la instalación sin vulnerabilidades.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.18] - 2026-07-21

### CSS — una sola hoja bloqueante

- `assets/css/main.css`: `normalize.css` v8.0.1 incorporado literalmente al inicio (nueva sección 0.0), antes de fuentes y variables. Sin cambios en el orden de cascada.
- `assets/css/normalize.css`: eliminado (su contenido vive ahora en `main.css`; una sola fuente de verdad).
- 15 páginas HTML: retirado el `<link>` a `normalize.css`. En `index.html`, retirado además el `preload` redundante de `main.css` (el `<link rel="stylesheet">` iba inmediatamente después).
- `.stylelintrc.json`: eliminado el bloque `overrides` de `normalize.css` (el archivo ya no existe). Las excepciones se declaran ahora en línea y acotadas dentro de `main.css`; ver `docs/14-css-architecture.md` §8.

**Motivo:** PageSpeed señalaba una cadena crítica `documento → normalize.css`. Medido sobre Brotli en producción: antes 8.407 B + 2.280 B en 2 peticiones; ahora 8.915 B en 1 petición (~1,8 KB menos y un round trip menos). La auditoría de dependencias de red pasa.

- `sitemap.xml`: **sin cambio de `<lastmod>`** — la modificación es de infraestructura de estilos, no de contenido indexable. Ver nota abajo.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.17] - 2026-07-21

### Inicio — galería y logo

- `index.html`: mini-galería con `<picture>` (WebP + JPEG), `loading="lazy"` y `decoding="async"`.
- Nuevas miniaturas en `assets/images/galeria/thumbs/` (jpg y webp).
- `assets/images/logo.png`: optimizado (menor peso).
- `sitemap.xml`: sin cambio — `/` ya tenía `<lastmod>` `2026-07-21` (única página HTML modificada).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.16] - 2026-07-21

### Enlaces internos (práctica)

- `practica/index.html`, `practica/meditacion-semanal-en-linea/index.html` y `practica/videos/index.html`: enlaces relativos sustituidos por rutas absolutas desde la raíz (`/…`) para evitar roturas con la política de URLs canónicas (ADR 0008; hallazgos FUNC-002/003).
- `sitemap.xml`: `<lastmod>` `2026-07-21` en `/practica/videos` (las demás URLs de práctica ya estaban en esa fecha).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.15] - 2026-07-21

### Meditación semanal en línea y eventos

- Nueva página `/practica/meditacion-semanal-en-linea` (horario, modalidad Zoom, enlace de participación).
- `index.html` y `practica/index.html`: enlace visible a la meditación semanal en línea.
- `eventos/index.html`: fichas de eventos pasados (Barranquilla, Calma en la Ciudad, Medellín, UniRemington, Vesak Bogotá) con imágenes nuevas.
- `eventos/encuentro-nacional-2026`: descripción del calendario `.ics` sin duplicar URL del cartel en el texto.
- `llms.txt`: entrada de la meditación semanal; `assets/css/main.css`: estilos de eventos pasados.
- `sitemap.xml`: `<lastmod>` `2026-07-21` en `/`, `/practica`, `/practica/meditacion-semanal-en-linea`, `/eventos` y `/eventos/encuentro-nacional-2026`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.14] - 2026-07-20

### Privacidad (embeds y almacenamiento local)

- `index.html`, `practica/index.html`, `practica/videos/index.html`: iframes de YouTube migrados a `youtube-nocookie.com`; Vimeo con `?dnt=1`; JSON-LD `embedUrl` alineado (hallazgo audit PRIV-001 / TASK-0006).
- `assets/js/main.js`: retirada la persistencia en `localStorage` del selector de idioma mientras el sitio es solo español (ADR 0019).
- `sitemap.xml`: `<lastmod>` actualizado a `2026-07-20` en `/`, `/practica` y `/practica/videos`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.13] - 2026-07-20

### Contenido editorial (grafía Buddhismo)

- `comunidad/index.html`: eliminada la sección «Cómo nos nombramos»; la explicación de grafía queda centralizada en Linaje. El copy usa «Buddhismo» y «buddhista» con naturalidad, sin justificación repetida.
- `linaje/index.html`: reescrita la nota «Sobre la palabra Buddhismo» — término sánscrito *buddha*, *Buddha*/*Buda* como título (no nombre propio), y reconocimiento de *budismo*/*budista* como formas extendidas en español.
- `sitemap.xml`: `<lastmod>` actualizado a `2026-07-20` solo en `/comunidad` y `/linaje` (únicas páginas modificadas; sin URLs nuevas ni retiradas).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.12] - 2026-07-19

### Privacidad

- **Google Analytics 4 desactivado** en las 14 páginas HTML: eliminado el bloque `gtag.js` (`G-B8FY69RGSS`). Motivo: cookies `_ga` sin consentimiento ni política de privacidad (hallazgo audit PRIV-001).
- **Reactivación:** solo tras política publicada y Consent Mode v2 (o alternativa acordada). Ver `.audit/implementation/tasks/TASK-0006.md` y `docs/17-orden-implementacion.md` §2.75 (PRIV-001). ID de propiedad conservado para uso futuro: `G-B8FY69RGSS`.
- Métricas de indexación: seguir usando **Google Search Console** (sin cookies en el sitio).

### SEO (auditoría externa — continuación 2026-07-19)

- `.htaccess`: limpieza del índice residual de la etapa WordPress — `410` para `/prueba`, `301` de `/category/*` → `/blog`, `301` de `/?page_id=10` → `/comunidad` y de otros `?page_id=` → portada (hallazgo SEO-EXT-002; estas URLs seguían indexadas y una página "prueba" aparecía en el SERP de marca).
- `index.html` (JSON-LD Organization): retirado `alternateName` "budismo en Colombia" (keyword, no nombre real); añadidos `foundingDate: 2012`, `foundingLocation: Cali` y `knowsAbout` (Chan, Tierra Pura, meditación, atención plena). Dirección postal no añadida: pendiente de confirmación de la comunidad.
- `index.html` y `comunidad/index.html`: mención textual de la fundación en Cali (2012) — señal local que el sitio no tenía.
- `eventos/index.html`: título "Eventos y Retiros Budistas en Colombia | Camino del Dharma" y descripción con intención temática (og/twitter sincronizados).
- `blog/index.html`: título "Blog de Budismo — Reflexiones y Enseñanzas | Camino del Dharma" (og/twitter sincronizados).
- Evidencia y análisis completo: `.audit/working/seo-external.md`; tareas derivadas TASK-0013–0016.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.11] - 2026-07-19

### Mejoras

- Google Analytics 4 (`G-B8FY69RGSS`): etiqueta `gtag.js` directa en las 14 páginas HTML (sin Google Tag Manager).
- `sitemap.xml`: `<lastmod>` en todas las URLs indexables (`2026-07-19`), al modificarse cada HTML del sitio.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.10] - 2026-07-19

### Mejoras

- Rendimiento (PageSpeed): preload del hero WebP, fuente Inter 400 y CSS principal en inicio.
- Inicio: imágenes con `<picture>` (WebP + JPEG), `fetchpriority="high"` en hero, lazy load bajo el pliegue.
- Imágenes optimizadas en `assets/images/inicio/` (JPEG recomprimidos + variantes `.webp`).
- CSS: `picture` en bloques hero y section-figure.
- `sitemap.xml`: `<lastmod>` actualizado en `/` (`2026-07-19`).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.9] - 2026-07-19

### Mejoras

- Eventos: JSON-LD enriquecido en páginas de detalle (`@id`, organizer, performer, `validFrom`, dirección ampliada).
- Eventos: eliminado microdata (`itemscope`/`itemprop`) del listado y de las fichas; datos estructurados solo en JSON-LD de cada evento.
- `sitemap.xml`: `<lastmod>` actualizado en `/eventos`, `/eventos/encuentro-nacional-2026` y `/eventos/pausa-profunda-cali` (`2026-07-19`), tras los cambios JSON-LD en esas páginas.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.8] - 2026-07-19

### Mejoras

- `<meta name="theme-color">` en todas las páginas; favicon 48×48 añadido.
- SEO: títulos y descripciones refinados en inicio, comunidad, linaje y práctica.
- Inicio: tagline y enlace introductorio a la comunidad.
- Eliminado `site.webmanifest` y referencias PWA (decisión de no implementar app instalable).
- `sitemap.xml`: `<lastmod>` alineado en todas las URLs (`2026-07-19`).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.7] - 2026-07-18

### Mejoras

- `sitemap.xml`: fechas `<lastmod>` actualizadas en eventos y artículo del blog.
- Checklist de despliegue: paso obligatorio de revisar `sitemap.xml` antes de incrementar `VERSION`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.6] - 2026-07-18

### Mejoras

- Metadatos SEO y redes sociales refinados en todas las páginas (título, descripción, Open Graph, Twitter Cards).
- Favicons estandarizados (`assets/favicon/`) y `site.webmanifest`.
- Imagen por defecto para compartir (`assets/images/og-default.jpg`).
- `llms.txt`: ajustes menores de contenido.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.5] - 2026-07-18

### Mejoras

- `llms.txt`: guía curada del sitio para agentes de IA (convención llmstxt.org), con enlaces canónicos a las páginas principales.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.4] - 2026-07-18

### Mejoras

- Resolución de URLs canónicas en `calendar.js` y `share.js` para enlaces de eventos y compartir.
- Rutas absolutas corregidas en páginas de eventos, práctica y blog.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.3] - 2026-07-18

### Mejoras

- Imágenes actualizadas en galería, inicio, comunidad, linaje, eventos, práctica, blog, contacto, celebraciones y logo.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.2] - 2026-07-18

### Mejoras

- `sitemap.xsl`: texto introductorio más breve en la vista del mapa del sitio.

### Correcciones

- Eventos finalizados: eliminados CTAs obsoletos en HTML y estilos asociados en `main.css`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.1] - 2026-07-18

### Mejoras

- `sitemap.xsl`: vista legible del mapa del sitio al abrir `/sitemap.xml` en el navegador.
- `sitemap.xml`: referencia a la hoja de estilo XSL (sin impacto en buscadores).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.0] - 2026-07-18

### Publicación inicial

- Sitio web publicado en producción.
- SEO: meta description, canonical, Open Graph, Twitter Cards, `robots`.
- Metadatos documentales: `author`, `creator`, `publisher`, `developer`, `copyright`, `keywords`.
- `robots.txt` y `sitemap.xml` con URLs canónicas sin barra final.
- JSON-LD: Organization, WebSite, BreadcrumbList, Event, Article, VideoObject.
- Accesibilidad: HTML semántico, skip link, navegación por teclado, contraste.
- Diseño responsive; corrección del selector de idioma en móvil.
- `.htaccess`: HTTPS, URLs sin barra final, 404 personalizada, caché y cabeceras de seguridad.

### Estado

- Desarrollo: Finalizado
- Producción: Publicado

### Servidor

- Hostinger
- Dominio: <https://caminodeldharma.org>

### Observaciones

- Primera versión pública.
- Pendiente o en curso: verificación en Google Search Console y envío del sitemap.
