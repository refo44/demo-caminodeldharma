# Auditoría web integral — Camino del Dharma

> **CORRECCIÓN 2026-07-21 — GBP descartado.** Todo lo que este documento afirma sobre Google Business Profile como acción prioritaria queda **retirado**: la comunidad no tiene sede ni dirección física y no es elegible según las directrices de Google (las entidades exclusivamente en línea quedan excluidas; se exige dirección real verificable aunque se oculte al público). Las consultas locales dejan de contabilizarse como brecha alcanzable. El texto original se conserva sin modificar como registro del análisis del 19–20 de julio. Ver `decisions.md`.

> **CORRECCIÓN 2026-07-21 — directorio budismo.com retirado.** Su autoridad nunca se midió, el sitio no muestra mantenimiento desde 2010 y carece de formulario de alta. Se recomendó con un estándar de evidencia inferior al aplicado al resto del análisis. Retirado de las recomendaciones; el texto original se conserva como registro.


## 1. Metadatos de la auditoría

| Campo | Valor |
|---|---|
| Sitio | https://caminodeldharma.org (producción) |
| Fuente | `DOCS/demo-caminodeldharma` @ `be896db2214c4dafdc8adad89f8496421c8b6071` (main, limpio, coincide con `expected_commit`) |
| Fecha | 2026-07-19 |
| Config | `configs/demo-caminodeldharma.yaml` |
| Alcance | 13 URLs indexables + 404 + puntos de entrada HTTP/HTTPS — **cobertura de sitio completo** (≪ max_urls 500) |
| Nivel de seguridad | Pasivo; sin envío de formularios; sin cambios de ningún tipo |
| Modo de remediación | Backlog de tareas atómicas para agentes externos (esta auditoría **no** implementó nada) |
| Auditor | Agente de auditoría Fable 5 (FABLE_AUDIT_AGENT.md) |

## 2. Resumen ejecutivo

> **Revisión 2026-07-19 (continuación):** HSTS era la decisión que este informe destacaba en cabecera; queda reposicionado como **un criterio de seguridad de transporte más**, no el objetivo de la auditoría. Además, el sitio estático auditado es **temporal** (migración a WordPress planificada). *(Actualización 2026-07-20: la decisión evolucionó de «escalonado» a **aplazado por completo** — ver ADR 0020 y §8.)*

**HSTS (criterio de transporte):** los 14 controles de la fase de decisión pasaron con evidencia reproducible (redirecciones directas, certificado válido para apex y www, TLS 1.3, cero contenido mixto, configuración fuente = producción): **no hay bloqueadores técnicos**. Sin embargo, la decisión vigente es **APLAZAR la activación** hasta después del corte a WordPress (**ADR 0020**, que sustituye operativamente a ADR 0018). Motivo: el sitio tiene 2 días, registra ~9 clics en 28 días (EVID-0052) y la migración inminente puede alterar TLS y redirects — el riesgo operativo de fijar política ahora supera al que HSTS mitiga a este volumen. La cabecera nunca llegó a servirse. `includeSubDomains` y `preload` siguen **RECHAZADOS**.

