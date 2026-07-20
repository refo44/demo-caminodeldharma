# Auditoría SEO Técnica — Camino del Dharma

| | |
|---|---|
| **Tipo de documento** | Auditoría SEO Técnica (estado de salud del sitio) |
| **Dominio** | https://caminodeldharma.org |
| **Versión en producción** | v1.0.14 (desplegada 2026-07-20) |
| **Fecha de corte** | 2026-07-20 |
| **Periodo cubierto** | Auditoría 2026-07-19 + continuaciones 2026-07-20 |
| **Fuente** | `.audit/` (auditoría de solo lectura sobre producción y fuente) |
| **Próxima revisión** | Tras el corte a WordPress + 30 días estables (`.audit/audit-schedule.md`) |

> **Naturaleza del sitio.** El estático actual es **temporal**: será sustituido por WordPress (ADR 0002 / 0018). Ninguna recomendación de este informe compromete al sitio a plazos largos (por ejemplo, HSTS de un año o `preload`) mientras esa migración siga pendiente.

---

## 1. Veredicto

**La salud técnica del sitio es alta y está verificada por dos vías independientes.** La auditoría interna dio 8/8 controles PASS, y PageSpeed Insights (Lighthouse 13.4.0) puntúa **SEO 100 / Rendimiento 99 (móvil) y 100 (escritorio)**. No hay ningún hallazgo crítico ni ningún defecto técnico que esté frenando la indexación.

Esto es importante decirlo con precisión, porque es el error de lectura más común en este proyecto: **la baja visibilidad orgánica del sitio no tiene causa técnica.** Lo técnico está resuelto. La brecha es de autoridad y contenido, y se trata en los informes [03](03-analisis-visibilidad-organica.md) y [04](04-posicionamiento-palabras-clave.md).

| Área técnica | Score | Confianza |
|---|---:|---|
| SEO interno / on-page | 100 | ALTA |
| SEO técnico (estados, canónicas, sitemap, redirecciones) | 100 | ALTA |
| Datos estructurados | 100 | ALTA |
| Arquitectura de contenido | 100 | MEDIA |
| Rendimiento (entrega) | 85 | ALTA |
| Core Web Vitals | 90 | ALTA |
| Preparación para IA / búsqueda agéntica | 70 | ALTA |
| **SEO externo (contraste)** | **45** | ALTA |

La última fila no es un error tipográfico: mide algo distinto (visibilidad conseguida, no salud del sitio) y es la razón por la que existen los otros tres informes de esta carpeta.

---

## 2. Indexabilidad y rastreo

| Control | Estado | Evidencia |
|---|---|---|
| `robots.txt` abierto y con referencia a sitemap | PASS | EVID-0006 |
| `sitemap.xml` = 13 URLs, coincide exactamente con las páginas desplegadas | PASS | EVID-0006 |
| Canónicas presentes y coherentes con la política **sin barra final** | PASS | EVID-0007 |
| Redirecciones 301 que imponen la política canónica | PASS | EVID-0008 |
| Titles y meta descriptions únicos en todas las páginas | PASS | EVID-0013 |
| Enlaces internos rotos | 0 | EVID-0015 |
| `hreflang` | N/A — sitio solo en español (EN desactivado) | — |
| Acceso de crawlers de IA (8 probados) | PASS — sin bloqueo ni cloaking | ASO |

**Ocho crawlers verificados sin bloqueo:** GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot, Googlebot, Bingbot. Coste de contexto por página ~400–1 600 tokens. Cero patrones de inyección de prompts. Lighthouse puntúa **Navegación agéntica 3/3** en móvil y escritorio.

---

## 3. Higiene del índice — el hallazgo técnico real (SEO-EXT-002)

**Severidad: MEDIA · Estado: corregido en fuente y desplegado (v1.0.14); pendiente de que Google recorra.**

El dominio alojó WordPress antes del estático actual, y el reemplazo se publicó sin un mapa de redirecciones para las URLs antiguas. Consecuencia observada:

| URL residual | Estado antes de la corrección |
|---|---|
| `https://www.caminodeldharma.org/prueba/` | 301 → **404** — página literalmente titulada «prueba», aparecía en el SERP de marca en DuckDuckGo |
| `caminodeldharma.org/category/noticias/` | 301 → **404** |
| `caminodeldharma.org/category/sin-categoria/` | 301 → **404** |
| `caminodeldharma.org/?page_id=10` | 200 duplicado de la portada |

**Corrección aplicada en `.htaccess`:** `410` para `/prueba`; `301 category/*` → `/blog`; `301 ?page_id=10` → `/comunidad`; `301 ?page_id=*` → `/`.

