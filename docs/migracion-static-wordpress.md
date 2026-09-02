# Migración static/ → WordPress

**Ledger operativo** de diferencias entre la implementación estática y el theme WordPress **durante la Fase 3**.

**No es el contrato de aceptación.** Completitud, Pages vs templates, matriz y QA:
[`contrato-migracion-static-wordpress.md`](contrato-migracion-static-wordpress.md) (ADR 0032).
Import vs fixtures: ADR 0033. Cutover: [`cutover-checklist-wordpress.md`](cutover-checklist-wordpress.md).

**No sustituye** a los ADR ni a `17-orden-implementacion`. Complementa el seguimiento día a día.

**CURRENT STATE:** Fase 3 **iniciada** (2026-08-31, WU-00…WU-07). El sitio desplegable vive en
`static/` (reorg ADR 0014, rama `fase3-wordpress`). Plugin `camino-del-dharma-core` v0.4.0:
modelos de dominio (WU-05), pipeline de migración (WU-06) — payload versionado
`migration/payload.json` (VERSION 1.0.35; paridad live verificada, delta 0), importador
`wp cdd-core migrate validate|plan|import|verify` + `seed` — y conversión field-scoped
`migrate convert` (WU-07/WU-08A). Theme FSE `camino-del-dharma` v0.3.0 con las **vistas
reales** (plantillas, parts, bloques dinámicos, CSS portado, fuentes autohospedadas) y el
**comportamiento portado** (diálogos Compartir y Añadir al calendario, audio de mantras como
bloque nativo). Contenido importado y convertido en el entorno local. Pendiente WU-08B: SEO
dinámico, redirects, OWN-015 y el pase de accesibilidad. Estado durable:
`.audit/fase3-execution-state.md`.
Las filas de abajo registran cambios del estático en producción y deudas hacia el theme futuro.