El sitio es un estático pequeño, muy bien construido: higiene SEO **interna** impecable (títulos, canónicas, sitemap, datos estructurados completos), accesibilidad estructural sólida, despliegue idéntico al commit auditado y superficie de exposición mínima. La continuación añadió la dimensión que faltaba — **SEO externo** — verificada finalmente en Google CO real: nicho ya ganado (#1 en "budismo chan colombia" y "budismo tierra pura colombia") pero ausente de la página 1 en consultas amplias y locales, dominadas por packs locales de Google Business Profile (SEO-EXT-001/002, §8). Los problemas materiales son concretos:

1. **FUNC-001 (ALTA):** el formulario de contacto no entrega mensajes (action="#", sin backend ni handler): pérdida silenciosa del journey principal de conversión.
2. **FUNC-002 (MEDIA, causa raíz corregida):** la descarga `.ics` devuelve 404 en el evento vigente — no por falta del archivo (existe y responde 200), sino porque la ruta **relativa** resuelve mal bajo URLs canónicas sin barra final.
3. **MEDIAS:** HSTS **aplazado por decisión** hasta después del corte a WordPress (ADR 0020), CSP mínima, e imágenes sobredimensionadas (logo de 1000 px y 46 KB servidos —36 KB en repo— para un hueco de 44 px). La privacidad dejó de ser MEDIA: **ADR 0019** descarta la analítica con cookies de forma definitiva y producción no sirve ninguna cookie propia.

4. **Autoridad de dominio nula (SEO-EXT-001, cuantificado):** DR 0,4 / DA 6 sobre un dominio de 7 años y 5 meses, con Spam Score sano (7 %) — ausencia de enlaces, no toxicidad.

Nada de esto exige contención urgente; todo está convertido en 20 tareas atómicas (14 READY, 4 BLOCKED por decisiones humanas, 1 completada, 1 implementada a la espera de validación) en `implementation/`.

## 3. Índice

1. Metadatos 2. Resumen ejecutivo 3. Índice 4. Alcance y metodología 5. Herramientas 6. Panel de riesgos 7. Métricas clave 8. Hallazgos por categoría 9. Matriz de evidencia 10. Scorecard 11. Roadmap priorizado 12. Diseños de remediación 13. Backlog incremental 14. Olas y grafo de dependencias 15. Checklist de validación 16. Limitaciones 17. Veredicto final

## 4. Alcance y metodología

Auditoría de solo lectura sobre producción y código fuente. Descubrimiento por robots/sitemap/navegación/árbol fuente; los 13 URLs del sitemap se probaron todos (sin muestreo). Baselines con la API de Performance del navegador integrado (3 runs desktop + móvil, ver limitaciones), inspección pasiva de cabeceras/TLS con curl y openssl, revisión completa de los 4 módulos JS, validación local de JSON-LD, verificación de integridad de enlaces por script, y comparación byte a byte de producción contra el commit fuente (14/14 idénticas). Los formularios se inspeccionaron sin enviarse. Perfiles: 1440×900, 390×844 y 360×800.

## 5. Herramientas

Ver `tools.md`. Relevante: Lighthouse/axe no disponibles durante la auditoría original (instalación prohibida) → **subsanado el 2026-07-20** con un informe PSI/Lighthouse 13.4.0 aportado por el propietario (EVID-0047), que cierra la medición de LCP; INP sigue sin verificar por ausencia de datos de campo; crt.sh caído (502) → historial de renovación de certificado no verificado.

## 6. Panel de riesgos

| Severidad | Nº | Categorías principales |
|---|---:|---|
| CRÍTICA | 0 | — |
| ALTA | 1 | Funcionalidad (formulario de contacto) |
| MEDIA | 8 | Seguridad/transporte (HSTS, CSP), Rendimiento (imágenes), SEO externo (SEO-EXT-001, SEO-EXT-002), ASO (ASO-001 consultas en lenguaje natural, ASO-002 meditación semanal sin entidad), Funcionalidad (FUNC-002 .ics — rebajado ALTA→MEDIA tras corregir causa raíz) |
| BAJA | 4 | Caché sin versionado, galería sin fallback JS, security.txt, privacidad (embeds + política — rebajado tras ADR 0019) |
| INFORMATIVA | 1 | Cadena www 2 saltos, MIME x-javascript, README de fuentes público |

## 7. Métricas clave

| Métrica | Móvil | Desktop | Fuente | Estado |
|---|---:|---:|---|---|
| TTFB | 306 ms | 170–310 ms | Performance API (lab sin throttling) | MEASURED |
| Load home (caliente) | 886 ms | 542–1412 ms | Performance API | MEASURED |
| Load home (fría) | — | 2078 ms | Performance API run 1 | MEASURED |
| CLS | **0,081** | **0,005** | PSI (EVID-0047/0048) | MEASURED — desplazamiento **específico de móvil** (16×); el 0 original se midió sin throttling |
| Long tasks | 0 | 0 | PerformanceObserver | MEASURED |
| Peso home (fría) | — | 602 KB / 18 requests (3 de terceros) | Resource Timing | MEASURED |
| **LCP** | **1,4 s** | **0,4 s** | PSI/Lighthouse 13.4.0 (EVID-0047/0048) | **MEASURED** |
| INP | — | — | — | NOT_VERIFIED (limitación 2: sin datos CrUX) |
| Compresión | br | br | curl | MEASURED |
| Protocolo | h2 (h3 anunciado) | h2 | curl/alt-svc | MEASURED |

> **Contexto imprescindible (añadido 2026-07-20):** el sitio estático actual se publicó el **2026-07-18** (`CHANGELOG` v1.0.0). Esta auditoría se ejecutó el **2026-07-19**, sobre un sitio de **un día de vida**. Los hallazgos técnicos (funcionalidad, seguridad, rendimiento, accesibilidad, código) son independientes de la antigüedad y se mantienen íntegros. Las conclusiones de **visibilidad en buscadores** deben leerse con ese matiz: la ausencia en consultas competidas es esperable a esa edad, y el hallazgo de fondo es el déficit de autoridad heredado del dominio (DR 0,4 en 7,5 años), no el rendimiento del sitio nuevo.

**Search Console (EVID-0052, export del propietario, 2026-07-20):** 9 clics y 35 impresiones en total. **Una sola consulta genera impresiones: «camino del dharma»** (marca) — 5 clics, posición media 3,35. Colombia concentra 8 de los 9 clics. Google aún cuenta por separado `https://`, `https://www.` y `http://`: los 301 son correctos y la consolidación debería llegar sola. Confirma con datos propios la localizabilidad solo por marca de SEO-EXT-001.

Datos de campo: **no disponibles** — CrUX responde "No hay datos" para este origen (tráfico por debajo del umbral del dataset), lo que **corrobora de forma independiente** el diagnóstico de visibilidad externa. INP no es medible sin ellos.

**PSI/Lighthouse 2026-07-20 (EVID-0047 móvil, EVID-0048 escritorio):**

| | Móvil (4G lenta) | Escritorio |
|---|---:|---:|
| Rendimiento | **99** | **100** |
| Accesibilidad · Prácticas · SEO | 100 · 100 · 100 | 100 · 100 · 100 |
| Navegación agéntica | 3/3 | 3/3 |
| FCP · LCP · TBT · CLS · SI | 0,9 s · 1,4 s · 0 ms · 0,081 · 1,0 s | 0,3 s · 0,4 s · 0 ms · 0,005 · 0,3 s |
| Ahorro en imágenes | **185 KiB** | 42 KiB |
| Bloqueo de renderizado | 450 ms | 200 ms |

La diferencia 185 vs 42 KiB en imágenes (4,4×) es la firma de la falta de `srcset` (**PERF-001**): el móvil recibe imágenes dimensionadas para pantallas grandes. El CLS de 0,081 en móvil frente a 0,005 en escritorio revela un desplazamiento **exclusivo del viewport móvil** — dentro del umbral bueno, pero vigilable.

## 8. Hallazgos por categoría

Los 14 hallazgos completos (10 originales + SEO-EXT-001/002 + ASO-001/002) (con reproducción, causa raíz, criterios de aceptación, pasos numerados, validación y rollback) están en `findings.jsonl`; cada uno enlaza su paquete en `remediation/` y sus tareas. Resumen:

### Funcionalidad
- **FUNC-001 · ALTA · CONFIRMADO** — Formulario de contacto inentregable. `contacto/index.html:127-147` (`action="#" method="post"`), ningún script adjunta handler (revisión completa de los 4 JS), host estático. Impacto agéntico CRÍTICO (única acción de contacto en página falla en silencio). Evidencia EVID-0024/0025. → `remediation/FUNC-001.md`, TASK-0002 (READY), TASK-0003 (BLOCKED: decisión de producto).
- **FUNC-002 · MEDIA · CONFIRMADO (causa raíz CORREGIDA 2026-07-20)** — La descarga `.ics` devuelve 404, pero **no porque el archivo falte**: `eventos/ical/encuentro-nacional-2026.ics` existe y responde 200. Las referencias son **relativas** (`ical/…`, `../ical/…`) y bajo la política canónica **sin barra final** resuelven a `/ical/…` → 404 (verificado en navegador real, EVID-0041). Solo **un** evento ofrece calendario; "Pausa Profunda – Cali" está finalizado (`EventCompleted`) y correctamente no lo ofrece. La auditoría original afirmaba que los archivos nunca se crearon y que ambos eventos estaban rotos: inexacto. Arreglo correcto = rutas absolutas + `text/calendar` (no crear archivos). → TASK-0001 (corregida).

### Seguridad y transporte
- **SEC-001 · MEDIA · APLAZADO POR EL PROPIETARIO (ADR 0020)** — HSTS ausente; la cabecera nunca se activó y repo y producción están sincronizados sin ella. El análisis técnico no encontró bloqueadores, pero se decide **no activar durante la transición**: con ~9 clics en 28 días la exposición al downgrade es mínima, mientras que fijar política justo antes de una migración que puede tocar TLS y redirects sí añade riesgo real. Se revisa tras el corte + ≥30 días estables. El hallazgo **no se cierra**: la brecha se acepta de forma consciente y acotada. → TASK-0004/0005 (BLOCKED), TASK-0012 pospuesta.
- **SEC-002 · MEDIA · CONFIRMADO** — CSP solo `upgrade-insecure-requests`: sin contención XSS. Política propuesta (inventario de terceros ya hecho: GA4, YouTube, Vimeo) con despliegue Report-Only → enforce. → TASK-0008.
- **SEC-003 · BAJA · CONFIRMADO** — Sin `/.well-known/security.txt` (SECURITY.md existe pero no se publica). → TASK-0009.
- **PRIV-001 · BAJA · PARCIALMENTE RESUELTO (revisado 2026-07-20)** — GA4 se retiró en la v1.0.12 y quedó **descartado de forma definitiva por ADR 0019**: producción no sirve ninguna cookie propia (verificado, sin `Set-Cookie`). Al desaparecer la analítica con cookies, **el consentimiento deja de ser necesario** y la tarea se desbloquea. Quedan dos elementos más acotados: los **10 embeds de vídeo** (8 YouTube + 2 Vimeo) sin variante `nocookie`, que pueden fijar cookies de terceros al reproducir; y la **política de privacidad**, todavía recomendable porque la Ley 1581/2012 cubre el tratamiento de datos personales en general, no solo cookies (conclusión legal: asesoría). → TASK-0006 (READY).

### Rendimiento
- **PERF-001 · MEDIA · CONFIRMADO** — `logo.png` 1000×1000 mostrado a 44 px (11×; 46 KB servidos, 36 KB en repo); miniaturas de galería de 1000 px a 159 px (~3× en DPR2); cero `srcset` en el sitio. El hero sí está bien dimensionado (WebP 626 px). → TASK-0007.
- **PERF-002 · BAJA · CONFIRMADO** — CSS/JS con caché de 7 días sin versionado: ventana de una semana de assets obsoletos tras cada release (el sitio itera: v1.0.11). → TASK-0011.

### AEO (Agentic Engine Optimization)
- **AEO-001 · BAJA · CONFIRMADO** — `/galeria` renderiza el grid 100 % en cliente sin fallback: vacía para agentes sin JS. Prueba de tareas agénticas: "contactar" por formulario = FALLA silenciosa (FUNC-001); "añadir evento a calendario" = parcial (2/4 opciones); enlaces WhatsApp/Google Calendar = correctos. → TASK-0010.

### SEO externo (continuación 2026-07-19 — `working/seo-external.md`)
- **SEO-EXT-001 · MEDIA · CONFIRMADO (revisado 2026-07-20 con Google CO real y con Search Console)** — Visibilidad débil en consultas amplias y locales; nicho ya ganado. **GSC confirma con datos de primera parte** que solo la marca genera impresiones (una única consulta con datos). **Matiz de edad:** el sitio tiene 2 días, por lo que la ausencia en consultas competidas es esperable; el hallazgo de fondo es la autoridad heredada (DR 0,4). Verificado en Google (hl=es, gl=co): **#1 para "budismo chan colombia" y "budismo tierra pura colombia"**; marca #1 con SERP limpio; **ausente de página 1** en "budismo en colombia", "comunidad budista colombia" y "budismo cali", donde dominan los packs locales (Google Business Profile) y competidores con más autoridad. Causas restantes: sin GBP, backlinks mínimos (una cita: ecoespiritualidad.org; Buddhistdoor enlaza solo Facebook; ausente de budismo.com), 1 entrada de blog, indexación parcial (4/13 URLs visibles en site:). Evidencia EVID-0033/0035/0036/0037. Palanca nº1: GBP (TASK-0014); medición GSC (TASK-0015); contenido (TASK-0016).
- **SEO-EXT-002 · MEDIA · CONFIRMADO** — Índice contaminado por URLs residuales de la etapa WordPress: `www./prueba/` (¡página de prueba en el SERP de marca!), `/category/*` (301→404), `?page_id=10` (200 duplicado). Evidencia EVID-0032/0034. Redirects 410/301 implementados en fuente (.htaccess) → desplegar con TASK-0013 y retirar en GSC con TASK-0015.

### ASO / AEO — visibilidad y accionabilidad agéntica (continuación 2026-07-20 — `working/aso-aeo.md`)
- **ASO-001 · MEDIA · CONFIRMADO** — El sitio gana la *keyword* pero desaparece en la **misma consulta formulada como pregunta**, que es como recuperan los motores agénticos: #1 en "budismo chan colombia" pero ausente de página 1 en "dónde practicar budismo chan en Colombia" y en "meditación budista online en español gratis". El AI Overview de Google para la consulta de marca cita EcoEspiritualidad, Wisdom Library, Wikipedia y otros — **no al sitio**, pese a ser el resultado orgánico #1. Causa: contenido como prosa institucional por tema, sin encabezados en forma de pregunta, sin respuestas directas breves ni páginas por intención. Evidencia EVID-0040. → TASK-0018.
- **ASO-002 · MEDIA · CONFIRMADO** — La **meditación semanal online de los lunes** —acción recurrente de mayor valor y mayor diferenciador frente a competidores presenciales— no es una entidad citable: sin URL propia, sin `Event`/`EventSeries`, ausente de `llms.txt` y del sitemap, sin enlace de unión directo. Un agente al que le preguntan por meditación budista online en español no tiene nada que citar. Evidencia EVID-0040/0042. → TASK-0017.
- **Verificado sin hallazgos (AEO):** acceso de crawlers de IA (GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot, Googlebot, Bingbot → 200, tamaño idéntico, sin cloaking ni WAF — EVID-0038); eficiencia de contexto (~400–1 600 tokens/página, ratio de texto 19–35 % — EVID-0039); seguridad agéntica (barrido de inyección de prompts: cero coincidencias — EVID-0042).

### Autoridad de dominio y perfil de enlaces (continuación 2026-07-20 — `working/authority-backlinks.md`)

Cuantifica la causa que SEO-EXT-001 describía cualitativamente. Métricas de terceros, con proveedor y fecha:

| Métrica | Valor | Fuente |
|---|---:|---|
| Domain Rating (Ahrefs) | **0,4** | Dapachecker (ejecución manual del propietario, 2026-07-20) |
| URL Rating (Ahrefs) | 4 | ídem |
| Domain Authority (Moz) | **6** | ídem |
| Page Authority (Moz) | 18 | ídem |
| Spam Score (Moz) | **7 %** (sano) | ídem |
| Antigüedad del dominio | **7 años 5 meses** | ídem |
| DA / PA (estimación Semrush) | 2 / 8 | SEO Review Tools (agente, 2026-07-20) |

**Baseline de competidores (2026-07-20, misma herramienta y fecha — EVID-0046):**

| Dominio | DA | PA | Enlaces → dominio |
|---|---:|---:|---:|
| sotozencolombia.org | 20 | 42 | 585 |
| budismocolombia.co | 18 | 2 | 368 |
| meditacionencolombia.org | 17 | 51 | 1 327 |
| budismocolombia.org | 8 | 1 | 214 |
| centroyamantaka.org | 8 | 16 | 496 |
| **caminodeldharma.org** | **2** | 8 | 207 |

**Lectura:** DR 0,4 sobre un dominio de 7,5 años = dominio establecido que **nunca acumuló enlaces**; no aplica el argumento de "hay que esperar". Spam Score 7 % = **no hay enlaces tóxicos**: la estrategia es adquisición, no `disavow`. **El baseline confirma el escenario favorable:** ningún competidor supera DA 20 (mediana 17) — nicho de autoridad uniformemente baja, sin actor dominante. Brecha de solo **6 puntos** al peldaño más cercano. Y el dato decisivo: `budismocolombia.org` tiene **214** enlaces externos (vs **207** aquí) pero **DA 8 vs 2** — mismo volumen, cuatro veces la autoridad: el déficit es **calidad y diversidad de dominios de referencia, no cantidad**. Metas: DA 8 (primer hito, alcanzable con TASK-0014), DA 15–17 (segundo, requiere contenido sostenido y presencia local). Causa raíz verificada en tres fuentes temáticas autorizadas que **citan a la comunidad sin enlazar el dominio**: Buddhistdoor (enlaza solo a Facebook), EcoEspiritualidad (ficha completa, #2 en Google para la marca, sin ningún enlace saliente) y el directorio budismo.com (no listada). Es una causa inusualmente fácil de corregir → TASK-0014 (tres peticiones concretas). Baseline de competidores no obtenido (CAPTCHA + rate limit) → TASK-0019. TF/CF de Majestic no obtenidos (registro) → limitación.

**Advertencia metodológica:** los comprobadores de "PageRank" (dnschecker, smallseotools, prchecker) **no se usaron como evidencia** — Google no publica PageRank desde 2016 y esas cifras no son auditables; incluirlas sería fabricar evidencia.

### Informativos (sin acción requerida)
- **INFO-001** — Cadena de 2 saltos solo en la entrada http://www (borde de plataforma, cada salto seguro); JS servido como `application/x-javascript` (legado, funcional); `assets/fonts/README.md` público (inofensivo).

### Áreas verificadas sin hallazgos
SEO técnico y de contenido, datos estructurados (Event/BlogPosting/Organization completos), arquitectura de contenido (eventos pasados correctamente etiquetados), accesibilidad estructural (skip link, landmarks, jerarquía, diálogos nativos accesibles, contraste AA en muestras, alt 100 %), responsive (sin overflow a 360/390), exposición (repo/VCS bloqueados, sin listado de directorios), llms.txt coherente, sin superficie de prompt injection detectada.

## 9. Matriz de evidencia

| Finding | Categoría | Sev. | Tipo de evidencia | Herramienta | URL/archivo | Confianza |
|---|---|---|---|---|---|---|
| FUNC-001 | Funcionalidad | ALTA | SOURCE_CODE + OBSERVED | grep + revisión JS | contacto/index.html:127-147 | ALTA |
| FUNC-002 | Funcionalidad | ALTA | OBSERVED + MEASURED | navegador + curl | /ical/*.ics (404) | ALTA |
| SEC-001 | Seguridad | MEDIA | MEASURED + CONFIGURATION | curl + .htaccess | .htaccess:103 | ALTA |
| SEC-002 | Seguridad | MEDIA | MEASURED | curl | cabecera CSP global | ALTA |
| PRIV-001 | Privacidad | MEDIA | OBSERVED | navegador (cookies) | todas las páginas | ALTA |
| PERF-001 | Rendimiento | MEDIA | MEASURED | navegador (naturalWidth) | logo.png, galeria-0X.jpg | ALTA |
| PERF-002 | Rendimiento | BAJA | MEASURED + CONFIGURATION | curl + .htaccess | assets css/js | ALTA |
| AEO-001 | AEO | BAJA | OBSERVED | navegador | /galeria | ALTA |
| SEC-003 | Seguridad | BAJA | MEASURED | curl | /.well-known/security.txt | ALTA |
| INFO-001 | Best practices | INFO | MEASURED | curl | varios | ALTA |

Detalle completo por evidencia (comando, timestamp, artefacto crudo): `evidence-ledger.jsonl` (31 registros) y `raw/`.

## 10. Scorecard final

| Área | Score | Cobertura | Confianza | Resumen |
|---|---:|---:|---|---|
| SEO (interno/on-page) | 100 | 90 % | ALTA | 8/8 controles aplicables PASS — **solo mide SEO interno** |
| SEO externo (visibilidad orgánica) | 45 | 80 % | ALTA | verificado en Google CO 2026-07-20: nicho #1 (chan, tierra pura); marca #1; ausente de página 1 en consultas amplias y locales; packs locales GBP dominan (`working/seo-external.md` §8) |
| SEO técnico | 100 | 90 % | ALTA | estados, canónicas, sitemap, redirecciones |
| Datos estructurados | 100 | 95 % | ALTA | validación local completa |
| Arquitectura de contenido | 100 | 80 % | MEDIA | eventos expirados bien gestionados |
| Rendimiento | 85 | 90 % | ALTA | entrega excelente (PSI 99/100, métricas en verde); dimensionado de imágenes y versionado siguen FAIL como higiene, + render-blocking 450 ms |
| Core Web Vitals | 90 | 70 % | ALTA | LCP 1,4 s y CLS 0,081 buenos, TBT 0 ms (PSI); INP no disponible sin datos de campo |
| Eficiencia runtime | 100 | 70 % | MEDIA | consola limpia, 0 long tasks |
| Accesibilidad | 100 | 70 % | MEDIA | estructural/contraste/teclado PASS; sin pase AT |
| Responsive | 100 | 80 % | ALTA | 360/390/1440 sin defectos |
| Seguridad | 61 | 90 % | ALTA | HSTS/CSP/consent/security.txt FAIL; TLS/exposición PASS |
| Calidad de código | 78 | 70 % | MEDIA | código limpio; 2 funciones rotas; sin tests |
| Arquitectura y mantenibilidad | 90 | 60 % | MEDIA | estático coherente; falta versionado de assets |
| Best practices | 85 | 75 % | MEDIA | observaciones menores |
| AI Search Readiness | 70 | 90 % | ALTA | acceso de crawlers IA y llms.txt PASS; pero sin citación en AI Overview, invisible en consultas tipo pregunta y meditación semanal sin entidad (ASO-001/002) |
| Descubribilidad agéntica | 100 | 75 % | ALTA | robots abierto, sitemap canónico |
| Parsabilidad agéntica | 83 | 75 % | ALTA | galería solo-JS |
| Accionabilidad agéntica | 40 | 85 % | ALTA | formulario de contacto roto (FUNC-001); calendario 2/4 opciones por ruta relativa (FUNC-002, causa corregida); WhatsApp/correo/Google/Outlook operativos |
| Seguridad agéntica | 100 | 70 % | MEDIA | sin inyección detectada |
| Observabilidad agéntica | NOT SCORED | 10 % | BAJA | sin acceso a monitorización |
| **Preparación agéntica global** | **72** | 85 % | ALTA | acceso/parsabilidad/seguridad sólidos; brechas en citación agéntica y accionabilidad |
| **Preparación para producción** | **80** | 85 % | ALTA | excelente base; 2 roturas funcionales y brecha de consentimiento |
| **Score global del sitio** | **84** | 78 % | MEDIA | media ponderada de áreas puntuadas (las NOT SCORED no promedian como cero) |

Método y pesos: `metrics/scorecard.csv` + `controls.csv` (fórmula de AUDIT_SCHEMAS §8).

## 11. Roadmap priorizado

Ver `roadmap.md`. Orden recomendado de arranque por impacto en usuarios: TASK-0001 (.ics, <30 min, elimina 404 de cara al usuario) → TASK-0002 (contacto) → TASK-0004+0005 (HSTS Fase 1 `max-age=604800`, 30 min y reversible en días — un criterio de transporte más, no el objetivo de la auditoría). Después WAVE-2/4/5/6 según `implementation/waves.md`.

## 12. Diseños de remediación

Uno por hallazgo accionable en `remediation/<FINDING_ID>.md` (índice en `remediation/README.md`): objetivo, criterios de aceptación medibles, alcance exacto, fix mínimo vs duradero, pasos numerados, cambios propuestos (todos marcados NO EJECUTADOS), validación pre y post despliegue, monitorización y rollback.

## 13. Backlog incremental de implementación

20 tareas atómicas en `implementation/tasks/` (ledger: `implementation/tasks.jsonl`; tabla: `implementation/backlog.md`). 14 READY, 4 BLOCKED (TASK-0003 decisión de formulario; TASK-0012 inventario DNS + estabilidad HSTS; TASK-0016 decisión editorial; TASK-0020 confirmación de sanghas por ciudad). TASK-0006 se desbloqueó con ADR 0019. Añadidas por las continuaciones: TASK-0013–0016 (SEO externo), TASK-0017–0018 (ASO), TASK-0019 (baseline de autoridad de competidores). Cada paquete es autocontenido: un agente externo puede ejecutarlo sin redescubrir el problema, con criterios, validación, rollback y checklist de validación independiente.

## 14. Olas de ejecución y grafo de dependencias

`implementation/waves.md` (WAVE-0 vacía — sin bloqueadores urgentes; WAVE-1 = HSTS + roturas funcionales), `implementation/dependency-graph.md` (7 aristas, sin ciclos), `implementation/conflict-map.md` (grupos CG-HTACCESS, CG-CONTACTO, CG-HTML-GLOBAL serializados).

## 15. Checklist de validación

`validation-checklist.md`: verificación por tarea + re-chequeo global post-olas (transporte HSTS, journeys de contacto y calendario, cookies, CSP, imágenes, caché).

## 16. Limitaciones

`limitations.md` (9 entradas). Las más relevantes para la confianza: sin LCP/INP (ni lab calibrado ni campo), sin pase de lector de pantalla real, historial CT no verificable, conclusiones legales fuera de alcance.

## 17. Veredicto final

**Listo para producción con reservas puntuales.** No hay bloqueadores críticos. La activación de HSTS Fase 1 (`max-age=604800`, ADR 0018) está aprobada y lista (SEC-001 → TASK-0004); el `max-age` de un año queda diferido a la etapa post-WordPress porque el sitio estático es temporal. Los mayores riesgos por dominio: UX/negocio = formulario de contacto silenciosamente roto (FUNC-001); funcionalidad = descargas .ics 404 (FUNC-002); seguridad = CSP mínima (SEC-002) y consentimiento/privacidad (PRIV-001); rendimiento = imágenes sobredimensionadas (PERF-001) con CWV no verificables (limitación); mantenibilidad = assets sin versionado (PERF-002); AI search = en verde; agéntico = accionabilidad 25/100 por FUNC-001/002. **Primeros pasos de implementación: desplegar lo ya hecho en fuente (TASK-0013, TASK-0001) y TASK-0002 (contacto).** HSTS queda aplazado por ADR 0020. Cada afirmación de este veredicto referencia hallazgos aceptados del ledger.
