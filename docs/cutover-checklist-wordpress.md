# Checklist de cutover static → WordPress

Checklist durable para el corte de producción. No sustituye Fase 2.5 ni la [matriz](matriz-migracion-static-wordpress.md).

**Contrato:** [contrato-migracion-static-wordpress.md](contrato-migracion-static-wordpress.md) (ADR 0032).
**Importación:** ADR 0033. **Despliegue:** ADR 0013, ADR 0015.

```text
DEPLOY SUCCESS ≠ APPLICATION SUCCESS
```

No marcar un ítem por un ZIP, FTP o File Manager en verde. Cada casilla exige evidencia del
environment bajo prueba (`Pass (local)` vs `Pass` en Hostinger).

WordPress **no está en producción** hoy. Usar este checklist cuando se ejecute el corte, no antes.

---

## PRE-CUTOVER GATE (obligatorio)

- [ ] Content inventory complete ([inventario](inventario-contenido-produccion-static.md))
- [ ] URL inventory complete (sitemap + [redirect-ledger](redirect-ledger.md))
- [ ] Migration matrix complete (URLs **y** entidades: 10 events, 2 posts, 35 gallery items)
- [ ] Content counts reconciled ([conteos](conteos-reconciliacion-migracion.md))
- [ ] Media inventory complete (`galeria-04` = Page, no álbum — OWN-001; PDF **RETIRE** — OWN-002; huérfanas seed oculto — OWN-003)
- [ ] Importer dry-run clean
- [ ] Importer tested (idempotent; create-missing-only; 0 fixtures públicos)
- [ ] FSE templates ready (static → `templates/*.html`; copy editorial en BD)
- [ ] JS parity tested
- [ ] Routing tested (incoming HTTP; archive+single; 404)
- [ ] Redirects tested (ledger; sin cadenas)
- [ ] SEO metadata preserved (title, description, canonical, OG, JSON-LD)
- [ ] Backup verified (static files + WP DB + uploads)
- [ ] Rollback defined (static artifact + document root + ventana)
- [ ] No unresolved content loss
- [ ] No important URL without KEEP/301
- [ ] No broken navigation (header, footer, CTAs, cards)
- [ ] Parity vs **producción publicada** (`https://caminodeldharma.org`): copy, contenido **y** estilos (OWN-007). No basta el repo local.

## PRE-CUTOVER (detalle operativo)

- [ ] Backup de archivos del `public_html` estático (y tag Git de la maqueta)
- [ ] Backup de base de datos WordPress de staging (y plan de backup de producción WP en el momento del corte)
- [ ] Inventario de URLs = [matriz](matriz-migracion-static-wordpress.md) + `sitemap.xml` actual + redirects de `.htaccess`
- [ ] Migration matrix completa (todas las filas con estrategia CONTENT + PRESENTATION + ROUTING + BEHAVIOR + QA)
- [ ] Importador de contenido probado (dry-run + `--apply` en local y staging; create-missing-only; sin pisar wp-admin)
- [ ] Templates de bloques (`templates/*.html`) mapeados **desde la maqueta**, no desde un theme PHP clásico (ADR 0029)
- [ ] JS probado (menú, share, calendario, galería Gutenberg; selectores y ARIA)
- [ ] Assets probados (imágenes, fuentes, audio, PDF, `.ics`, favicon; sin 404)
- [ ] CPT routing probado: `/eventos` archive + **10** singles (ADR 0035); **incoming HTTP**, no solo `get_permalink()`; pasados **sin** Inscribirme **ni** «Añadir al calendario» (OWN-012)
- [ ] `/eventos/ical/encuentro-nacional-2026.ics` → **410**; Círculos `.ics` **generado** 200 solo si `hoy ≤` fecha de fin (OWN-009, OWN-013); **noindex** (OWN-014); no está en `/wp-sitemap.xml`
- [ ] Evento con fecha de fin vencida: bloque archivo, sin inscripción ni calendario; ningún `.ics` huérfano en Media Library ni en disco
- [ ] wp-admin: herramienta «Eliminar huérfanos» (OWN-015) — dry-run + apply; solo `.ics`; no borra fotos OWN-003
- [ ] Tags del blog: archivo existe; `noindex` hasta volumen (ADR 0031)
- [ ] Autores (ADR 0037): `/author/zheng-gong` **200** (CPT, no user); `query_var === 'blog_author'`; users `/author/{login}` **404**; byline ≠ usuario WP; publicar post exige ficha; archivo `/author` noindex
- [ ] `/comunidad` (WP): enlaces a `/author/zheng-gong` y a la ficha Comunidad (OWN-016); copy live no pisado; estático no cambiado por esto
- [ ] Sin Page slug `eventos` si el CPT usa ese rewrite
- [ ] Contact Form 7: actualizar `/privacidad` y revisión legal, o el form sigue gated (ADR 0026, ADR 0039)
- [ ] Rollback definido (volver a estático versionado **o** restaurar BD+files WP; dueño y ventana)
- [ ] Indexing policy definida: staging no indexable; producción: `robots.txt` + sitemap nativo (ADR 0030); no dejar «Disuadir motores de búsqueda» en producción
- [ ] Deploy scope auditado: theme + plugin propio solamente; no core, no `wp-config.php`, no uploads, no plugins de terceros sobrescritos
- [ ] Flujo ZIP/HTML legacy **incapaz** de escribir sobre el document root WP tras el corte (README/CONTRIBUTING actualizados)
- [ ] `.htaccess` de producción: backup + plan para permalinks WP **y** redirects legacy de este dominio
- [ ] HSTS sigue aplazado hasta ≥30 días estables post-corte (ADR 0020); no activarlo en el día del corte por este checklist

