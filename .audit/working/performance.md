# Performance working notes
Home cold: 18 req, 602KB, 3 third-party (EVID-0019). CLS 0, no long tasks. Brotli on. Caching per class per .htaccess (EVID-0027). Findings: PERF-001 oversized logo(46KB@44px)/gallery thumbs, no srcset; PERF-002 no cache busting on 7-day CSS/JS. LCP/INP NOT_VERIFIED (limitations 1-2).

---

## PageSpeed Insights / Lighthouse — móvil (2026-07-20, EVID-0047)

Informe compartido por el propietario: `pagespeed.web.dev/analysis/https-caminodeldharma-org/3dw4qkkdhr`
(Lighthouse 13.4.0, Moto G Power emulado, limitación 4G lenta, HeadlessChromium 149).
**Cierra la mayor limitación de la auditoría original**, que no pudo medir LCP por el throttling del panel.

### Puntuaciones
| Categoría | Score |
|---|---:|
| Rendimiento | **99** |
| Accesibilidad | **100** |
| Prácticas recomendadas | **100** |
| SEO | **100** |
| **Navegación agéntica** (categoría nueva de Lighthouse) | **3/3** |

### Métricas de laboratorio
| Métrica | Valor | Estado |
|---|---:|---|
| First Contentful Paint | 0,9 s | bueno |
| **Largest Contentful Paint** | **1,4 s** | **bueno** (<2,5 s) — antes NOT_VERIFIED |
| Total Blocking Time | 0 ms | bueno |
| **Cumulative Layout Shift** | **0,081** | bueno (<0,1) — **corrige el 0 registrado** |
| Speed Index | 1,0 s | bueno |
| INP | — | sigue NO disponible (requiere datos de campo) |

### Datos de campo (CrUX)
**"Descubre lo que experimentan tus usuarios reales → No hay datos".** El origen no alcanza el umbral
de tráfico del dataset CrUX. Es coherente con la visibilidad orgánica medida (DA 2, ausente de
consultas amplias): **corrobora de forma independiente el diagnóstico de SEO externo**. INP seguirá
sin poder medirse hasta que haya tráfico suficiente o se instrumente RUM.

### Oportunidades detectadas (nuevas o corroborantes)
| Oportunidad | Ahorro estimado | Relación |
|---|---|---|
| Mejorar la entrega de imágenes | **185 KiB** | **Corrobora PERF-001** (logo y miniaturas sobredimensionados) de forma independiente |
| Solicitudes que bloquean el renderizado | 450 ms | **NUEVO** — no detectado por la auditoría original |
| Minificar CSS | 3 KiB | **NUEVO** — menor |
| Tiempos de vida de caché eficientes | 1 KiB | Relacionado con PERF-002 (versionado) |

### Señales de "Prácticas recomendadas" (marcadas pese al score 100)
Lighthouse señala como pendientes: **CSP efectiva frente a XSS** (corrobora SEC-002), **política HSTS
sólida** (corrobora SEC-001), aislamiento COOP y Trusted Types. Confirmación externa de dos hallazgos
de seguridad ya registrados; COOP y Trusted Types no se elevan a hallazgo (superficie mínima en un
estático sin autenticación ni datos de usuario).

### Comparativa móvil vs escritorio (mismo informe, 2026-07-20)

| Métrica | Móvil (4G lenta) | Escritorio | Observación |
|---|---:|---:|---|
| Rendimiento | 99 | **100** | ambos excelentes |
| Accesibilidad / Prácticas / SEO | 100 / 100 / 100 | 100 / 100 / 100 | idénticos |
| Navegación agéntica | 3/3 | 3/3 | idénticos |
| FCP | 0,9 s | 0,3 s | — |
| **LCP** | **1,4 s** | **0,4 s** | ambos "bueno"; el móvil es el caso límite real |
| TBT | 0 ms | 0 ms | — |
| **CLS** | **0,081** | **0,005** | **el desplazamiento es específico de móvil** (16× mayor) |
| Speed Index | 1,0 s | 0,3 s | — |

| Oportunidad | Móvil | Escritorio | Lectura |
|---|---:|---:|---|
| Entrega de imágenes | **185 KiB** | 42 KiB | 4,4× más en móvil → confirma que falta `srcset` (PERF-001): el móvil recibe imágenes dimensionadas para pantallas grandes |
| Bloqueo de renderizado | 450 ms | 200 ms | proporcional al throttling |
| Minificar CSS | 3 KiB | 3 KiB | idéntico (no depende del dispositivo) |
| Caché | 1 KiB | 1 KiB | idéntico |

**Hallazgo de la comparativa:** el CLS de 0,081 en móvil frente a 0,005 en escritorio indica un
desplazamiento de diseño **exclusivo del viewport móvil**. Sigue dentro del umbral bueno (<0,1), por
lo que no se eleva a hallazgo, pero desmiente el "CLS 0 en ambos perfiles" de la auditoría original
y merece vigilancia: cualquier cambio futuro en la portada podría empujarlo por encima de 0,1.
Nota metodológica: la auditoría original midió CLS con `PerformanceObserver` **sin throttling**, que
es precisamente la condición en la que un desplazamiento tardío no llega a registrarse.

### Lectura
El rendimiento **entregado** al usuario es excelente en ambos perfiles (**99** móvil / **100** escritorio, todas las métricas en verde). El score
67 de la auditoría original reflejaba controles de higiene fallidos (dimensionado de imágenes,
versionado de assets), no una experiencia lenta: ambas cosas son ciertas y se reconcilian en el
scorecard. La categoría **Navegación agéntica 3/3** corrobora de forma independiente la conclusión
de la auditoría ASO/AEO: la capa técnica agéntica está bien; la brecha es de citación y entidades,
no de acceso ni de parsabilidad.
