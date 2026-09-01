# Contrato de migración static → WordPress

Contrato de aceptación de la futura migración del sitio de la Comunidad Buddhista Camino del Dharma.

**Estado de este documento:** vigente. **No implementa** WordPress, importadores, workflows ni
despliegue.

**Decisión:** [ADR 0032](adr/0032-contrato-migracion-static-wordpress.md),
[ADR 0033](adr/0033-importador-contenido-vs-fixtures.md),
[ADR 0034](adr/0034-static-live-como-fuente-contenido-produccion.md) y
[ADR 0040](adr/0040-retirar-content-source-produccion-como-fuente.md).

**No sustituye:** ADR, `17-orden-implementacion`, `11-arbol-urls-final`, `12-theme-file-structure`.
**Complementa:** `migracion-static-wordpress.md` (ledger de diferencias) y
`playbook-migracion-static-wordpress.md` (QA y arranque de Fase 3).

---

## 0. Tres tiempos (no mezclar)

| Tiempo | Qué es cierto |
| ------ | ------------- |
| **HISTORICAL STATE** | Hubo restos de un WordPress anterior en este dominio (redirects de `/category`, `?page_id=`, `/prueba` en `.htaccess`). Algunos docs numerados describen plantillas PHP clásicas (`front-page.php`, `page-*.php`) porque se escribieron antes de ADR 0029. |
| **CURRENT STATE** | Producción = sitio **estático live** en Hostinger, `https://caminodeldharma.org` (visitas reales). HTML en la **raíz**. Eventos/blog/galería hardcodeados = contenido de producción (ADR 0034), no demo. La fuente legacy fue eliminada (ADR 0040). `wordpress/` tiene árboles placeholder (README, sin código) para Sonar (ADR 0038). No hay `docker-compose.yml` ni `.github/workflows`. `VERSION`. ZIP manual (ADR 0015). |
| **FUTURE PLAN** | Fase 3: reorg a `static/` + `wordpress/` (ADR 0014). Ruta **única:** maqueta estática → **FSE** (ADR 0029), sin theme clásico PHP. Plugin `camino-del-dharma-core` (ADR 0024). Staging separado; corte según [cutover-checklist-wordpress.md](cutover-checklist-wordpress.md). |

Afirmaciones como «WordPress not started» y «deploy HTML to production» describen el **estado
actual**. Dejan de aplicar **después** del corte; no se reescriben como si nunca hubieran sido
ciertas.

---

## 1. La migración no está completa porque el theme esté desplegado

Activar `camino-del-dharma` o copiar archivos a `wp-content/themes/` no crea Pages, no registra CPTs
(eso es el plugin, ADR 0024), no importa media, no configura permalinks públicos y no prueba JS.

```text
A migration is NOT complete merely because the WordPress theme is deployed or activated.
```

---

## 2. Cinco entregables independientes

