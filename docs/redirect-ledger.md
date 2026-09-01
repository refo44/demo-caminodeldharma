# Redirect ledger (URLs de producción)

El sitio ya recibe visitas. Las URLs actuales son contrato (ADR 0008, ADR 0034).

Para cada URL: **KEEP** o **301** (o **410** ya existente). Nunca cambiar en silencio.
Prohibido: 404 temporales, cadenas, loops.

Probar **HTTP entrante**, no solo `get_permalink()`.

---

## Públicas actuales → destino WordPress (plan)

Forma canónica: **sin barra final**.

| Old / current URL | Expected new URL | Status | Canonical destination | Notas |
| ----------------- | ---------------- | ------ | --------------------- | ----- |
| `/` | `/` | KEEP | `https://caminodeldharma.org` | front-page |
| `/comunidad` | `/comunidad` | KEEP | igual | Page |
| `/linaje` | `/linaje` | KEEP | igual | Page |
| `/practica` | `/practica` | KEEP | igual | Page |
| `/practica/videos` | `/practica/videos` | KEEP | igual | Page hija o slug anidado |
| `/practica/meditacion-semanal-en-linea` | misma | KEEP | igual | Page |
| `/galeria` | `/galeria` | KEEP | igual | Page; hub SEO (ADR 0036) |
| `/galeria/general` | misma | **PLANNED KEEP** | término `gallery_album` | no existe en static; **noindex** hasta volumen |
| `/galeria/2023` | misma | **PLANNED KEEP** | término | noindex hasta volumen (5 fotos hoy) |
| `/galeria/2021` | misma | **PLANNED KEEP** | término | noindex hasta volumen (5 fotos hoy) |
| `/contacto` | `/contacto` | KEEP | igual | Page |
| `/donaciones` | `/donaciones` | KEEP | igual | Page |
| `/privacidad` | `/privacidad` | KEEP | igual | Page; aviso provisional (ADR 0039) |
| `/eventos` | `/eventos` | KEEP | archive CPT; **no** Page `eventos` | |
| `/eventos/circulos-de-presencia-consciente` | misma | KEEP | single `event` | |
| `/eventos/encuentro-nacional-2026` | misma | KEEP | single `event` | |
| `/eventos/pausa-profunda-cali` | misma | KEEP | single `event` | sin inscripción (pasado) |
| `/eventos/meditacion-presencial-barranquilla` | misma | **PLANNED KEEP** | single `event` | no existe en static; corte WP (ADR 0035) |
| `/eventos/festival-calma-en-la-ciudad` | misma | **PLANNED KEEP** | single `event` | nueva en corte |
| `/eventos/pausa-profunda-medellin` | misma | **PLANNED KEEP** | single `event` | nueva en corte |
| `/eventos/ansiedad-agotamiento-crisis-de-atencion` | misma | **PLANNED KEEP** | single `event` | nueva en corte |
| `/eventos/vesak-2026` | misma | **PLANNED KEEP** | single `event` | nueva en corte |
| `/eventos/buddhismo-tiempos-cansancio` | misma | **PLANNED KEEP** | single `event` | nueva en corte |
| `/eventos/6-encuentro-nacional-2025` | misma | **PLANNED KEEP** | single `event` | nueva en corte |
| `/eventos/ical/circulos-de-presencia-consciente.ics` | misma mientras vigente | KEEP | `.ics` **generado** (OWN-009) | **noindex** (OWN-014); al vencer (OWN-013): **410** |
| `/eventos/ical/encuentro-nacional-2026.ics` | — | **RETIRE / 410** | no servir | evento finalizado (OWN-012); no seed |
| `/blog` | `/blog` | KEEP | posts page | |
| `/blog/circulos-de-presencia-consciente` | misma | KEEP | `post` | |
| `/blog/sangha-refugio-hiperconexion` | misma | KEEP | `post` | |
| `/author/zheng-gong` | misma | **PLANNED KEEP** | CPT `blog_author` | no existe en static; corte WP (ADR 0037); indexable |
| `/author/comunidad-camino-del-dharma` | misma | **PLANNED KEEP** | CPT `blog_author` | slug cambiable **antes** del corte; luego KEEP/301 |
| `/author` (archivo de fichas) | misma | **PLANNED KEEP** | archive CPT | **noindex** hasta volumen (ADR 0037) |
| `/author/{user}` nativo WP | — | **404** | no es perfil | apagar rewrite de users |
| *(404 genérico)* | plantilla 404 | KEEP behavior | HTTP 404 | no URL `/404` |

