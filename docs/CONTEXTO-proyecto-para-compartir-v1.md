# Camino del Dharma — Contexto del proyecto

Documento de contexto autocontenido, pensado para compartir con otra IA (ChatGPT u otra) que no
tiene acceso al repositorio. Resume el proyecto, su estado actual, sus decisiones arquitectónicas y
lo que está en curso, a fecha **2026-09-01**.

---

## 1. Qué es el proyecto

**Camino del Dharma** es el sitio web de una comunidad buddhista en Colombia (Personería Jurídica
Especial – Ministerio del Interior). En palabras del propio plan del proyecto:

> "No se trata de una web informativa ni comercial, sino de un espacio de acogida que oriente, inspire
> confianza y facilite el primer contacto con la práctica buddhista."

No es una landing page, ni un sitio comercial, ni un CMS orientado a marketing. No tiene funnels, no
tiene presión de conversión, no tiene analítica de comportamiento. Es una plataforma comunitaria.

El sitio orienta hacia: la comunidad, el linaje (tradición Chan y Tierra Pura), la práctica
(meditación semanal, retiros, mantras), eventos, una galería, cómo contribuir (donaciones, nunca
como transacción) y cómo contactar (WhatsApp, correo, y en el futuro un formulario funcional).

---

## 2. Estado actual (2026-09-01)

