# Auditoría: Gutenberg + metabox clásico — transporte de meta

**Fecha:** 2026-09-01  
**Alcance:** read-only · sin fixes · sin deploy  
**Referencia:** revistalogos issue #30 (UI visible ≠ REST payload ≠ meta persistida)

**Decisión del propietario 2026-09-01 (OWN-019, [ADR 0042](../docs/adr/0042-gutenberg-meta-sin-metabox-clasico-sin-sync.md)):**
estas filas **no** son bugs de producción ni trabajo de pre-staging/corte. Son restricciones
para UI wp-admin futura. Este archivo queda como evidencia y etiqueta de advertencia; el índice
vinculante está en `docs/backlog-decisiones-owner-migracion.md` v1.25.

---

## [BACKLOG] META-001 — Authors metabox sin sync a `core/editor`

**Status:** decided (OWN-019 / ADR 0042) — design constraint, not a cutover defect  
**Severity:** high (latente → blocker al implementar metabox sin sync)  
**Post type(s):** `post`  
**Field(s):** `authors`

### Symptom

Al publicar una entrada nueva en Gutenberg con una ficha de autor seleccionada en el metabox clásico
(ADR 0037), el editor vería:

> Para publicar una entrada asigna al menos una ficha de autor publicada.

…aunque el picker muestre un autor publicado.

### Root cause (hypothesis)

- [x] Classic metabox not synced to `core/editor` (metabox **pendiente**, README plugin)
- [x] REST guard reads wrong source — parcialmente mitigado: el guard **sí** lee `$request['meta']['authors']`, pero si REST no lo trae y el post es nuevo (meta aún vacía), falla
- [ ] Missing CPT `custom-fields` — `post` lo tiene por core
- [ ] Meta not `show_in_rest` — `authors` tiene `show_in_rest` con schema
- [ ] Shared admin JS scope bleed — aún no hay JS admin

### Evidence

- `wordpress/wp-content/plugins/camino-del-dharma-core/README.md` L67–68: metabox pendiente
- `docs/adr/0037-cpt-autor-blog-url-author.md` §6–7: metabox con buscador REST
- `includes/authors-guard.php`: `cdd_core_rest_guard_post_publish()` L98–127; stash L24–36
- `camino-del-dharma-core.php` L85–86: hooks `rest_pre_insert_post`, `wp_insert_post_data`
- **No** `add_meta_box`, **no** `wp_enqueue_script` admin, **no** `editPost` / `core/editor` en el repo

### Reproduction

1. Implementar metabox clásico de autores (sin JS sync) — escenario futuro
2. Crear borrador `post` en Gutenberg
3. Seleccionar ficha publicada solo en el metabox
4. Publicar sin recargar
5. Inspeccionar REST: body sin `meta.authors`
6. Resultado esperado: HTTP 400 `cdd_core_missing_authors`

### Proposed fix (do not implement yet)

1. TDD: test de integración que simule publish REST **sin** `meta.authors` tras selección solo en DOM del metabox (falla antes del fix)
2. Metabox clásico + JS admin: en `change` y en captura Publish/Save, `wp.data.dispatch('core/editor').editPost({ meta: { authors: [...] } })`
3. Deps: `wp-data`, `wp-editor`; scope solo claves `authors` en `post`
4. Mantener `rest_pre_insert_post` + stash (ya correcto para meta en el mismo request)

### Regression tests to add when approved

- REST publish con `meta.authors` en el mismo request (ya existe: `Post_AuthorsRelationTest::test_rest_publish_honors_the_authors_guard`)
- E2E/harness: metabox DOM → publish → REST body incluye `meta.authors`
- REST publish sin meta en post **nuevo** → 400; en post **existente** con meta guardada → 200

### Notes

Patrón idéntico a revistalogos #30. El guard de negocio es correcto; el riesgo es de transporte al
construir la UI pendiente.

---

## [BACKLOG] META-002 — Meta de dominio de eventos sin UI ni sync

**Status:** decided (OWN-019 / ADR 0042) — design constraint, not a cutover defect  
**Severity:** high (latente)  
**Post type(s):** `event`  
**Field(s):** `event_date`, `event_end`, `event_place`, `event_modality`, `event_status`, `event_featured`, `event_signup_url`, `event_signup_payment`, `event_calendar_dates`, `share_*`, `seo_*`, `event_attendance_mode`, `seo_jsonld_extra`, `seo_related_url`

### Symptom

Tras el corte, un editor crea o edita un evento en Gutenberg y rellena fechas/lugar/inscripción en
metaboxes clásicos futuros. El evento publica, pero el front muestra fechas vacías, `.ics` incorrecto,
CTA de inscripción ausente, o JSON-LD incompleto — **sin error visible**.

### Root cause (hypothesis)

- [x] Classic metabox not synced to `core/editor` — no hay UI; cuando exista, riesgo alto
- [ ] Missing CPT `custom-fields` — **presente** en `event` (`post-types.php` L33)
- [ ] Meta not `show_in_rest` — todas registradas con `show_in_rest` (`meta.php`)
- [ ] REST guard reads wrong source — no hay guard de publish en eventos

### Evidence

- `includes/meta.php`: registro completo con `show_in_rest`
- `includes/post-types.php` L33: `'custom-fields'` en supports
- `includes/events.php`: lectura request-time de meta (`event_date`, etc.)
- Sin metaboxes, sin admin JS, sin tests REST de round-trip para event meta