**Matiz relevante:** verificado en Google Colombia (hl=es, gl=co) el 2026-07-20, el SERP de marca **ya estaba limpio** y `site:` no mostraba residuos en primer plano. La contaminación se observó en índices secundarios (DuckDuckGo). La corrección sigue siendo correcta, pero su urgencia era menor de lo que sugería la primera medición.

### Variantes de host y protocolo — vigilar

Search Console contabiliza por separado tres formas de la portada:

| URL | Clics | Posición media |
|---|---:|---:|
| `https://caminodeldharma.org/` | 7 | 3,00 |
| `https://www.caminodeldharma.org/` | 1 | 8,33 |
| `http://caminodeldharma.org/` | 1 | 1,00 |

Las tres redirigen correctamente (301 verificado). Google aún no ha consolidado las señales en la canónica, lo cual **es normal en un sitio de dos días**. **Acción:** si a las 4–8 semanas siguen apareciendo separadas, investigar la consolidación.

---

## 4. Rendimiento y Core Web Vitals

Fuente: PageSpeed Insights / Lighthouse 13.4.0, 2026-07-20 (EVID-0047 móvil, EVID-0048 escritorio).

| Métrica | Móvil (4G lenta) | Escritorio | Umbral |
|---|---:|---:|---|
| Rendimiento | **99** | **100** | — |
| Accesibilidad · Prácticas · SEO | 100 · 100 · 100 | 100 · 100 · 100 | — |
| Navegación agéntica | 3/3 | 3/3 | — |
| LCP | **1,4 s** | 0,4 s | < 2,5 s ✅ |
| FCP | 0,9 s | 0,3 s | < 1,8 s ✅ |
| TBT | 0 ms | 0 ms | < 200 ms ✅ |
| CLS | **0,081** | 0,005 | < 0,1 ✅ |
| Speed Index | 1,0 s | 0,3 s | — |
| **INP** | **no disponible** | no disponible | requiere datos de campo |

**Todas las métricas medibles están en verde.** Dos observaciones que conviene no perder:

1. **El CLS es un fenómeno exclusivo de móvil** (0,081 frente a 0,005: 16×). Sigue en verde, pero cualquier cambio futuro en la portada podría empujarlo por encima de 0,1. Vigilar tras cada cambio de maquetación en la home.
2. **CrUX no tiene datos para este origen** («No hay datos»). El sitio no alcanza el umbral de tráfico del dataset de campo de Google. Esto confirma por una vía completamente distinta el diagnóstico de visibilidad del informe 03 — y significa que **INP no podrá medirse** hasta que haya más tráfico o se instrumente RUM.

### Oportunidades abiertas (ninguna urgente)

| Oportunidad | Ahorro móvil | Ahorro escritorio | Estado |
|---|---|---|---|
| Entrega de imágenes (falta `srcset`) | **185 KiB** | 42 KiB | PERF-001, abierto |
| Solicitudes que bloquean el renderizado | 450 ms | 200 ms | detectado por PSI |
| Minificar CSS | 3 KiB | 3 KiB | menor |
| Versionado de assets / caché | 1 KiB | 1 KiB | PERF-002, abierto |

La proporción 185 KiB móvil frente a 42 KiB escritorio (4,4×) es exactamente la firma de la falta de `srcset`: al móvil se le entregan imágenes dimensionadas para pantallas grandes. El caso más claro es el **logo de 1000 px (46 KB servidos) renderizado en un hueco de 44 px**.

> **Nota sobre una cifra que parece contradictoria.** La auditoría original puntuó Rendimiento 67 y PSI da 99. No se contradicen: el 67 medía **controles de higiene** que siguen fallando (dimensionado de imágenes, versionado de assets) y el 99 mide la **experiencia entregada**, que es excelente. El scorecard reconcilia ambas lecturas en 85.

---

## 5. Datos estructurados

Validación local completa: **100/100**.

- `Organization` en portada, con `foundingDate` 2012, `foundingLocation` Cali y `knowsAbout` (Chan, Tierra Pura, meditación, atención plena).
- `Event` en eventos y `BlogPosting` en el blog: correctos.
- `llms.txt` presente y servido.

**Una decisión deliberada que conviene registrar:** se **eliminó** el `alternateName` «budismo en Colombia» del JSON-LD. No era un nombre real de la organización sino una palabra clave, y declarar keywords como nombres alternativos es marcado estructurado engañoso — riesgo de acción manual sobre un dominio que ya arrastra DA 2. En su lugar se optó por señales verificables: fundación en Cali en 2012, mencionada además en texto visible en portada y `/comunidad`.

