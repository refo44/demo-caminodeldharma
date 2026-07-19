# Auditoría web integral — Camino del Dharma

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

**DECISION: ACTIVATE HSTS NOW** — la cabecera candidata `Strict-Transport-Security "max-age=31536000"` (solo host) puede activarse ya: los 14 controles de la fase de decisión HSTS pasaron con evidencia reproducible (redirecciones directas, certificado válido para apex y www, TLS 1.3, cero contenido mixto, configuración fuente = producción). `includeSubDomains` y `preload` quedan **RECHAZADOS** como decisiones separadas (ver `hsts-decision.md`).

El sitio es un estático pequeño, muy bien construido: higiene SEO impecable (títulos, canónicas, sitemap, datos estructurados completos), accesibilidad estructural sólida, despliegue idéntico al commit auditado y superficie de exposición mínima. Los problemas materiales son pocos y concretos:

1. **FUNC-001 (ALTA):** el formulario de contacto no entrega mensajes (action="#", sin backend ni handler): pérdida silenciosa del journey principal de conversión.
2. **FUNC-002 (ALTA):** las descargas de calendario `.ics` devuelven 404 en ambos eventos (los archivos `/ical/*.ics` nunca se crearon).
3. **MEDIAS:** HSTS pendiente de activación (aprobada por esta auditoría), CSP mínima, cookies GA4 sin consentimiento ni política de privacidad, imágenes sobredimensionadas (logo de 1000 px y 46 KB servidos —36 KB en repo— para un hueco de 44 px).

Nada de esto exige contención urgente; todo está convertido en 12 tareas atómicas (9 READY, 3 BLOCKED por decisiones humanas) en `implementation/`.

## 3. Índice

1. Metadatos 2. Resumen ejecutivo 3. Índice 4. Alcance y metodología 5. Herramientas 6. Panel de riesgos 7. Métricas clave 8. Hallazgos por categoría 9. Matriz de evidencia 10. Scorecard 11. Roadmap priorizado 12. Diseños de remediación 13. Backlog incremental 14. Olas y grafo de dependencias 15. Checklist de validación 16. Limitaciones 17. Veredicto final

## 4. Alcance y metodología

Auditoría de solo lectura sobre producción y código fuente. Descubrimiento por robots/sitemap/navegación/árbol fuente; los 13 URLs del sitemap se probaron todos (sin muestreo). Baselines con la API de Performance del navegador integrado (3 runs desktop + móvil, ver limitaciones), inspección pasiva de cabeceras/TLS con curl y openssl, revisión completa de los 4 módulos JS, validación local de JSON-LD, verificación de integridad de enlaces por script, y comparación byte a byte de producción contra el commit fuente (14/14 idénticas). Los formularios se inspeccionaron sin enviarse. Perfiles: 1440×900, 390×844 y 360×800.

## 5. Herramientas

Ver `tools.md`. Relevante: Lighthouse/axe no disponibles (instalación prohibida durante la auditoría); métricas de pintura del navegador embebido suprimidas por throttling del panel → LCP/INP no verificados; crt.sh caído (502) → historial de renovación de certificado no verificado.

## 6. Panel de riesgos

| Severidad | Nº | Categorías principales |
|---|---:|---|
| CRÍTICA | 0 | — |
| ALTA | 2 | Funcionalidad (formulario de contacto, descargas .ics) |
| MEDIA | 4 | Seguridad/transporte (HSTS, CSP), Privacidad (consentimiento), Rendimiento (imágenes) |
| BAJA | 3 | Caché sin versionado, galería sin fallback JS, security.txt |
| INFORMATIVA | 1 | Cadena www 2 saltos, MIME x-javascript, README de fuentes público |

## 7. Métricas clave

| Métrica | Móvil | Desktop | Fuente | Estado |
|---|---:|---:|---|---|
| TTFB | 306 ms | 170–310 ms | Performance API (lab sin throttling) | MEASURED |
| Load home (caliente) | 886 ms | 542–1412 ms | Performance API | MEASURED |
| Load home (fría) | — | 2078 ms | Performance API run 1 | MEASURED |
| CLS | 0 | 0 | PerformanceObserver | MEASURED |
| Long tasks | 0 | 0 | PerformanceObserver | MEASURED |
| Peso home (fría) | — | 602 KB / 18 requests (3 de terceros) | Resource Timing | MEASURED |
| LCP / INP | — | — | — | NOT_VERIFIED (limitaciones 1–2) |
| Compresión | br | br | curl | MEASURED |
| Protocolo | h2 (h3 anunciado) | h2 | curl/alt-svc | MEASURED |