### Reproduction

1. (Futuro) Metabox clásico con `event_date` / `event_calendar_dates` sin sync
2. Crear evento en Gutenberg, rellenar metabox, publicar
3. REST body sin `meta.event_date` / `meta.event_calendar_dates`
4. DB: meta vacía o valores importados obsoletos; `.ics` y calendario incorrectos

### Proposed fix (do not implement yet)

1. Decidir UI: metaboxes nativos (ADR 0025) **o** paneles Gutenberg (`PluginDocumentSettingPanel`) — evitar solo HTML clásico sin sync
2. Si metabox clásico: sync post-type-scoped a `core/editor` para todas las claves `event_*` + share/seo de evento
3. Tests REST: POST/PUT `/wp/v2/event/{id}` con `meta` completo → persistencia y efecto en `cdd_core_event_calendar_payload()`

### Regression tests to add when approved

- REST create/update event con `meta.event_calendar_dates` → `.ics` multi-VEVENT (BUG-001)
- REST update sin enviar meta → meta existente **no** se borra (regresión de merge REST)
- Metabox → publish: body REST incluye campos editados

---

## [BACKLOG] META-003 — Meta SEO/compartir sin UI ni sync

**Status:** decided (OWN-019 / ADR 0042) — design constraint, not a cutover defect  
**Severity:** medium (latente)  
**Post type(s):** `page`, `post`, `event`  
**Field(s):** `seo_title`, `seo_description`, `seo_keywords`, `og_title`, `og_description`, `seo_related_url`; `share_*` en post/event

### Symptom

Editor cambia título SEO o plantilla WhatsApp en metabox clásico; guarda/publica en Gutenberg. El
front sigue mostrando copy importado; el editor cree que guardó.

### Root cause (hypothesis)

- [x] Classic metabox not synced to `core/editor`
- [ ] Missing CPT `custom-fields` — core en page/post; event sí
- [ ] Meta not `show_in_rest` — sí (`cdd_core_register_seo_meta`, `Share_MetaTest`)

### Evidence

- `includes/meta.php` L238–311; `tests/WordPress/Seo_HeadTest.php` L84–92
- `tests/WordPress/Share_MetaTest.php` L32–42
- Sin UI wp-admin para editar SEO/share (solo import/convert)

### Proposed fix (do not implement yet)

1. UI acoplada a Gutenberg (panel document settings) o metabox + sync JS por post type
2. Mapa de claves por CPT (post: seo + share; event: seo + share + jsonld; page: seo)
3. Tests REST por post type

---

## [BACKLOG] META-004 — `blog_author` sin `custom-fields`

**Status:** decided (OWN-019 / ADR 0042) — design constraint, not a cutover defect  
**Severity:** medium (latente)  
**Post type(s):** `blog_author`  
**Field(s):** (futuro) cualquier `register_post_meta`

### Symptom

Meta registrada en fichas de autor no persiste vía REST/Gutenberg (mismo gap 1 de revistalogos).

### Root cause (hypothesis)

- [x] Missing CPT `custom-fields` — `post-types.php` L59: solo `title`, `editor`, `thumbnail`
- [ ] Meta not `show_in_rest` — hoy no hay meta en `blog_author`

### Evidence

- `includes/post-types.php` L37–60 vs `event` L33 con `custom-fields`
- `Blog_AuthorTest` no asserta `custom-fields`

### Proposed fix (do not implement yet)

1. Añadir `'custom-fields'` a supports **antes** de registrar meta en `blog_author`
2. Test: `post_type_supports( 'blog_author', 'custom-fields' )`

---

## [BACKLOG] META-005 — Cobertura de tests REST insuficiente

**Status:** decided (OWN-019 / ADR 0042) — design constraint, not a cutover defect  
**Severity:** medium  
**Post type(s):** `event`, `page`, `post`  
**Field(s):** todas las claves `show_in_rest`

### Symptom

Regresión de transporte meta no detectada en CI hasta wp-admin manual o producción.

### Root cause (hypothesis)

- [x] Other: solo `Post_AuthorsRelationTest::test_rest_publish_honors_the_authors_guard` usa `rest_do_request`; event/seo/share usan `update_post_meta` / `meta_input`

### Evidence

- `tests/WordPress/Post_AuthorsRelationTest.php` L127–154 — único test REST publish con meta
- `tests/WordPress/Event_ModelTest.php` — `meta_input`, no REST
- `tests/WordPress/Seo_HeadTest.php` — request-time front, no REST persistencia

### Proposed fix (do not implement yet)

1. Suite wp-phpunit: REST round-trip por CPT y claves críticas
2. Contrato: publish body debe incluir meta editada en la misma petición cuando la UI sea Gutenberg

---

## Orden si el propietario pide UI wp-admin (después del corte)

No es una cola de Fase 3. Cuando exista esa sesión:

1. **META-001** — criterio de aceptación de la UI de autores (TDD; guard intacto)
2. **META-005** — tests REST del camino Gutenberg de **esa** UI
3. **META-002 + META-003** — misma regla para evento / SEO / compartir, fusionados
4. **META-004** — `custom-fields` en el commit del primer meta de `blog_author`