Decisiones del propietario (**Fase 3 cerrada** v1.28; no reabrir OWN-* sin decisión nueva).
OWN-020 / D-08 está decidido; el código de SEO de fichas de autor sigue **pendiente**
([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)). Pre-staging: D-02/D-03/D-04 en
`main` antes de Hostinger (OWN-035). ADR 0044 (feeds 404), ADR 0045 (entrega CF7).
OWN-021 / D-09: overflow Sangha **dejado** en el corte; wrap WP-only post-corte
([#7](https://github.com/refo44/demo-caminodeldharma/issues/7)).
OWN-022 / D-10: `sessionStorage` de `wp-emoji` **aceptado**.
Fases posteriores (`POST-*`, incl. POST-008–010) no entran en el corte:
[`backlog-decisiones-owner-migracion.md`](backlog-decisiones-owner-migracion.md).

---

## Cuándo actualizar este documento

Registrar cada cambio que afecte una sola implementación o que esté en curso de portarse al theme.

| Tipo de cambio | Static | WordPress |
| ---------------- | ------ | --------- |
| Contenido temporal (p. ej. evento que caduca antes del corte) | Sí | No aplica |
| Corrección editorial permanente | Sí | Sí (cuando se porte) |
| Diseño, CSS, JS, navegación, a11y, SEO, componentes | Sí | **Obligatorio** portar |

---

## Registro de cambios

| Fecha | Cambio | Static | WordPress | Estado |
| ----- | ------ | ------ | --------- | ------ |
| 2026-07-19 | GA4 (`G-B8FY69RGSS`) retirado de las 14 páginas HTML (PRIV-001; sin consentimiento ni `/privacidad`) | Sí | Pendiente portar decisión al theme | Completo (static) |
| 2026-07-21 | Grid de `/galeria` pasa a servir miniaturas (`galeria/thumbs/`, `srcset` 300w/600w) en vez de los originales — PERF-001 | Sí | **No aplica**: la galería pasa a bloque de Gutenberg; `gallery.js` no se migra (ADR 0021) | Completo (static) |
| 2026-07-21 | Visor ampliado (lightbox) de la galería | **No se implementa** | **Nativo del bloque de galería** ("Ampliar al hacer clic", WP 6.4+); sin plugin ni JS propio | Cerrado por ADR 0021 |
| 2026-07-21 | `normalize.css` incorporado a `main.css`; `main.min.css` vía `npm run build:css`; MarloweEscapade subsetada | Sí | **Portado en WU-07**: el theme encola su propio `assets/css/main.css` (porte completo, tokens vía presets; sin paso de minificado propio por ahora) y el subset de MarloweEscapade viaja como `fontFace` de `theme.json` | Completo (repo/local) |
| 2026-07-31 | Formulario de contacto (FUNC-001/TASK-0003): decidido resolverlo con **Contact Form 7** en el theme WordPress | **No** — `contacto/index.html` conserva el `<form action="#">` no funcional junto a los CTAs de WhatsApp/correo, por decisión expresa de no tocar el estático | Pendiente (WordPress), a implementar al iniciar Fase 3 | Pendiente (WordPress) |
| 2026-08-29 | `/privacidad` publicada (aviso provisional; ADR 0039). Pie de todas las páginas. CF7 gated *en esa fecha* | Sí | Page `privacidad` + enlace en `parts/footer.html`; importar HTML live | Completo (static); pendiente WordPress |
| 2026-08-31 | **CF7 en el corte sin espera legal (OWN-018, ADR 0041):** el disclaimer de `/privacidad` basta para lanzar. En WordPress, WU-09 actualiza solo los párrafos del formulario. El HTML estático no cambia mientras el form siga `action="#"`. Revisión legal = trabajo posterior | No (estático intacto) | Sí — delta field-scoped en la Page WP | Pendiente WU-09 |
| 2026-08-31 | **Reorg monorepo (WU-01, ADR 0014):** superficie desplegable movida raíz → `static/` (renames puros, 0 cambios de contenido/URLs). PDF retirado (OWN-002) archivado en `docs/archive/recitacion-practica-comida/`, fuera del ZIP. Tooling actualizado (package.json, stylelint, scripts, README) | Sí (sin despliegue; próximo ZIP se genera desde `static/`) | No aplica (cambio de repo, no de contenido) | Completo (repo) |
| 2026-08-31 | **Línea base de paridad visual del theme (WU-04, ADR 0029, docs/12 §8):** el `theme.json` inicial del theme FSE nace en el commit dedicado `d3b30f5` reproduciendo exactamente los tokens del `:root` de `static/assets/css/main.css` (paridad protegida por `tests/Unit/Theme_TokensTest.php`). Cualquier ajuste posterior de Global Styles se compara contra ese commit | No aplica (el estático no cambia) | Sí — baseline registrada; sin `fontSizes` ni `fontFace` todavía (llegan con las plantillas reales, sin inventar escala) | Completo (repo) |
| 2026-08-31 | **Vistas FSE reales (WU-07, ADR 0029):** 16 plantillas de bloques, parts header/footer vía patterns PHP, 11 bloques dinámicos del theme (calendario con paridad byte a byte contra el grid publicado, listado vigentes/finalizados con tarjeta compacta doc 03 §3, destacado del Inicio, byline ADR 0037, galería nativa por álbum). Sustituciones y deltas de copy registrados en `.audit/fase3-validation-matrix.md` § WU-07 | No aplica (el estático no cambia) | Sí — theme 0.2.0 | Completo (repo/local) |
| 2026-08-31 | **Conversión de contenido importado (WU-07):** `wp cdd-core migrate convert` (dry-run por defecto, `--apply`, idempotente, guard de producción) — inicio (aside destacado y cards del blog → bloques dinámicos; `<picture>`/thumbs hechas a mano → biblioteca), galeria (mount JS → galerías Gutenberg por álbum, ADR 0021/0036, sin paginación OWN-011), comunidad (enlaces a fichas de autor, OWN-016). En staging: `import --apply` → `seed` → `convert --apply` | No aplica | Sí — plugin 0.4.0; aplicado en el entorno local | Completo (local) |
| 2026-08-31 | **BUG-001 (cerrado):** el `.ics` de Círculos incluye **todas las sesiones**. `Cdd_Core_Ics_Generator` emite un VEVENT por fecha de `event_calendar_dates`, con UID propio (`slug-Ymd@host`) y fin exclusivo de día completo; sin cronograma se conserva el rango `event_date`/`event_end` y el UID publicado. Como un enlace profundo lleva una sola entrada, el diálogo nombra la próxima sesión —fecha que el archivo contiene— y una nota dice que el `.ics` trae todas. OWN-012 intacto | No — el estático sigue publicando su VEVENT único de la bienvenida hasta el corte | Sí — plugin 0.7.1, theme 0.5.1 | Completo (repo/local) |

**Estados sugeridos:** `Pendiente`, `En migración`, `Completo`, `No aplica`, `Cerrado`.

---

## Clasificación de cambios

### Solo static (permitido)

- Eventos u ofertas temporales vigentes solo hasta la fecha de lanzamiento de WordPress.
- Hotfixes urgentes de producción mientras el theme aún no refleja el fix (debe registrarse aquí y planificarse porte).

### Ambas implementaciones (obligatorio)

- Estructura de bloques, navegación, URLs.
- CSS, JavaScript, tokens, identidad visual.
- Accesibilidad, SEO técnico, comportamiento de componentes.
- Copy permanente alineado con producción publicada (OWN-007, ADR 0040).

---

## Antes del corte final

1. Revisar que no queden filas en `En migración` o `Pendiente` (salvo `No aplica`).
2. [Matriz](matriz-migracion-static-wordpress.md) con estrategia en las cinco columnas de entregable.
3. Importación de contenido (ADR 0033); Pages institucionales reales en BD.
4. Validación en staging (Fase 2.5 sobre theme). Theme activado ≠ corte completo.
5. Backup estático + backup WordPress (BD + uploads).
6. Corte según [`cutover-checklist-wordpress.md`](cutover-checklist-wordpress.md). Retirar el ZIP estático del document root WP.

---

## Referencias

- `docs/17-orden-implementacion` § Transición estático → WordPress
- `docs/contrato-migracion-static-wordpress.md` (ADR 0032)
- ADR 0012, ADR 0014, ADR 0015, ADR 0016, ADR 0029, ADR 0033
- `docs/12-theme-file-structure`

---

**Versión:** 1.2 · **Fecha:** 2026-08-31
