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
| `/galeria` | `/galeria` | KEEP | igual | Page |
| `/contacto` | `/contacto` | KEEP | igual | Page |
| `/donaciones` | `/donaciones` | KEEP | igual | Page |
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
| `/blog` | `/blog` | KEEP | posts page | |
| `/blog/circulos-de-presencia-consciente` | misma | KEEP | `post` | |
| `/blog/sangha-refugio-hiperconexion` | misma | KEEP | `post` | |
| *(404 genérico)* | plantilla 404 | KEEP behavior | HTTP 404 | no URL `/404` |

`/privacidad`: **no live**. Cuando exista copy legal: publicar + enlazar; no 301 desde nada hoy.

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

---

## Sitemap / robots

| Recurso static | Plan WP |
| -------------- | ------- |
| `sitemap.xml` manual | `/wp-sitemap.xml` (ADR 0030); redirigir o retirar el XML viejo para no duplicar |
| `robots.txt` → sitemap manual | actualizar a sitemap nativo en producción; staging noindex |

---

**Versión:** 1.0