| Aspecto | Estado |
| --- | --- |
| **Producción** | Sitio estático (HTML/CSS/JS) en `https://caminodeldharma.org`, Hostinger compartido |
| **Versión en el repositorio** | **1.0.35** (`VERSION`) |
| **Versión en Hostinger** | Paridad verificada 2026-08-31 (delta 0); no asumir paridad futura sin comparar (OWN-006/007) |
| **Fase activa** | Fase 2 **en producción**. Fase 3 **implementada en Git** (WU-00–WU-10, BUG-001). Staging Hostinger **no** creado |
| **Estructura del repo** | Monorepo: `static/` (HTML desplegable) + `wordpress/` (plugin `camino-del-dharma-core` 0.7.1, theme FSE `camino-del-dharma` 0.5.1) |
| **Fuente editorial pre-corte** | Producción publicada (`https://caminodeldharma.org`). La carpeta legacy `content-source/` fue **eliminada permanentemente** (OWN-017, ADR 0040) |
| **Despliegue estático** | Manual (ZIP → File Manager). CI/CD de **deploy** pospuesto (ADR 0016) |
| **CI de calidad** | `.github/workflows/test.yml` (checks `php` y `css`); no despliega. `main` protegida (ADR 0043) |
| **Pre-staging** | D-02/D-03/D-04 en `main` **antes** de crear Hostinger (OWN-035, issues #10–#12). D-08 (#5) puede ir después (A2) |
| **Backlog de dueño** | **Cerrado** (v1.28): OWN-001–OWN-035. CF7: elegibilidad ADR 0041; **entrega** ADR 0045. Feeds 404: ADR 0044 |
| **Fases posteriores** | `POST-001`–`POST-007` (i18n) abiertas; `POST-008`–`POST-010` decididas. **No** se implementan en el corte |

---

## 3. Principios de producto (qué es y qué NO es)

**El sitio SÍ permite (cuando exista WordPress):** actualizar la meditación semanal, publicar/ocultar
eventos, mantener un cronograma, incrustar videos, compartir testimonios y editar Pages — sin tocar
código ni servidor para el contenido editorial.

**El sitio NO tiene, por decisión explícita:**

- Buscador
- Área privada / registro de usuarios
- Sistema de cursos
- Pagos internos (cualquier inscripción con costo redirige a una plataforma externa)
- Analítica con cookies, de ningún tipo
- PWA / manifest / Service Worker
- Un lightbox de galería propio (en WP: nativo de Gutenberg)
- Paginación numerada de galería en el corte (OWN-011)

**Tono editorial:** sobrio, cálido, sin urgencia. CTAs como "Practica con nosotros", "Participar",
"Inscribirme", "Preinscribirme", "Ver evento", "Donar" — nunca lenguaje de venta.

**Migración imperceptible:** el cambio static → WordPress no debe notarse para los visitantes, salvo
funcionalidades y contenido explícitamente aprobados (OWN-007, ADR 0040).

---

## 4. Arquitectura de decisiones (ADR) — resumen

Hay **45 ADR** aceptados (índice: `docs/adr/README.md`). Los más relevantes hoy:

| ADR | Decisión | Por qué importa |
| --- | --- | --- |
| 0001 / 0002 | Estático = base definitiva; WordPress adapta, no rediseña | Paridad visual y de comportamiento |
| 0003 | Sin PWA nunca | Web tradicional |
| 0004 / 0005 | Git gobierna código; producción no se edita a mano | Trazabilidad |
| 0008 | URLs públicas **sin barra final** | Ya rompió producción dos veces con rutas relativas |
| 0012 | WordPress es el motor de contenido | — |
| 0013 | Código en Git; contenido editorial en WP tras el corte | Tres deploys distintos |
| 0014 | Monorepo `static/` + `wordpress/` al iniciar Fase 3 | — |
| 0015 / 0016 | Deploy manual temporal; automatización de CD pospuesta | No crear workflows de deploy aún |
| 0019 | Sin analítica con cookies; GA4 descartado | Search Console |
| 0020 | HSTS aplazado hasta ≥30 días tras el corte | — |
| 0021 | Lightbox nativo de Gutenberg; no migrar `gallery.js` | — |
| 0022 | Ciudad = taxonomía, no URL de filtro | Sin doorway pages |
| 0023 | Local Docker: PHP 8.3 + MariaDB 11.8 | Paridad Hostinger |
| 0024 | Plugin `camino-del-dharma-core` = dominio; theme = presentación | — |
| 0025 / 0026 | Plugins de terceros solo con ADR; CF7 aprobado; producción en el corte: ADR 0041; entrega: ADR 0045 | — |
| 0027 | Estándares de ingeniería; WPCS y seguridad no negociables | — |
| 0029 | Theme **FSE / block theme** — **sin** theme clásico PHP intermedio | Sustituye la arquitectura clásica |
| 0030 / 0031 | Sitemap nativo WP; tags de blog noindex hasta volumen | — |
| 0032 | Contrato: CONTENT + PRESENTATION + ROUTING + BEHAVIOR + OPERATIONS | Theme activado ≠ migración completa |
| 0033 / 0034 | Importador ≠ fixtures; HTML/JSON live = contenido de producción | Extraer, no reescribir |
| 0035 | Todo evento tiene single; pasados sin inscripción | 10 `/eventos/{slug}` |
| 0036 | Álbumes `/galeria/{slug}` vía taxonomía; noindex hasta volumen | Hub `/galeria` KEEP |
| 0037 | CPT `blog_author` en `/author/{slug}`; no Users WP | — |
| 0038 | TDD + wp-phpunit; Sonar solo plugin + theme | — |
| 0039 | `/privacidad` publicada (provisional) | — |
| 0040 | `content-source/` retirado; producción publicada gobierna pre-corte | — |
| 0041 | CF7 en el corte sin espera de asesoría legal; copy WP del formulario | OWN-018; punto 5 → 0045 |
| 0042 | Meta Gutenberg: sin metabox clásico sin sync; no es defecto de corte | OWN-019 |
| 0043 | `main` protegida; Conventional Branch + Commits; PR | — |
| 0044 | Feeds nativos **404** en el corte; RSS futuro POST-010 | OWN-025 |
| 0045 | Entrega CF7 es **gate del corte**; prueba técnica ≠ buzón comunitario | OWN-033 |

ADR 0028 (privacidad aplazada) está **sustituido** por 0039. ADR 0009 queda histórico para WordPress
(sustituido por 0029); sigue describiendo el CSS del estático.

---

## 5. Modelo de contenido y URLs

**Objetos en el corte:**

- Pages institucionales (home, comunidad, linaje, práctica, videos, meditación, galería, donaciones,
  contacto, blog archivo, privacidad)
- CPT `event` (10) — todos con single público (ADR 0035)
- CPT `blog_author` (2 semillas) + 2 posts
- Taxonomía `gallery_album` (General / 2023 / 2021) — URLs `/galeria/{slug}` noindex al corte
- Media real vía seed (OWN-009-img); mp3 a Media Library; `.ics` **generado** por el plugin (OWN-009)

**Fuera del corte inicial:** CPT `sangha` (ADR 0024).

**Política de URL pública:** **sin barra final** (ADR 0008). El doc 11 puede mostrar barras por
convención de árbol; la forma canónica HTTP es `/comunidad`, no `/comunidad/`.

**Árbol canónico (sin barra final):**

```text
/
/comunidad
/linaje
/practica
/practica/videos
/practica/meditacion-semanal-en-linea
/eventos
/eventos/{slug}
/galeria
/galeria/{slug}          (nueva en WP; noindex hasta volumen)
/donaciones
/contacto
/blog
/blog/{slug}
/author/{slug}           (nueva en WP; CPT blog_author)
/privacidad
/eventos/ical/{slug}.ics (solo vigentes; noindex; generado)
```

**Sin buscador, sin páginas de filtro por ciudad/tipo, sin URLs creadas solo por SEO.**

---

## 6. Arquitectura técnica (theme + plugin WordPress)

- **Ruta única:** estático de producción → **FSE / block theme** (ADR 0029). **No** hay theme
  clásico PHP (`front-page.php`, `page-*.php`) como puente.
- **Theme:** `camino-del-dharma` — `theme.json`, `templates/*.html`, `parts/*.html`, CSS
  complementario. Solo presenta.
- **Plugin:** `camino-del-dharma-core` — CPTs, taxonomías, meta, ICS, limpieza de huérfanos ICS,
  importador/seed, WP-CLI.
- **CSS:** tokens iniciales alineados con la maqueta; Global Styles editable por Administrador;
  hoja complementaria para layout/a11y. Sin frameworks, sin `!important`.
- **Galería:** bloque Gutenberg + lightbox nativo; sin paginación numerada en el corte.
- **Pruebas:** TDD desde el primer PHP propio; `composer test` (gate barato); wp-phpunit para
  contratos in-process; Sonar Automatic Analysis **solo** plugin + theme (ADR 0038).
- **Estándares:** WPCS, prefijo único, escaping/sanitización/nonces; cadenas translation-ready
  aunque el sitio sea monolingüe hoy.

---

## 7. Infraestructura

- **Hosting:** Hostinger, `caminodeldharma.org`.
- **PHP / MariaDB objetivo:** 8.3 / 11.8 (ADR 0023).
- **Local:** Docker Compose versionado en el repo (ADR 0023, WU-02).
- **Staging (OWN-005):** otra instancia Hostinger **sin dominio custom**, noindex, en paralelo al
  estático hasta el switch. No pisa `public_html` de producción.
- **SSH:** disponible en la cuenta; no es el canal de deploy hoy.

---

## 8. Despliegue

- **Hoy (estático):** ZIP manual **desde el contenido de `static/`** (README, ADR 0014/0015).
  No incluir `docs/`, `scripts/`, `wordpress/`, `tests/`.
- **WordPress code deploy ≠ content deploy ≠ static deploy** (ADR 0013). Tras el corte, un ZIP
  estático **nunca** escribe sobre el document root de WordPress.
- **CD:** pospuesto (ADR 0016). **CI de calidad:** sí, cuando exista PHP de prueba (ADR 0038).

---

## 9. Seguridad y privacidad

- Sin cookies de analítica (ADR 0019). Medición: Search Console.
- HSTS aplazado (ADR 0020).
- `/privacidad` publicada (ADR 0039, provisional). Contact Form 7 **elegible en el corte**
  (ADR 0041 / OWN-018): el disclaimer basta para lanzar; en WordPress se actualizan solo los
  párrafos del formulario. **Entrega** al buzón comunitario es gate del corte (ADR 0045).
  Revisión legal = trabajo posterior, no gate.
- Canales humanos actuales: WhatsApp y correo.

---

## 10. Alcance de Fase 3 — dentro y fuera

**Dentro (corte):**

- Reorg monorepo `static/` + `wordpress/`
- Theme FSE + plugin de dominio
- Extracción e import de contenido real (Pages, 10 eventos, 2 posts, autores, galería, media)
- Contact Form 7 (con gate de privacidad)
- QA de los cinco entregables (ADR 0032) + paridad vs producción publicada
- D-02/D-03/D-04 en `main` antes de staging (OWN-035)
- Feeds nativos 404 (ADR 0044)
- Entrega de correo CF7 demostrada (ADR 0045)

**Fuera del corte:**

- CPT `sangha`
- Inglés / i18n (`POST-001`–`POST-007`)
- Wrap Sangha 320 px (`POST-008`), conteo admin de álbum (`POST-009`), RSS (`POST-010`)
- Buscador, analítica, HSTS, PWA
- URLs de filtro por ciudad/tipo
- Automatización de deploy
- Recrear `content-source/`

---

## 11. Pendientes reales (no incongruencias de gobernanza)

| Ítem | Estado |
| --- | --- |
| D-02 demo content, D-03 feeds 404, D-04 overflow `/practica` | Código pendiente (#10–#12); **antes** de Hostinger |
| Crear staging Hostinger | OWN-035: después de esos merges + «go» del propietario |
| D-08 SEO fichas `/author/{slug}` | Decidido; código pendiente ([#5](https://github.com/refo44/demo-caminodeldharma/issues/5)); A2 |
| Seed en Hostinger | OWN-032: SSH + `~/cdd-extract/` |
| Formulario CF7 end-to-end | Elegible (ADR 0041); gate de entrega ADR 0045 |
| HSTS | Aplazado post-corte |
| Inglés / i18n | `POST-001`–`POST-007` |

Ya resuelto y no debe reabrirse como duda:

- Política de barra final → **sin barra** (ADR 0008)
- Embeds YouTube → `youtube-nocookie.com` (ya en el estático)
- Gobernanza de copy → producción publicada (ADR 0040); `content-source/` eliminado

---

## 12. Documentos de referencia

- `docs/adr/README.md` — índice ADR 0001–0045
- `docs/17-orden-implementacion.md` — fases y criterios (v3.11)
- `docs/backlog-decisiones-owner-migracion.md` — OWN + POST (v1.28)
- `docs/contrato-migracion-static-wordpress.md` — contrato de aceptación
- `docs/inventario-contenido-produccion-static.md` + `conteos-reconciliacion-migracion.md`
- `docs/matriz-migracion-static-wordpress.md` + `redirect-ledger.md` + `cutover-checklist-wordpress.md`
- `docs/guia-pruebas-plugin-theme-fse.md` — TDD / wp-phpunit / Sonar
- `AGENTS.md` / `CLAUDE.md` — reglas para agentes

---

**Fin del documento de contexto.**