---

## CUTOVER

- [ ] WordPress operativo en el hostname de producción
- [ ] Theme `camino-del-dharma` desplegado y **activo**
- [ ] Plugin `camino-del-dharma-core` desplegado y **activo**
- [ ] Contact Form 7 activo **solo** si ADR 0039/0026 lo permiten
- [ ] Pages institucionales reales creadas/importadas (no solo templates en disco)
- [ ] Slugs correctos (ADR 0008, sin barra final en la URL pública canónica)
- [ ] Permalinks / rewrite verificados (`flush` de activación ya ocurrido; **no** flush por request)
- [ ] Ajustes: portada + página de entradas (`/blog`)
- [ ] Navegación completa sin 404 inesperados (navbar, subnav, footer)
- [ ] Formularios: contacto envía **o** el estado no-operativo está explícito y los CTAs WhatsApp/correo funcionan
- [ ] JS funcionando en las URLs de la matriz
- [ ] Assets sin 404
- [ ] Search: confirmado **ausente** (no hay buscador en este sitio)
- [ ] Custom 404 funcionando (status 404 + `templates/404.html`)
- [ ] No fixtures públicos accidentales (grep `_cdd_fixture` / equivalente)
- [ ] Redirects legacy 301/410 verificados con HTTP

---

## POST-CUTOVER

- [ ] Anonymous smoke test (sesión sin cookies de admin)
- [ ] Desktop QA (paridad visual vs **sitio publicado**, no solo maqueta local)
- [ ] Mobile QA
- [ ] Keyboard QA (menú, calendario, diálogos, lightbox)
- [ ] Accessibility basics (`docs/19-accesibilidad-estandares`)
- [ ] `robots.txt` correcto para producción
- [ ] Sitemap nativo alcanzado; XML estático antiguo no compite o redirige (ADR 0030)
- [ ] Canonical por URL
- [ ] Metadata / OG / JSON-LD
- [ ] Redirects (host, HTTPS, legacy)
- [ ] Cookies: sin analítica (ADR 0019); embeds según política vigente
- [ ] Privacy: `/privacidad` publicada (ADR 0039); no reescribir el copy live; revisión legal sigue abierta
- [ ] Media / downloads (PDF, audio, `.ics`)
- [ ] Legacy static deploy disabled/retired (README, CONTRIBUTING, procedimiento ZIP)
- [ ] Rollback window evaluated
- [ ] Production evidence documented (fecha, tag, backups, matriz en estado Migrada)
- [ ] Search Console: sitemap nuevo; no asumir que el deploy equivale a indexación
- [ ] HTTP smoke anónimo
- [ ] CPT archives/singles
- [ ] 301 del ledger
- [ ] no unexpected 404
- [ ] content counts still reconcile
- [ ] Artefacto estático **aún disponible** para rollback (no borrado)

---

## Referencias

- `docs/17-orden-implementacion.md` § Transición (pasos históricos de corte; este checklist los detalla)
- ADR 0013, 0015, 0020, 0029, 0032, 0033, 0034