Datos de campo: no disponibles (sin acceso a Search Console/RUM).

## 8. Hallazgos por categoría

Los 10 hallazgos completos (con reproducción, causa raíz, criterios de aceptación, pasos numerados, validación y rollback) están en `findings.jsonl`; cada uno enlaza su paquete en `remediation/` y sus tareas. Resumen:

### Funcionalidad
- **FUNC-001 · ALTA · CONFIRMADO** — Formulario de contacto inentregable. `contacto/index.html:127-147` (`action="#" method="post"`), ningún script adjunta handler (revisión completa de los 4 JS), host estático. Impacto agéntico CRÍTICO (única acción de contacto en página falla en silencio). Evidencia EVID-0024/0025. → `remediation/FUNC-001.md`, TASK-0002 (READY), TASK-0003 (BLOCKED: decisión de producto).
- **FUNC-002 · ALTA · CONFIRMADO** — `.ics` rotos: `data-calendar-ics` apunta a `/ical/encuentro-nacional-2026.ics` y `/ical/pausa-profunda-cali.ics`; ambos 404 en producción y ausentes del repo (no existe `ical/`). 2 de 4 opciones del diálogo "Añadir al calendario" fallan en ambos eventos. Evidencia EVID-0026. → `remediation/FUNC-002.md`, TASK-0001 (READY).

### Seguridad y transporte
- **SEC-001 · MEDIA · CONFIRMADO** — HSTS ausente (línea 103 de `.htaccess` comentada a la espera de esta auditoría). Decisión: **ACTIVATE HSTS NOW** con confianza alta; riesgo residual: renovación de certificado inferida (crt.sh caído), fijación de 1 año. → `remediation/SEC-001.md`, TASK-0004 + verificación TASK-0005; `includeSubDomains` en TASK-0012 (BLOCKED).
- **SEC-002 · MEDIA · CONFIRMADO** — CSP solo `upgrade-insecure-requests`: sin contención XSS. Política propuesta (inventario de terceros ya hecho: GA4, YouTube, Vimeo) con despliegue Report-Only → enforce. → TASK-0008.
- **SEC-003 · BAJA · CONFIRMADO** — Sin `/.well-known/security.txt` (SECURITY.md existe pero no se publica). → TASK-0009.
- **PRIV-001 · MEDIA · CONFIRMADO** — GA4 fija `_ga*` en el primer render sin banner ni mecanismo de consentimiento, y el sitio no tiene política de privacidad. Riesgo regulatorio (Ley 1581/2012; visitantes UE) — la conclusión legal corresponde a asesoría. Embeds de YouTube sin modo nocookie. → TASK-0006 (BLOCKED: decisión organizativa + texto de política).

### Rendimiento
- **PERF-001 · MEDIA · CONFIRMADO** — `logo.png` 1000×1000 mostrado a 44 px (11×; 46 KB servidos, 36 KB en repo); miniaturas de galería de 1000 px a 159 px (~3× en DPR2); cero `srcset` en el sitio. El hero sí está bien dimensionado (WebP 626 px). → TASK-0007.
- **PERF-002 · BAJA · CONFIRMADO** — CSS/JS con caché de 7 días sin versionado: ventana de una semana de assets obsoletos tras cada release (el sitio itera: v1.0.11). → TASK-0011.