| # | Entregable | Definición |
| - | ---------- | ---------- |
| 1 | **CONTENT** | Objetos en la base de datos y media: Pages, posts, CPT `event`, CPT `blog_author` (ADR 0037; `seo`/bio/thumb de ficha OWN-020 **pendiente** [#5](https://github.com/refo44/demo-caminodeldharma/issues/5)), taxonomía de álbum (ADR 0036), `sangha` solo si una decisión posterior lo activa, metadata, biblioteca de medios, copy institucional. |
| 2 | **PRESENTATION** | Theme de bloques: `templates/*.html`, `parts/*.html`, `patterns/`, `theme.json`, CSS complementario, layout responsive, estructura de accesibilidad. |
| 3 | **ROUTING** | Slugs, permalinks, archives/singles, taxonomías con archivo público, redirects, 404, canonicales. Las URLs públicas deben coincidir con ADR 0008 y `11-arbol-urls-final`. |
| 4 | **BEHAVIOR** | JavaScript, formulario, menú móvil, diálogos (compartir / añadir al calendario en **vigentes**), tooltips del calendario de `/eventos`, galería Gutenberg **sin** paginación numerada (ADR 0021, OWN-011), audio de mantras, `.ics` **generado** (OWN-009). |
| 5 | **OPERATIONS** | Environments, backups, rollback, importadores, fixtures, indexación, QA con evidencia, ownership, retiro del deploy estático legacy. |

Un entregable en verde no cierra los otros cuatro.

---

## 3. CONTENT vs PRESENTATION

El contenido editorial debe **sobrevivir** a un cambio de theme.

| | CONTENT | PRESENTATION |
| --- | ------- | ------------ |
| Vive en | BD WordPress + `wp-content/uploads/` (tras el corte) | Theme (Git) |
| Fuente de verdad actual (pre-corte) | HTML/JSON y media de producción publicada | HTML/CSS/JS publicado |
| Fuente de verdad futura (post-corte) | WordPress (ADR 0013) | Git / theme de bloques |
| Puede cambiar el theme sin reescribir | Sí, si el copy no está hardcodeado en plantillas | N/A |

Precedencia pre-corte (ADR 0033 + ADR 0034 + ADR 0040):

| Tipo | SOURCE OF TRUTH (hoy) | Tras el corte |
| ---- | --------------------- | ------------- |
| Copy institucional | **HTML live** (OWN-007) | WP |
| Eventos, posts, JSON galería, fechas, cards | **HTML/JSON live** (única representación completa) | WP |
| `main.min.css` | generado desde `main.css` | theme |
| Presentación | HTML/CSS/JS de la maqueta (contrato visual) | theme FSE |

Hasta el corte:

```text
Until migration is complete, the live static repository is a production content source.
```

Hardcoded ≠ dummy. Extraer, no reescribir. Conteos deben cuadrar. Patterns FSE no almacenan colecciones reales.

---

## 4. Un template no crea una Page

```text
A TEMPLATE DOES NOT CREATE A WORDPRESS PAGE.
```

`templates/page-comunidad.html` (o, en docs históricos, `page-comunidad.php`) **no** implica que
exista `/comunidad` ni un objeto Page.

Debe existir:

1. El objeto WordPress (Page, post o CPT) con el slug correcto.
2. La plantilla de bloques asignada o resuelta por jerarquía.
3. Rewrite/permalinks que resuelvan la **ruta entrante**.
4. El comportamiento y los assets de esa vista.

**Colisión conocida de este proyecto:** si el CPT `event` usa rewrite slug `eventos`, **no** se
publica una Page con slug `eventos` (`docs/12-theme-file-structure.md`). El listado es
`templates/archive-event.html`.

Antes del cutover: estrategia de creación/importación de Pages institucionales (ADR 0033). Preferencia:
WP-CLI, fuente canónica, idempotente, dry-run, create-missing-only, skip si existe, no pisar
wp-admin, no borrar, production guard, QA posterior. **No implementar el importador en esta fase
de documentación.**

---

## 5. Páginas con diseño o JS específico

No asumir que todas las Pages pueden usar `templates/page.html` genérico.

Para cada URL especial, verificar la cadena:

```text
static HTML
  → WP object (Page / post / CPT)
    → template (blocks)
      → parts / patterns
        → CSS
          → JavaScript
            → assets
              → responsive
                → accessibility
```

Una Page que deja de devolver 404 pero pierde diseño o comportamiento **no está migrada**.

### Paridad de JavaScript

No basta con que exista `assets/js/main.js` encolado. Comprobar:

- el script **realmente se carga** en esa vista;
- selectores (`#nav-toggle`, `#nav-menus`, `#gallery-albums`, `[data-share-title]`, `[data-calendar-title]`, `[data-tooltip]`, `#gallery-data`, …);
- DOM esperado (ids, clases, `<dialog>` o el equivalente Gutenberg);
- `data-*` attributes;
- estado ARIA (`aria-expanded`, `aria-current`, `aria-hidden` del hint del calendario);
- eventos (click, Escape, resize, primer/segundo toque en puntero grueso);
- formulario de contacto (hoy `action="#"` no envía; en WP: Contact Form 7, ADR 0026, elegible
  en el corte — ADR 0041; actualizar párrafos del formulario en `/privacidad`);
- comportamiento dinámico (diálogo de calendario, `.ics`; galería: bloque Gutenberg, **sin**
  paginación numerada — OWN-011).

Diferencia ya decidida: `gallery.js` **no** se porta; la galería pasa al bloque Gutenberg con
lightbox nativo (ADR 0021). Eso debe quedar en la matriz como «sustitución documentada», no como
olvido.

Inventario de scripts de la maqueta (CURRENT STATE):

| Archivo | Dónde se carga | Rol |
| ------- | -------------- | --- |
| `assets/js/main.js` | Todas las páginas + 404 | Menú móvil (`#nav-toggle` / `#nav-menus`); switcher de idioma solo UI (el sitio sigue en español; i18n real = `POST-001`–`POST-004`, no el corte) |
| `assets/js/gallery.js` | `/galeria` | Paginación por álbum (12 imágenes), query params, JSON en `#gallery-data` / `#gallery-albums-data` |
| `assets/js/share.js` | singles de eventos y de blog | Diálogo «Compartir» (`[data-share-title]`) |
| `assets/js/calendar.js` | `/eventos` y ficha de evento vigente | Diálogo «Añadir al calendario» + tooltips/toque del grid `.eventos-calendar-grid` |

Estado del porte (WU-07/WU-08A): `main.js` y `calendar.js` viven ya en el theme —
`assets/js/main.js`, `assets/js/calendar-tooltips.js` y `assets/js/calendar-dialog.js`, este
último la mitad del diálogo—, y `share.js` se portó literal. Los tres se encolan solo en las
vistas cuyo bloque los necesita. El copy de los mensajes de compartir viaja como meta editable
(`share_whatsapp`/`share_x`/`share_threads`), no como HTML congelado.

CSS: fuente `assets/css/main.css` → servido `assets/css/main.min.css` (`npm run build:css`). En
WordPress: `theme.json` + hoja complementaria (ADR 0029). La maqueta estática sigue con un solo
`main.css` (ADR 0009 vigente solo para static).

---

## 6. Routing: reglas preventivas de este proyecto

Árbol oficial: `docs/11-arbol-urls-final.md`. Si una URL no está ahí, no existe.

Antes de aprobar un CPT o taxonomía con archivo público:

1. Revisar [nombres reservados de WordPress](https://codex.wordpress.org/Reserved_Terms) y query vars nativas.
2. Definir `slug` de rewrite **explícitamente** (este proyecto: `event` → `/eventos/{slug}`, listado `/eventos`; sin archivos por ciudad — ADR 0022).
3. Probar archive, single, taxonomías que sí tengan archivo (`post_tag` en `/blog/tag/{slug}/`, ADR 0031).
4. Probar `get_permalink()`.
5. Probar **resolución de URL entrante** (HTTP 200/404 real). `get_permalink()` correcto no prueba el incoming route.
6. Si hay riesgo de colisión con query vars nativas, usar `query_var` explícita y namespaced (p. ej. prefijo `cdd_`). No copiar query vars de otros sitios.
7. `flush_rewrite_rules()`: en activación/upgrade del plugin cuando corresponda; **nunca** en cada request.

Canonicalización actual (`.htaccess` estático, CURRENT STATE): HTTPS, host sin www,
`caminodeldharma.org`, sin barra final, sin `index.html` visible, redirects legacy, `410` en
`/prueba` y `site.webmanifest`. Tras el corte, WordPress reescribe `.htaccess`: hay que **portar**
esa política, no asumir que el archivo estático sigue gobernando.

Sitemap: en static, `sitemap.xml` manual. En WordPress, nativo `/wp-sitemap.xml` (ADR 0030).
`robots.txt` deberá apuntar al sitemap vigente.

**No hay búsqueda pública.** No crear ruta de search.

---

## 7. Importers vs fixtures

Ver ADR 0033. Resumen:

- **Importador real:** persistente, create-missing-only, sin cleanup destructivo.
- **Fixtures:** marcados, removibles, solo teardown de objetos propios, nunca en producción pública.

---

## 8. Deployment safety

```text
STATIC DEPLOY  ≠  WORDPRESS CODE DEPLOY  ≠  WORDPRESS CONTENT
```

| Operación | Qué mueve | Qué no mueve |
| --------- | --------- | ------------ |
| Static deploy (CURRENT) | HTML, CSS, JS, assets, `.htaccess`, `robots.txt`, `sitemap.xml` → `public_html` | `docs/`, `scripts/` |
| WP code deploy (FUTURE) | Solo `wordpress/wp-content/themes/camino-del-dharma/` y `plugins/camino-del-dharma-core/` | core, `wp-config.php`, uploads, plugins de terceros, BD |
| WP content | Importador WP-CLI / edición wp-admin | Código del theme |

En el cutover:

- El workflow/ZIP legacy de HTML **no** puede sobrescribir el document root de WordPress.
- Sin `--delete` / mirror sobre `wp-content` compartido (ADR 0013).
- `.htaccess` de la raíz del servidor: tratamiento explícito (diff, backup, reglas de permalinks WP + redirects legacy de este dominio).
- Hostinger File Manager / FTP: si se usa FTP/FTPS, cuenta dedicada, alcance mínimo, directorios remotos explícitos, secretos por environment, producción separada. Hoy el estático se sube por File Manager (README); no hay workflow FTPS en este repo.

### Rollback window

No borrar el sitio estático el día del corte. Conservar:

- artefacto estático versionado (tag Git + ZIP desplegado);
- backup de BD WordPress y `uploads/`;
- plan de document root / DNS (volver el `public_html` al ZIP estático **o** restaurar WP);
- media y `.htaccess` respaldados.

La ventana la define el propietario. El corte no está cerrado si el rollback no es ejecutable.

---

## 9. Environments

Nombres que **sí** usa este repositorio:

| Environment | Nombre en docs | Estado |
| ----------- | -------------- | ------ |
| **LOCAL** | Docker Compose, `WP_ENVIRONMENT_TYPE=local` (ADR 0023, `docker-wordpress-playbook.md`) | Planificado; `docker-compose.yml` aún no existe |
| **STAGING** | WordPress en **otra instancia Hostinger**, **sin dominio custom**, hasta el switch (OWN-005) | Planificado / en paralelo; noindex; no pisa el estático. Hostname no versionado en el repo |
| **PRODUCTION** | `https://caminodeldharma.org` en Hostinger `public_html` | **Actual y hasta el corte:** sitio **estático** |

No mezclar entre environments: credenciales, base de datos, uploads, política de indexación
(staging no debe indexarse), fixtures, dominios.

No hay otros nombres de environment en este proyecto. No importar nomenclatura de otros repos.

---

## 10. QA: deploy success ≠ application success

Un Stylelint verde, un ZIP generado o un File Manager «success» solo prueban esa operación.

Smoke HTTP mínimo (tras cutover, sobre el hostname del environment bajo prueba):

- Cada URL de la [matriz](matriz-migracion-static-wordpress.md) con estrategia «migrada».
- CPT: **archive + single** (`/eventos` y al menos una ficha).
- Blog: **archivo + single**.
- Pages institucionales críticas: `/`, `/comunidad`, `/linaje`, `/practica`, `/contacto`, `/donaciones`, `/galeria`, `/privacidad`.
- 404 real (ruta fuera del árbol) con `templates/404.html`.
- Formulario: prueba funcional en staging (correo a `caminodeldharma1@gmail.com`), no solo markup.
- Comportamiento: menú teclado, calendario `/eventos`, compartir en una ficha, galería (bloque Gutenberg).

**Paridad vs producción publicada (OWN-007):** antes de dar por buena una Page, un import o el
corte, comparar copy, contenido **y** estilos con `https://caminodeldharma.org`. El repo local o
materiales legacy no bastan. Si ZIP en Hostinger y `VERSION` del repo no coinciden, registrar el
delta; no asumir que son el mismo artefacto (ADR 0040).

Honestidad probatoria ya adoptada: `Unverified` / `Pass (local)` / `Pass`. `Pass (local)` no
sustituye a `Pass` para PHP/Apache/HTTPS/correo reales de Hostinger (ADR 0023).

Niveles: ver `playbook-migracion-static-wordpress.md` §8.

---

## 11. FSE: la maqueta se migra directo a un block theme

Ruta de este proyecto (ADR 0029, ADR 0032):

```text
maqueta estática (HTML / CSS / JS)
        ↓
WordPress Full Site Editing (block theme)
  templates/*.html   parts/*.html   patterns/
  theme.json + CSS complementario
  plugin camino-del-dharma-core (dominio)
```

**No** existe el paso `theme clásico PHP`. No construir `front-page.php` / `page-comunidad.php` «para
convertirlos después a bloques». `functions.php` del block theme solo hace bootstrap (encolar,
supports); no es una etapa clásica.

Un template HTML de bloques **tampoco** crea la Page (regla §4).

Mapping desde la **maqueta**, no desde un theme PHP:

| Estático (contrato actual) | Destino FSE |
| -------------------------- | ----------- |
| `index.html` y páginas `*/index.html` | `templates/*.html` (front-page, page-comunidad, …) |
| Header / footer / nav repetidos | `parts/*.html` |
| Bloques reutilizables (meditación, mantras) | `patterns/` |
| Copy institucional | contenido en BD (`post_content` / bloques), no hardcodeado en plantillas cuando sea evitable |
| CPT, taxonomías, WP-CLI | plugin `camino-del-dharma-core`, **no** el theme |
| JS de la maqueta | encolado desde el theme, o bloque dinámico solo donde se justifique (calendario de `/eventos`) |
| Galería `gallery.js` | excepción ADR 0021: bloque Gutenberg + lightbox nativo |

**Pattern ≠ content.** `patterns/` = estructura reutilizable. Eventos, posts y las 35 fotos de galería
viven en la BD, no en patterns.

Producción **sigue** en el estático mientras FSE se construye en Docker/staging. Extractores (no ahora):
HTML/JSON → payload → dry-run → import. Conteos deben cuadrar (ADR 0034). Ledger de cambios durante
el build; freeze corto al corte. Rollback: no borrar el artefacto estático el día del corte.
**NO CUTOVER WITH BROKEN NAVIGATION.**

---

## 12. Inventario estático (contrato: todo esto se migra o se registra como excepción)

### Entry point y páginas HTML

| Recurso | Rol |
| ------- | --- |
| `index.html` | Home |
| `comunidad/index.html`, `linaje/index.html`, `practica/index.html`, `practica/videos/index.html`, `practica/meditacion-semanal-en-linea/index.html` | Institucionales / secundarias |
| `eventos/index.html` + `eventos/{slug}/index.html` | Listado y singles |
| `galeria/index.html` | Galería (JS propio) |
| `blog/index.html` + `blog/{slug}/index.html` | Archivo y entradas |
| `donaciones/index.html`, `contacto/index.html` | Contribuir y contacto |
| `404.html` | Error; **no** es URL pública `/404` |

URLs canónicas: sin barra final. Ver matriz.

### Navegación

Navbar + subnav (Galería, Blog, Contribuir, Contacto) según `05-arquitectura-informacion-navegacion`.
Ítem Eventos condicional si no hay vigentes. Footer en todas las páginas (identidad, contacto,
redes, donaciones, datos bancarios).

### Formularios y búsqueda

- Contacto: markup Nombre / Correo / Mensaje / Enviar; `action="#"` **no operativo** en static.
- **No buscador.**

### Archivos, listados, singles

- Listados: `/eventos`, `/blog`, `/galeria` (álbumes).
- Singles: eventos y posts actuales en sitemap.
- `/sanghas/` está en el modelo como opcional y **fuera del alcance inicial** de Fase 3 (ADR 0024).

### SEO y ops estáticas

- Metadata: `<title>`, description, canonical, Open Graph, JSON-LD por página.
- `robots.txt` → `https://caminodeldharma.org/sitemap.xml`
- `sitemap.xml` + `sitemap.xsl`
- `llms.txt`
- `.htaccess` (canonicalización, redirects, caché, seguridad; HSTS **comentado**, ADR 0020)
- Favicon, fuentes, imágenes (JPEG/WebP), audio (mp3 → Media Library, OWN-009). `.ics` **generado**
  por el plugin (no biblioteca); vigentes: `/eventos/ical/{slug}.ics`; pasados: 410 y borrar
  huérfano (OWN-012, OWN-013). PDF de recitación: **RETIRE** (OWN-002); no migrar.

Todo esto forma parte del contrato. No hace falta un equivalente WordPress en esta tarea de
documentación; hace falta no olvidarlo en Fase 3.

---

## 13. Documentos relacionados

| Documento | Rol |
| --------- | --- |
| [inventario-contenido-produccion-static.md](inventario-contenido-produccion-static.md) | Inventario live (ADR 0034) |
| [conteos-reconciliacion-migracion.md](conteos-reconciliacion-migracion.md) | Conteos que deben cuadrar |
| [redirect-ledger.md](redirect-ledger.md) | KEEP / 301 |
| [matriz-migracion-static-wordpress.md](matriz-migracion-static-wordpress.md) | Una fila por URL y por entidad |
| [cutover-checklist-wordpress.md](cutover-checklist-wordpress.md) | Pre / cutover / post |
| [migracion-static-wordpress.md](migracion-static-wordpress.md) | Ledger de diferencias static vs WP |
| [playbook-migracion-static-wordpress.md](playbook-migracion-static-wordpress.md) | QA, WU, anti-patrones |
| [17-orden-implementacion.md](17-orden-implementacion.md) | Fases; el corte remite a este contrato |

---

**Versión:** 1.1 · **Fecha:** 2026-08-30
