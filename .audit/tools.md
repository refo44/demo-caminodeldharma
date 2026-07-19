# Tool availability

| Tool or capability | Status | Version | Verification method | Intended use | Limitations |
|---|---|---|---|---|---|
| curl | AVAILABLE | 8.11.1 (h2, h3 n/a, brotli) | `curl --version` executed | Headers, redirects, status classes, caching | No HTTP/3 probing verified |
| openssl s_client/x509 | AVAILABLE | OpenSSL 3.6.3 | `openssl version` + live handshake | TLS protocol, certificate, SAN inspection | No full cipher-suite enumeration performed |
| dig | AVAILABLE_WITH_LIMITATIONS | DiG 9.10.6 | executed | DNS A/CNAME records | AAAA/NS/MX queries timed out on local resolver; partial DNS view |
| python3 | AVAILABLE | 3.14.6 | executed | HTML parsing, link integrity, JSON-LD validation | Regex-based HTML parsing, not a full DOM |
| node / npx | AVAILABLE | v20.17.0 / 10.8.2 | executed | Not needed beyond verification | Lighthouse not installed; not used (would install packages) |
| jq | AVAILABLE | 1.7.1 | executed | JSON processing | — |
| git | AVAILABLE | 2.39.5 | executed | Commit/branch capture | — |
| In-app browser (Claude Browser) | AVAILABLE_WITH_LIMITATIONS | Chromium-based pane | Live navigation, JS execution, screenshots | Runtime, console, cookies, perf timing, a11y, responsive | Paint/LCP PerformanceObserver entries suppressed on most runs (pane throttling); no CPU/network throttling; no calibrated Lighthouse |
| Lighthouse / axe-core | UNAVAILABLE | — | not installed; install prohibited during audit | — | Automated a11y/CWV scoring replaced by manual + scripted checks |
| crt.sh CT log API | UNAVAILABLE | — | HTTP 502 at audit time (raw/security/hsts/ct-log-raw.json) | Certificate renewal history | Renewal reliability inferred, not verified |
| Search Console / RUM / server logs | UNAVAILABLE | — | no account access provided | Field data, crawl stats | No field CWV (INP/LCP) data; origin observability unknown |