### AEO (Agentic Engine Optimization)
- **AEO-001 · BAJA · CONFIRMADO** — `/galeria` renderiza el grid 100 % en cliente sin fallback: vacía para agentes sin JS. Prueba de tareas agénticas: "contactar" por formulario = FALLA silenciosa (FUNC-001); "añadir evento a calendario" = parcial (2/4 opciones); enlaces WhatsApp/Google Calendar = correctos. → TASK-0010.

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
| SEO | 100 | 90 % | ALTA | 8/8 controles aplicables PASS |
| SEO técnico | 100 | 90 % | ALTA | estados, canónicas, sitemap, redirecciones |
| Datos estructurados | 100 | 95 % | ALTA | validación local completa |
| Arquitectura de contenido | 100 | 80 % | MEDIA | eventos expirados bien gestionados |
| Rendimiento | 67 | 75 % | MEDIA | imágenes y versionado FAIL; resto PASS |
| Core Web Vitals | NOT SCORED | 20 % | BAJA | solo CLS fiable; LCP/INP no verificados |
| Eficiencia runtime | 100 | 70 % | MEDIA | consola limpia, 0 long tasks |
| Accesibilidad | 100 | 70 % | MEDIA | estructural/contraste/teclado PASS; sin pase AT |
| Responsive | 100 | 80 % | ALTA | 360/390/1440 sin defectos |
| Seguridad | 61 | 90 % | ALTA | HSTS/CSP/consent/security.txt FAIL; TLS/exposición PASS |
| Calidad de código | 78 | 70 % | MEDIA | código limpio; 2 funciones rotas; sin tests |
| Arquitectura y mantenibilidad | 90 | 60 % | MEDIA | estático coherente; falta versionado de assets |
| Best practices | 85 | 75 % | MEDIA | observaciones menores |
| AI Search Readiness | 100 | 80 % | ALTA | llms.txt coherente; entidades consistentes |
| Descubribilidad agéntica | 100 | 75 % | ALTA | robots abierto, sitemap canónico |
| Parsabilidad agéntica | 83 | 75 % | ALTA | galería solo-JS |
| Accionabilidad agéntica | 25 | 80 % | ALTA | las 2 acciones ejecutables están rotas |
| Seguridad agéntica | 100 | 70 % | MEDIA | sin inyección detectada |
| Observabilidad agéntica | NOT SCORED | 10 % | BAJA | sin acceso a monitorización |
| **Preparación agéntica global** | **77** | 70 % | MEDIA | ponderado de las áreas AEO puntuadas |
| **Preparación para producción** | **80** | 85 % | ALTA | excelente base; 2 roturas funcionales y brecha de consentimiento |
| **Score global del sitio** | **84** | 78 % | MEDIA | media ponderada de áreas puntuadas (las NOT SCORED no promedian como cero) |

Método y pesos: `metrics/scorecard.csv` + `controls.csv` (fórmula de AUDIT_SCHEMAS §8).

## 11. Roadmap priorizado

Ver `roadmap.md`. Orden recomendado de arranque: TASK-0004+0005 (HSTS, objetivo de la auditoría, 30 min y reversible) → TASK-0001 (.ics, <30 min, elimina 404 de cara al usuario) → TASK-0002 (contacto). Después WAVE-2/4/5/6 según `implementation/waves.md`.

## 12. Diseños de remediación

Uno por hallazgo accionable en `remediation/<FINDING_ID>.md` (índice en `remediation/README.md`): objetivo, criterios de aceptación medibles, alcance exacto, fix mínimo vs duradero, pasos numerados, cambios propuestos (todos marcados NO EJECUTADOS), validación pre y post despliegue, monitorización y rollback.

## 13. Backlog incremental de implementación

12 tareas atómicas en `implementation/tasks/` (ledger: `implementation/tasks.jsonl`; tabla: `implementation/backlog.md`). 9 READY, 3 BLOCKED (TASK-0003 decisión de formulario; TASK-0006 decisión de consentimiento; TASK-0012 inventario DNS + estabilidad HSTS). Cada paquete es autocontenido: un agente externo puede ejecutarlo sin redescubrir el problema, con criterios, validación, rollback y checklist de validación independiente.

## 14. Olas de ejecución y grafo de dependencias

`implementation/waves.md` (WAVE-0 vacía — sin bloqueadores urgentes; WAVE-1 = HSTS + roturas funcionales), `implementation/dependency-graph.md` (7 aristas, sin ciclos), `implementation/conflict-map.md` (grupos CG-HTACCESS, CG-CONTACTO, CG-HTML-GLOBAL serializados).

## 15. Checklist de validación

`validation-checklist.md`: verificación por tarea + re-chequeo global post-olas (transporte HSTS, journeys de contacto y calendario, cookies, CSP, imágenes, caché).

## 16. Limitaciones

`limitations.md` (9 entradas). Las más relevantes para la confianza: sin LCP/INP (ni lab calibrado ni campo), sin pase de lector de pantalla real, historial CT no verificable, conclusiones legales fuera de alcance.

## 17. Veredicto final

**Listo para producción con reservas puntuales.** No hay bloqueadores críticos. La activación de HSTS está aprobada y lista (SEC-001 → TASK-0004). Los mayores riesgos por dominio: UX/negocio = formulario de contacto silenciosamente roto (FUNC-001); funcionalidad = descargas .ics 404 (FUNC-002); seguridad = CSP mínima (SEC-002) y consentimiento/privacidad (PRIV-001); rendimiento = imágenes sobredimensionadas (PERF-001) con CWV no verificables (limitación); mantenibilidad = assets sin versionado (PERF-002); AI search = en verde; agéntico = accionabilidad 25/100 por FUNC-001/002. **Primer paso de implementación: TASK-0004 (activar HSTS) seguido de TASK-0005 (verificación independiente).** Cada afirmación de este veredicto referencia hallazgos aceptados del ledger.
