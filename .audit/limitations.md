# Limitations

1. **No calibrated lab performance tooling.** Lighthouse/axe are not installed and installing packages is prohibited during the audit. Lab metrics come from the in-app browser Performance API without CPU/network throttling; paint/LCP entries were suppressed on warm runs (pane throttling). Effect: Core Web Vitals cannot be scored; CLS (0) and TTFB (170–310 ms) are reliable, FCP/LCP are not. Severity of PERF findings is therefore based on byte/dimension evidence, not on timing claims.
2. **No field data.** No Search Console, GA4 account access, RUM or server logs were provided. INP and field LCP are unknown. Observability controls are NOT_VERIFIED.
3. **Form submission prohibited** (`form_submission_permission: false`). FUNC-001 is confirmed from source code plus verified absence of any submit handler, not from a live submission.
4. **CT log history unavailable.** crt.sh returned 502 twice (EVID-0030). Certificate renewal reliability is inferred from the platform-managed, freshly issued certificate; recorded as residual risk in the HSTS decision.
5. **Partial DNS view.** Local resolver timed out on AAAA/NS/MX (EVID-0003). Subdomain enumeration was not possible; this blocks `includeSubDomains` approval (by design) but does not affect the host-only decision.
6. **No assistive-technology pass.** Screen-reader behavior was assessed from ARIA semantics and code review, not with an actual AT. A11Y-08 is NOT_VERIFIED; accessibility score reflects structural checks only.
7. **Contrast sampling.** Contrast was computed for representative selectors on two templates, not exhaustively for every element/state pair.
8. **Single geography/network.** All measurements from one location; CDN behavior elsewhere not verified.
9. **Legal scope.** Privacy findings (PRIV-001) describe observed behavior; legal compliance conclusions require counsel and are not asserted.

## Añadidas por la continuación (2026-07-19, SEO externo)
9b. **Limitación 1 (LCP) RESUELTA 2026-07-20** por PSI/Lighthouse 13.4.0: LCP 1,4 s en laboratorio (EVID-0047). **Limitación 2 (INP) SIGUE ABIERTA**: requiere datos de campo y CrUX no tiene datos para este origen (tráfico insuficiente) — solo se cerrará con más tráfico o instrumentando RUM.

10. ~~Posiciones de Google medidas por aproximación~~ **RESUELTA 2026-07-20:** Google (hl=es, gl=co) verificado por navegación real del panel; 7 consultas archivadas (EVID-0037, raw/seo-external/google-co-serps-2026-07-20.md). Persiste: sin datos de impresiones/clics hasta GSC (TASK-0015) y Bing no medible vía curl.
11. DuckDuckGo bloqueó por anti-bot las consultas temáticas (páginas "anomaly" guardadas en raw/seo-external/); solo `site:` y marca devolvieron resultados.
12. Sin acceso a Search Console/Bing Webmaster ni a herramientas de backlinks (Ahrefs/Semrush): el perfil de enlaces se evaluó por inspección directa de las páginas que citan la comunidad (Buddhistdoor, budismo.com).

## Añadidas por la continuación (2026-07-20, autoridad y enlaces)
13. Trust Flow / Citation Flow / ratio TF-CF (Majestic) y Authority Score nativo de Semrush no obtenidos: requieren registro. No extrapolados.
14. ~~Baseline de autoridad de competidores no obtenido~~ **RESUELTA 2026-07-20:** entregado por ejecución manual del propietario (EVID-0046, 5 CSV, misma herramienta y fecha; ver authority-backlinks.md §5). Contexto original: Dapachecker exige CAPTCHA (no resoluble por el agente por política) y SEO Review Tools limitó al agente tras la primera consulta. DR 0,4 se interpreta contra referencias generales, no contra los rivales concretos → TASK-0019 (ejecución manual).
15. Las métricas DA/PA/DR/UR son estimaciones de terceros sobre grafos de enlaces propietarios, no mediciones directas; varían entre proveedores (DA 6 Moz vs DA 2 Semrush). Se reportan con proveedor, escala y fecha. Para comparar en el tiempo debe usarse siempre la MISMA herramienta.
16. Los comprobadores de "PageRank" (dnschecker.org/pagerank.php, smallseotools, prchecker.info) se descartaron deliberadamente como fuente: Google no publica PageRank desde 2016 y sus cifras no son auditables. prchecker.info además exige CAPTCHA.

17. Métrica 'Social shares' de SEO Review Tools inservible: devuelve 0 en los seis dominios medidos, incluidos los líderes del sector. La herramienta no recolecta el dato (restricciones de la API de Facebook). Descartada como evidencia, no interpretada.

18. Los datos de PSI son de **laboratorio** (móvil: Moto G Power emulado con 4G lenta; escritorio: emulado), no de campo. CrUX no dispone de datos para este origen. Las métricas de laboratorio no sustituyen la experiencia real de usuarios; INP en particular no es medible en laboratorio.