Del mismo modo se **descartó** cubrir la grafía «buddhista» mediante texto invisible o `aria-label` con palabras clave: es spam según las Search Essentials, los atributos ARIA no son señal de ranking (**no funcionaría**) y degradaría la experiencia de lectores de pantalla. La solución adoptada fue contenido visible: la nota editorial «Sobre la palabra Buddhismo» en `/linaje`.

---

## 6. Hallazgos técnicos que afectan al usuario (no al ranking)

Se incluyen porque una auditoría técnica de salud los cubre, aunque su impacto SEO sea nulo:

| ID | Severidad | Hallazgo | Estado |
|---|---|---|---|
| FUNC-001 | ALTA | Formulario de contacto con `action="#"` y sin backend: los mensajes se pierden en silencio | Mitigado con CTAs de WhatsApp/correo (TASK-0002). Fix duradero **BLOQUEADO** por decisión de la comunidad (TASK-0003) |
| FUNC-002 | MEDIA | Descarga `.ics` con ruta relativa que rompe bajo URLs sin barra final | **Corregido** (TASK-0001, v1.0.14) |
| SEC-001 | MEDIA | HSTS no activo | **Aplazado deliberadamente** — ADR 0020, hasta después del corte a WordPress |
| SEC-002 | MEDIA | CSP solo con `upgrade-insecure-requests` | Abierto |
| PERF-001 | MEDIA | Imágenes sobredimensionadas, sin `srcset` | Abierto |
| PERF-002 | BAJA | CSS/JS con caché de 7 días sin versionado | Abierto |
| A11Y | BAJA | Galería invisible sin JavaScript, sin `noscript` | Abierto |
| PRIV-001 | BAJA | 10 embeds de vídeo sin variante `nocookie`; política de privacidad | Abierto |

> Sobre HSTS: es **un criterio de seguridad de transporte entre muchos**, no el objetivo de la auditoría. La decisión vigente (ADR 0018 / 0020) es el despliegue escalonado — Fase 1 con `max-age=604800` (7 días) y el año completo **solo** tras un corte a WordPress estable. No se recomienda el pin de un año mientras el sitio sea temporal.

---

## 7. Limitaciones de esta auditoría técnica

1. **INP no medible**: CrUX no tiene datos para este origen. Solo se cerrará con más tráfico o instrumentando RUM.
2. **Datos PSI son de laboratorio**, no de campo (móvil: Moto G Power emulado con 4G lenta). No sustituyen la experiencia real.
3. **Sin pase con tecnología de asistencia**: la accesibilidad se evaluó por semántica ARIA y revisión de código, no con un lector de pantalla real.
4. **Muestreo de contraste**: calculado para selectores representativos en dos plantillas, no exhaustivamente.
5. **Vista DNS parcial**: el resolutor local agotó el tiempo en AAAA/NS/MX; la enumeración de subdominios no fue posible (bloquea `includeSubDomains` por diseño).
6. **Una sola geografía/red**: comportamiento del CDN en otras ubicaciones no verificado.
7. **Formularios no enviados** (`form_submission_permission: false`): FUNC-001 se confirma por código fuente y ausencia verificada de manejador, no por un envío real.

---

## 8. Acciones técnicas recomendadas

| Prioridad | Acción | Esfuerzo | Estado |
|---|---|---|---|
| 1 | Añadir `srcset` y redimensionar logo y miniaturas (PERF-001) | ~2 h | Abierto |
| 2 | Versionar CSS/JS para permitir caché larga sin servir stale (PERF-002) | ~1 h | Abierto |
| 3 | Ampliar la CSP más allá de `upgrade-insecure-requests` (SEC-002) | ~2 h | Abierto |
| 4 | Publicar `/.well-known/security.txt` | 15 min | Abierto |
| 5 | Fallback `noscript` para la galería | ~1 h | Abierto |
| 6 | Cambiar los 10 embeds a variantes `nocookie` | ~30 min | Abierto |
| 7 | Verificar consolidación de host/protocolo en GSC | 15 min | **A las 4–8 semanas** |
| — | HSTS Fase 1 (`max-age=604800`) | 30 min | **Aplazado** — ADR 0020, post-WordPress |

---

## Fuentes

`.audit/report.md` §6–10 · `.audit/working/seo.md` · `.audit/working/performance.md` · `.audit/working/seo-external.md` §2 · `.audit/working/aso-aeo.md` · `.audit/findings.jsonl` (SEO-EXT-002, PERF-001/002, SEC-001/002, FUNC-001/002) · `.audit/limitations.md` · EVID-0006/0007/0008/0013/0015/0032/0034/0047/0048/0052 · ADR 0018/0019/0020
