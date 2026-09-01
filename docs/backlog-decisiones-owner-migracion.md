# Backlog — decisiones del propietario (migración static → FSE)

Preguntas de dueño. **No son ADR.**

Hay cinco bloques que no se mezclan:

| Bloque | Alcance | Estado |
| ------ | ------- | ------ |
| **Fase 3** (auditoría 2026-08-19, ADR 0034) | Contenido, media, URLs y corte static → FSE | **Cerrado.** No reabrir OWN-001–OWN-019 sin decisión nueva. |
| **Pre-staging** (`D-08` / OWN-020) | SEO/AEO de fichas `/author/{slug}` | **Decidido 2026-09-01.** Implementación **pendiente** ([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)). No reabrir noindex en singles (ADR 0037). |
| **Fases posteriores** (`POST-*`) | Trabajo **después** del corte (p. ej. inglés / i18n; wrap D-09) | i18n abiertas. **POST-008 decidido**, no implementar hasta WordPress en el dominio canónico ([#7](https://github.com/refo44/demo-caminodeldharma/issues/7)). |
| **Defectos conocidos** (`BUG-*`) | Fallos con sesión propia en el orden de implementación | **BUG-001 cerrado** (2026-08-31, antes de WU-10). Sin defectos abiertos. |
| **Riesgos meta transport** (`META-*`) | Gutenberg + metabox clásico (auditoría 2026-09-01) | **Decididos 2026-09-01** (OWN-019 / ADR 0042): restricciones de diseño para UI futura, **no** defectos de corte. |

Hasta que el propietario cierre una fila `POST-*`, vale el **default** de «Mientras tanto». No
implementar esas filas en el corte. Si una respuesta cambia URLs o arquitectura, **entonces** se
escribe un ADR.

---

## ADR vs backlog (este proyecto)

| | Backlog (este archivo) | ADR (`docs/adr/`) |
| --- | --- | --- |
| Qué es | Pregunta abierta o ítem UNCLEAR | Decisión ya tomada (o **Propuesta** si está en discusión arquitectónica activa) |
| Cuándo | Contenido puntual, media, timing, copy | Arquitectura, URLs públicas nuevas, modelo CMS, seguridad, corte |
| Estado | `Abierta` → `Decidida` (anotar aquí + actualizar inventario/conteos/ledger) | Aceptada / Rechazada / … |

**No crear un ADR por cada UNCLEAR.** Si más adelante una respuesta cambia arquitectura o URLs
(p. ej. publicar singles nuevos para los 7 eventos solo-listado), **entonces** se escribe un ADR.

El backlog de la auditoría 2026-07-19 (`.audit/implementation/backlog.md`, TASK-NNNN) es **otro**
listado. No mezclar.

Ya resuelto por ADR (no repetir aquí como pregunta):

- `/privacidad` publicada (provisional) — ADR 0039; [0028](adr/0028-privacidad-aplazada-conscientemente.md) sustituida. CF7 en el corte: ADR 0041 / OWN-018
- CPT `sangha` fuera del corte inicial — ADR 0024
- Freeze por defecto: ledger durante el build + freeze corto al corte — ADR 0034
- Todo evento tiene single; pasados sin inscripción ni «Añadir al calendario» — ADR 0035 (OWN-004, OWN-012)
- `.ics` generado al vuelo (no Media Library); mp3 sí — OWN-009
- Fecha de fin vencida → finalizado automático; `.ics` huérfano se elimina — OWN-013
- wp-admin: acción manual «Eliminar huérfanos» (solo `.ics`) — OWN-015
- `/eventos/ical/{slug}.ics` no se indexa (SEO/AEO) — OWN-014
- Autores del blog: CPT + `/author/{slug}`; usuario WP no firma — ADR 0037 (OWN-010)
- En WP, `/comunidad` enlaza a esas fichas; el estático no se toca — OWN-016
- Álbumes: misma agrupación que prod; URLs `/galeria/{slug}` noindex hasta volumen — ADR 0036 (OWN-008)
- Galería WP: taxonomía (no Page hija ni CPT); corte **sin** paginación numerada — OWN-011

---

## Fase 3 — Abiertas

Ninguna.

## Fase 3 — Decididas

| ID | Fecha | Decisión |
| -- | ----- | -------- |
| OWN-001 | 2026-08-28 | **Foto de página ≠ ítem de galería.** Una imagen usada como ilustración en otra página no entra en `/galeria`. `galeria-04.jpg` se importa como media de `/practica`, no al álbum. Conteos: 35 galería + 1 media de página. El preview del inicio (`galeria-01`–`03`) es teaser de la galería, no ilustración de otra sección: esas tres **sí** siguen en `#gallery-data`. |
| OWN-006 | 2026-08-28 | Extraer **lo más reciente del repo** (`VERSION` vigente, hoy 1.0.35), no el ZIP más viejo que aún esté en Hostinger. Indicar tag/commit en el payload. Si producción se atrasó, el delta es «desplegar o reconciliar», no extraer el artefacto viejo. |
| OWN-009-img | 2026-08-28 | **Imágenes → Media Library vía seed.** Todas las imágenes referenciadas (galería, carteles, páginas, hero, fundador, etc.) se suben como attachments. El seed es **contenido real** (ADR 0033): idempotente, create-missing-only, sin `_cdd_fixture`, **sin teardown**. Thumbs estáticos: WordPress regenera tamaños. No hardcodear fotos en `templates/` ni en el theme. |
| OWN-002 | 2026-08-28 | **RETIRE.** El PDF de recitación de la comida queda **excluido de la web**. No forma parte de los archivos del sitio. No enlazar, no importar, no seed, no URL. Docs 04/16/23 que lo mencionan son HISTORICAL. |
| OWN-003 | 2026-08-28 | **Huérfanas → seed oculto.** Toda imagen del repo no enlazada en HTML (p. ej. `celebracion-vesak-2019` y el resto no usado de `celebraciones/`) se sube a Media Library con el seed, **sin** mostrarse en el sitio: no álbum, no Page, no teaser. Quedan en la biblioteca por si se usan después. `celebracion-diwali.jpg` **sí** está en `/practica` → media de página (OWN-001), no huérfana. El PDF no es imagen (OWN-002). |
| OWN-004 | 2026-08-28 | **Todo evento tiene single.** Los 10 CPT tienen `/eventos/{slug}`. Los 7 que hoy no tienen ficha se publican en el corte (slugs en ADR 0035 / ledger). Eventos **pasados: sin Inscribirme / Preinscribirme**. Vigentes: inscripción solo si es real. |
| OWN-005 | 2026-08-29 | **C+A confirmado.** Producción = estático en `caminodeldharma.org`. WordPress se despliega en **otra instancia Hostinger, sin dominio custom**, hasta el switch. Esa instancia es **STAGING**: noindex; no pisa `public_html` del estático. Ledger durante el build; freeze corto al corte. Delta (B) solo si el freeze no es viable. |
| OWN-007 | 2026-08-29 | **Gana el HTML live.** El copy y el contenido de producción en `caminodeldharma.org` son los correctos. **QA obligatorio:** revisar y comparar copy, contenido **y estilos** contra la versión **publicada**, no solo contra el repo local. Si repo y Hostinger no coinciden, anotar el delta (OWN-006 + ledger); no asumir paridad. |
| OWN-017 | 2026-08-29 | **Retiro definitivo de la fuente legacy.** El propietario ordena eliminar permanentemente `content-source/` **sin copia de respaldo** porque está desactualizado y genera ambigüedad. No participa en migración ni QA. Producción publicada gobierna antes del corte; WordPress, después (ADR 0040). |
| OWN-008 | 2026-08-29 | **Agrupar como en producción** en `/galeria` (General, 2023, 2021). **Además** se permiten URLs `/galeria/{slug}` (taxonomía, no CPT). Hub `/galeria` = SEO principal (KEEP). Términos **`noindex, follow` hasta volumen** (ADR 0036), igual espíritu que tags (ADR 0031). Hoy 2023/2021 tienen 5 fotos: no indexar en el corte. |
| OWN-011 | 2026-08-29 | **Taxonomía confirmada; sin paginación numerada en el corte.** El álbum es término `gallery_album`, no Page hija ni CPT. En WordPress el bloque Galería muestra **todas** las fotos del álbum (lazy-load nativo). No se porta `?galeria-*-page=` ni `/galeria/{slug}/page/2`. La paginación de 12 de la maqueta (`gallery.js`, `06` §6) es HISTORICAL para el destino FSE. Revisar solo si un álbum crece mucho; entonces, si acaso, solo en la URL del álbum. |
| OWN-009 | 2026-08-29 | **mp3 → Media Library. `.ics` no.** Los 2 mantras se suben como attachments (bloque Audio). El `.ics` lo **genera** `camino-del-dharma-core` al vuelo desde los campos del evento (título, fechas, lugar, URL). URL estable `/eventos/ical/{slug}.ics` mientras esté vigente (Círculos KEEP). No se guarda en la biblioteca: no se desactualiza ni cambia de ruta. Vista previa en wp-admin = descargar el generado, no reescribir un archivo. |
| OWN-012 | 2026-08-29 | **Pasados: sin «Añadir al calendario» y sin `.ics`.** Misma lógica que ADR 0035 (sin inscripción). No seed, no URL, no botón (oculto, no deshabilitado a la vista). `encuentro-nacional-2026.ics` → **RETIRE** (410). Círculos sigue vigente: su `.ics` y el botón se mantienen hasta que pase a finalizado; entonces la misma regla. El estático aún tiene el archivo huérfano; no se toca HTML/ICS live en esta nota. |
| OWN-013 | 2026-08-29 | **Al día siguiente de la fecha de fin, el evento pasa solo a finalizado.** Comparar en `America/Bogota`: `hoy > event_end` (si no hay fin, `event_date`). Ese día el evento **sigue** vigente. `cancelado` no lo pisa la fecha. El visitante ve el archivo **al pedir la página** (no espera un cron). Al pasar: sin inscripción, sin calendario, `/eventos/ical/{slug}.ics` → **410**, y se **borra** cualquier `.ics` guardado (attachment o archivo huérfano). Si se alarga la fecha de fin, vuelve a vigente y el `.ics` generado reaparece. La meditación semanal no es `event`. |
| OWN-014 | 2026-08-29 | **El `.ics` no se indexa.** `/eventos/ical/{slug}.ics` es descarga para calendarios, no documento. Fuera del sitemap (hoy no está; en WP el plugin no lo añade a `/wp-sitemap.xml`). Cabecera `X-Robots-Tag: noindex, nofollow`. No entra en `llms.txt`. SEO y AEO van a la ficha `/eventos/{slug}` (JSON-LD `Event`, doc 15 §12.3). `rel="alternate" type="text/calendar"` en la ficha vigente sí: lo usan los calendarios, no Google. |
| OWN-015 | 2026-08-29 | **wp-admin tiene «Eliminar huérfanos»** (forzar a mano). Vive en `camino-del-dharma-core` (herramientas del plugin, no un plugin de terceros). Alcance: **solo `.ics`** — attachments `text/calendar` y archivos sueltos que no correspondan a un evento **vigente**. Primero lista (dry-run); luego aplica con nonce. Quien puede: editar eventos. No borra fotos huérfanas (OWN-003), mp3 ni carteles. El pase automático (OWN-013) sigue valiendo; este botón es por si quedó un resto o se quiere limpiar ya. **Aceptado 2026-08-29:** no hay otro tipo de archivo que valga la pena borrar. PDF de recitación no se importa (OWN-002). mp3 son los mantras. Thumbs no se importan. Embeds no son archivos. La papelera y las revisiones de WP no entran en este botón. |
| OWN-010 | 2026-08-29 | **D — CPT de autor.** El «Por…» del blog sale de fichas `blog_author`, no de copy ni del usuario WP. Perfil `/author/{slug}` (ADR 0037). Semilla: Zheng Gong (`zheng-gong`), Comunidad Camino del Dharma (`comunidad-camino-del-dharma`). Buscador al asignar; publicar exige ≥1 ficha. `query_var` ≠ `author`. Archivos de users = 404. Eventos no usan este CPT. `/comunidad` no se mueve. |
| OWN-016 | 2026-08-29 | **Cuando exista WordPress, `/comunidad` enlaza a las fichas de autor.** Fundador → `/author/zheng-gong`. La comunidad (quiénes somos / nombre institucional) → `/author/comunidad-camino-del-dharma` (o el slug que quede). Son enlaces, no se sustituye la Page ni se mueve la bio. **No modificar el HTML estático** ahora; se hace en la Page de WP (o al importar). OWN-007 sigue: el copy live no se pisa; solo se **añaden** esos enlaces. |
| OWN-018 | 2026-08-31 | **CF7 entra al corte sin esperar asesoría legal.** El disclaimer publicado en `/privacidad` basta para lanzar. La revisión jurídica queda recomendada para más adelante, no este año, y **no** bloquea WU-09 ni el corte. En WordPress se actualizan solo los párrafos del formulario (ADR 0041). El HTML estático no se toca mientras el form de producción siga siendo `action="#"`. |
| OWN-019 | 2026-09-01 | **`META-001`–`META-005` no son bugs de corte.** Restricciones para UI wp-admin futura (ADR 0042): sin metabox clásico en staging; autores/evento/SEO cuando se construyan = panel nativo o clásico **con** sync REST demostrado; `custom-fields` en `blog_author` solo al registrar meta. No relajar el guard de autores. |
| OWN-020 | 2026-09-01 | **D-08 cerrado.** Las fichas `/author/{slug}` siguen **indexables** (ADR 0037). No hay meta en el estático porque esas URLs no existían: reutilizar **copy corto y fotos ya publicados**, no inventar, no duplicar el ensayo largo del fundador (queda en `/comunidad`, KEEP). Zheng Gong: description JSON-LD + `assets/images/fundador/foto-biografia-fundador.jpg`. Comunidad: primer párrafo de «Quiénes somos» o la meta de `/comunidad` + `assets/images/comunidad-linaje/comunidad-quienes-somos.jpg`. En ambas, enlace a `/comunidad` para el texto largo. Código **pendiente** ([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)). |
| OWN-021 | 2026-09-01 | **D-09 cerrado: dejar el desbordamiento en el corte.** `/blog/sangha-refugio-hiperconexion` a 320 px mide 339 px; **producción publicada desborda igual**. No se toca `static/`. No se «arregla» solo en WordPress antes del corte (inventaría un delta frente al live). Wrap **después** del corte, WordPress ya en `caminodeldharma.org`: POST-008 / [#7](https://github.com/refo44/demo-caminodeldharma/issues/7). |

Al cerrar una fila de Fase 3: fecha, decisión en una frase, y actualizar
`inventario-contenido-produccion-static.md`, `conteos-reconciliacion-migracion.md` y, si hay URL,
`redirect-ledger.md`.

---

## Pre-staging — implementación pendiente

Pregunta **cerrada**. El código no está. No tratar D-08 como copy sin dueño ni como `noindex`.

| ID | Decisión | Issue | Estado |
| -- | -------- | ----- | ------ |
| D-08 | OWN-020: fichas de autor = páginas de entidad; copy corto + foto publicados; singles `index,follow` | [#5](https://github.com/refo44/demo-caminodeldharma/issues/5) | Pendiente de implementar (TDD, ADR 0038). No dummy / `_cdd_fixture`. |

Copy y fotos a reutilizar (producción publicada, OWN-007):

| Ficha | `seo.description` / bio corta | Foto |
| ----- | ----------------------------- | ---- |
| `zheng-gong` | JSON-LD publicado: «Maestro buddhista contemporáneo, fundador de la Comunidad Buddhista Camino del Dharma. Su enseñanza integra la sabiduría del Buddhismo Chan y Tierra Pura con los desafíos de la vida moderna.» On-page: esa frase o el byline corto de Sangha, más enlace a `/comunidad`. | `static/assets/images/fundador/foto-biografia-fundador.jpg` |
| `comunidad-camino-del-dharma` | Meta de `/comunidad`: «Conoce Camino del Dharma, una comunidad budista en Colombia dedicada a la práctica del budismo Chan y Tierra Pura.» On-page: primer párrafo de «Quiénes somos» o esa meta, posts relacionados, enlace a `/comunidad`. | `static/assets/images/comunidad-linaje/comunidad-quienes-somos.jpg` |

---

## Fases posteriores — Abiertas

Estas filas salen de lo ya escrito (sitio monolingüe, selector solo UI, cadenas translation-ready)
y **no** forman parte del corte. El inglés no se implementa «por si acaso».

| ID | Tema | Pregunta | Mientras tanto | Disparador |
| -- | ---- | -------- | -------------- | ---------- |
| POST-001 | Publicar inglés | ¿Se publica una versión en inglés? ¿Cuándo? ¿Quién da el OK editorial? | Sitio **solo en español**. El botón EN del header permanece deshabilitado («Próximamente en inglés»). No hay páginas `/en`, ni `.po` de contenido, ni `hreflang`. | Decisión comunitaria de tener contenido inglés listo para publicar. |
| POST-002 | Mecanismo i18n | ¿Cómo se implementa el segundo idioma en WordPress: solo gettext del theme/plugin, WordPress core, Polylang, WPML u otro? El `main.js` del switcher **no** migra (decisión 2026-07-20). | Cadenas de PHP de cara al usuario envueltas en `__()` / `_e()` con text domain propio (ADR 0027). **Sin** plugin multilingual. **Sin** switcher activo. | POST-001 = sí, y hay copy inglés (POST-005). Entonces ADR. |
| POST-003 | URLs al activar idiomas | `docs/11` ya reserva `/es/` como prefijo del español **si** hay multilingualismo. ¿Se confirma? ¿El inglés es `/en/…`? ¿Las URLs actuales sin prefijo hacen 301 a `/es/…`? | URLs **sin** prefijo de idioma (`/` , `/comunidad`, …). KEEP en el ledger. No crear `/es` ni `/en` en el corte. | POST-002 decidido. Cualquier prefijo nuevo exige ADR + filas KEEP/301 en `redirect-ledger.md`. |
| POST-004 | Selector ES \| EN | Con inglés real: ¿el control del header lo pone el plugin/core, o se rediseña? ¿Se oculta hasta entonces o se mantiene el EN disabled como hoy? | Paridad del estático live (OWN-007): el control **visual** ES activo / EN disabled es STRUCTURAL COPY. No hay i18n real. La persistencia `localStorage` ya se retiró. No sobrescribir `html lang` desde el cliente. | POST-002. No activar EN hasta POST-001. |
| POST-005 | Alcance de la traducción | ¿Qué se traduce: UI, páginas fijas, blog, eventos, galería, `.ics`, autores? ¿Todo a la vez o por oleadas? Términos budistas según `21` §7; un texto no mezcla idiomas (`23` §10). | Copy canónico **español** (OWN-007). `23` §10 aplica solo cuando haya idiomas activos. No encargar ni importar traducciones en Fase 3. | POST-001. Cierre editorial de la comunidad. |
| POST-006 | SEO multilingual | ¿`hreflang`? ¿sitemaps por idioma? ¿`og:locale` por versión (`es_CO` vs `en_*`)? | `hreflang` **no aplica** (informe SEO: un solo idioma). `og:locale` del estático (mayoría `es_CO`). Sitemap único en español. | POST-003. Entra en el ADR de i18n, no en el corte. |
| POST-007 | Quién traduce y paridad | ¿Quién produce y mantiene el inglés (comunidad, profesional, mixto)? ¿Cómo se evita que una lengua se desactualice? | No hay flujo de traducción. El español es la fuente editorial hasta POST-001. | POST-001 + POST-005. |

## Fases posteriores — Decididas

| ID | Fecha | Decisión | Issue |
| -- | ----- | -------- | ----- |
| POST-008 | 2026-09-01 | **Después del corte**, con WordPress ya sirviendo producción en **`https://caminodeldharma.org`**, envolver/partir la URL larga de `/blog/sangha-refugio-hiperconexion` **solo en el theme WordPress** (320 px sin scroll horizontal). Hasta entonces: paridad con el estático live (OWN-021 / D-09). No editar `static/` para esto. | [#7](https://github.com/refo44/demo-caminodeldharma/issues/7) |

## Defectos conocidos — Abiertos

Ninguno.

## Riesgos meta transport (Gutenberg + metabox clásico) — Decididos

Auditoría read-only 2026-09-01 (patrón revistalogos #30). **No eran bugs de producción:** no hay
`add_meta_box` ni JS admin; el contenido llega por migración/CLI. **Decisión del propietario
2026-09-01 (OWN-019, ADR 0042):** restricciones de diseño, no cola de Fase 3. Detalle histórico:
`.audit/gutenberg-meta-transport-audit-2026-09-01.md`.

| ID | Qué es ahora | Disparador (no el corte) |
| -- | ------------ | ------------------------ |
| META-001 | Criterio de aceptación de la **sesión de UI de autores** (ADR 0037) | Panel nativo **o** metabox clásico + sync `core/editor` demostrado (TDD). El guard REST se mantiene. |
| META-002 + META-003 | Misma regla para UI de evento / SEO / compartir | Fusionar en esa unidad; no inventar metaboxes en staging |
| META-004 | Una línea + test | El mismo commit que el primer `register_post_meta` de `blog_author` |
| META-005 | Tests REST de persistencia | Esa misma unidad de UI, no una suite preventiva ahora |

## Defectos conocidos — Cerrados

| ID | Fecha | Defecto | Arreglo aplicado |
| -- | ----- | ------- | ---------------- |
| BUG-001 | Abierto 2026-08-31 · **Cerrado 2026-08-31** | El `.ics` exportado de Círculos no incluía todas las sesiones. El estático publicado emite un solo VEVENT de la **bienvenida** (3–4 sep); WordPress (WU-06/08A) emitía un solo VEVENT del **rango** 3 sep → 25 oct. Ninguno listaba las fechas de `event_calendar_dates`. | Sesión propia justo antes de WU-10, TDD. `Cdd_Core_Ics_Generator` emite **un VEVENT por sesión** de `event_calendar_dates`, cada uno con UID propio (`slug-Ymd@host`) y su fin exclusivo de día completo, dentro del sobre VCALENDAR de producción; sin lista de sesiones se mantiene el rango `event_date`/`event_end` con el UID publicado. `cdd_core_event_calendar_payload()` resuelve el cronograma una sola vez: el archivo y el diálogo no pueden divergir, y como un enlace profundo lleva una sola entrada, Google/Outlook añaden **la próxima sesión** (fecha que el archivo contiene) con una nota que dice que el `.ics` trae todas. Ni el estático ni OWN-012 cambian. Plugin 0.7.1, theme 0.5.1. |

## Ya aplazado (no duplicar aquí)

Tienen ADR o decisión de dueño y un disparador propio. No son `POST-*` nuevos:

| Tema | Dónde | Disparador |
| ---- | ----- | ---------- |
| HSTS | ADR 0020 | ≥30 días estables **después** del corte WordPress |
| Copy de `/privacidad` | ADR 0039 + [0041](adr/0041-cf7-corte-sin-asesoria-legal.md) | Publicada (provisional). Disclaimer basta para el corte (OWN-018). Revisión legal = trabajo **posterior**, no gate. En WP: solo párrafos del formulario |
| Automatización de deploy/CD | ADR 0016 | Estructura estable; no crear workflows de deploy ahora |
| CPT `sangha` | ADR 0024 | Fuera del corte inicial; reabrir solo con decisión nueva |
| Paginación numerada de galería | OWN-011 | Solo si un álbum crece mucho **después** del corte |
| UI wp-admin de meta (autores, evento, SEO) | ADR 0037 + [0042](adr/0042-gutenberg-meta-sin-metabox-clasico-sin-sync.md) | Sesión propia **después** del corte (o cuando el dueño pida editar meta en Gutenberg). Native-first; sin metabox clásico sin sync |

Al cerrar una fila `POST-*`: fecha, decisión en una frase, y —si cambia URLs o el motor i18n—
ADR + `redirect-ledger.md` + matriz.

---

**Reconciliación docs (2026-08-29):** bloque Fase 3 **cerrado** (v1.18). Alineados AGENTS/CLAUDE,
contrato, playbook, FABLE5 v2, docs 03/04/12/15/17, inventario, matriz, ledger, ADR 0035–0037.
FABLE5 v1 sigue HISTORICAL. Fase 3 no iniciada.

**v1.19 (2026-08-29):** se añaden `POST-001`–`POST-007` (i18n / inglés) como backlog de **fases
posteriores**. No reabren Fase 3. No se implementan en el corte.

**v1.20 (2026-08-29):** OWN-017 retira permanentemente `content-source/` sin respaldo; ADR 0040
consolida producción publicada como fuente pre-corte.

**Higiene 2026-08-30:** OWN-006 actualiza el ejemplo de `VERSION` a 1.0.35; no reabre decisiones.

**v1.21 (2026-08-31):** OWN-018 / ADR 0041 — Contact Form 7 entra al corte sin esperar
asesoría legal. El disclaimer de `/privacidad` basta para lanzar. En WordPress se actualizan
solo los párrafos del formulario.

**v1.22 (2026-08-31):** BUG-001 — el `.ics` de Círculos debe incluir todas las sesiones; ni el
estático (solo bienvenida) ni el WP actual (un VEVENT de rango) lo hacen. **Sesión propia
inmediatamente antes de WU-10** (después de WU-09). No se mezcla con WU-08B.

**v1.23 (2026-08-31):** BUG-001 **cerrado** en su sesión propia, antes de WU-10. Un VEVENT por
sesión con UID propio; el diálogo enlaza la próxima sesión y lo dice. Estático intacto, OWN-012
intacto. Quedan 0 defectos abiertos.

**v1.24 (2026-09-01):** Auditoría Gutenberg + metabox clásico — 5 riesgos latentes `META-001`–`META-005`
(pending owner decision). Sin UI wp-admin de meta hoy; el guard REST de `authors` ya mitiga parte del
patrón revistalogos #30.

**v1.25 (2026-09-01):** OWN-019 / ADR 0042 — el propietario **no** acepta META-* como defectos de
corte. Son restricciones para UI wp-admin futura. Staging no construye metaboxes clásicos.

**v1.26 (2026-09-01):** OWN-020 / D-08 **cerrado**. Fichas `/author/{slug}` indexables con copy
corto y fotos ya publicados; no noindex; implementación pendiente
([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)).

**v1.27 (2026-09-01):** OWN-021 / D-09 **cerrado** (dejar overflow en el corte). POST-008:
wrap WordPress-only **después** de que WP sea producción en el dominio canónico
([#7](https://github.com/refo44/demo-caminodeldharma/issues/7)).

**Versión:** 1.27 · **Fecha:** 2026-09-01 · **Estado:** Fase 3: 0 abiertas · 22 decididas.
Pre-staging: 1 decidida, implementación pendiente (`D-08` / OWN-020).
Fases posteriores: 7 abiertas (`POST-001`–`POST-007`) · 1 decidida (`POST-008`).
Defectos conocidos: 0 abiertos · 1 cerrado (`BUG-001`).
Riesgos meta transport: 0 abiertos · 5 decididos como restricciones (`META-001`–`META-005`).