`/privacidad`: **live** (ADR 0039). Enlace en el pie; no 301 desde nada. Disclaimer provisional se conserva. En WordPress, delta del formulario (ADR 0041); revisión legal no es gate.

`/blog/tag/{slug}`: no existe en static; en WP existirá con noindex (ADR 0031). No es 301 desde el estático.

`/sanghas`: no live; fuera de alcance inicial (ADR 0024).

---

## Ya en `.htaccess` (portar al corte)

| Entrada | Destino / status | Tipo |
| ------- | ---------------- | ---- |
| `/sangha-refugio-hiperconexion` | `/blog/sangha-refugio-hiperconexion` | 301 KEEP |
| `/encuentro-nacional-2026` | `/eventos/encuentro-nacional-2026` | 301 KEEP |
| `/pausa-profunda-cali` | `/eventos/pausa-profunda-cali` | 301 KEEP |
| `/prueba` | 410 | KEEP (WP histórico) |
| `/category`… | `/blog` | 301 KEEP |
| `/?page_id=10` | `/comunidad` | 301 KEEP |
| otros `/?page_id=` | `/` | 301 KEEP |
| `site.webmanifest` | 410 | KEEP (ADR 0003) |
| HTTP / `www` | `https://caminodeldharma.org{URI}` | 301 KEEP |
| barra final (excepto `/`) | sin barra | KEEP (ADR 0008) |
| `*/index.html` | URL limpia | KEEP |

WordPress reescribe `.htaccess`: estas reglas deben **reimplementarse** (plugin de dominio o bloque documentado), no asumirse.

**Portado en WU-08B (2026-08-31):** `wordpress/.htaccess` es el artefacto desplegable. Las reglas
propias viven **encima** del bloque `# BEGIN WordPress`, que el núcleo reescribe al guardar
enlaces permanentes. Verificado sobre Apache real con `curl`: un solo salto por regla, sin
cadenas ni loops (`.audit/fase3-validation-matrix.md` § WU-08B).

Dos desviaciones deliberadas respecto del `.htaccess` estático, ambas documentadas en el propio
archivo:

- **No viajan** `DirectoryIndex`, la reescritura de `*/index.html` ni `ErrorDocument 404
  /404.html`: en un document root de WordPress sombrearían el front controller y fabricarían
  404 blandos. WordPress sirve su plantilla 404 con estado 404 real.
- La condición HTTPS pasa de `[OR]` a **AND**. Con `[OR]`, una petición que llega segura por un
  proxy con TLS terminado (`%{HTTPS}` != `on` pero `X-Forwarded-Proto` = `https`) se redirige a
  una URL que vuelve a cumplir la condición: bucle. En producción no se dispara porque Hostinger
  fija las dos señales, pero el bucle latente no se porta. **El estático no se toca.**

Añadido en WU-08B: `sitemap.xml` → `/wp-sitemap.xml` (301), porque el sitemap manual queda
deprecado (ADR 0030) y la URL vieja sigue indexada.

---

## Sitemap / robots

| Recurso static | Plan WP |
| -------------- | ------- |
| `sitemap.xml` manual | `/wp-sitemap.xml` (ADR 0030); **301 desde `sitemap.xml`** ya portado en `wordpress/.htaccess` (WU-08B) |
| `robots.txt` → sitemap manual | actualizar a sitemap nativo en producción; staging noindex |

---

**Versión:** 1.3 — WU-08B: `.htaccess` de WordPress versionado y verificado; 301 del sitemap manual.
