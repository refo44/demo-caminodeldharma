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
10. Posiciones de Google medidas por aproximación (herramienta de búsqueda real con índice tipo Google/US + DuckDuckGo región co-es); Google.com.co directo no navegable en esta sesión (navegación del panel denegada) y Bing no medible vía curl (shell JS). Los datos exactos por país requieren Google Search Console (TASK-0015).
11. DuckDuckGo bloqueó por anti-bot las consultas temáticas (páginas "anomaly" guardadas en raw/seo-external/); solo `site:` y marca devolvieron resultados.
12. Sin acceso a Search Console/Bing Webmaster ni a herramientas de backlinks (Ahrefs/Semrush): el perfil de enlaces se evaluó por inspección directa de las páginas que citan la comunidad (Buddhistdoor, budismo.com).
