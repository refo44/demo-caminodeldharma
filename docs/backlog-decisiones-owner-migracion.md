# Backlog — decisiones del propietario (migración static → FSE)

Preguntas de dueño. **No son ADR.**

Hay dos bloques que no se mezclan:

| Bloque | Alcance | Estado |
| ------ | ------- | ------ |
| **Fase 3** (auditoría 2026-08-19, ADR 0034) | Contenido, media, URLs y corte static → FSE | **Cerrado.** No reabrir OWN-001–OWN-017 sin decisión nueva. |
| **Fases posteriores** (`POST-*`) | Trabajo **después** del corte (p. ej. inglés / i18n) | Abiertas. **No bloquean** Fase 3, staging ni el corte. |

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

- `/privacidad` publicada (provisional) — ADR 0039; [0028](adr/0028-privacidad-aplazada-conscientemente.md) sustituida
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
| OWN-006 | 2026-08-28 | Extraer **lo más reciente del repo** (`VERSION` vigente, hoy 1.0.34), no el ZIP más viejo que aún esté en Hostinger. Indicar tag/commit en el payload. Si producción se atrasó, el delta es «desplegar o reconciliar», no extraer el artefacto viejo. |
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

Al cerrar una fila de Fase 3: fecha, decisión en una frase, y actualizar
`inventario-contenido-produccion-static.md`, `conteos-reconciliacion-migracion.md` y, si hay URL,
`redirect-ledger.md`.

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

Ninguna.

## Ya aplazado (no duplicar aquí)

Tienen ADR o decisión de dueño y un disparador propio. No son `POST-*` nuevos:

| Tema | Dónde | Disparador |
| ---- | ----- | ---------- |
| HSTS | ADR 0020 | ≥30 días estables **después** del corte WordPress |
| Copy de `/privacidad` | ADR 0039 | Publicada (provisional). Revisión legal sigue abierta; CF7 gated a actualizar el aviso |
| Automatización de deploy/CD | ADR 0016 | Estructura estable; no crear workflows de deploy ahora |
| CPT `sangha` | ADR 0024 | Fuera del corte inicial; reabrir solo con decisión nueva |
| Paginación numerada de galería | OWN-011 | Solo si un álbum crece mucho **después** del corte |

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

**Versión:** 1.20 · **Fecha:** 2026-08-29 · **Estado:** Fase 3: 0 abiertas · 18 decididas (no
iniciada). Fases posteriores: 7 abiertas (`POST-001`–`POST-007`) · 0 decididas.
