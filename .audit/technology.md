# Technology inventory

| Layer | Identification | Evidence | Confidence |
|---|---|---|---|
| Site type | Hand-written static HTML/CSS/JS (no framework, no build step) | Repo tree; pages are complete HTML files; package.json has only stylelint | HIGH |
| Hosting | Hostinger shared hosting behind "hcdn" CDN | `platform: hostinger`, `panel: hpanel`, `server: hcdn` response headers (EVID-0004) | HIGH |
| Web server config | Apache/LiteSpeed via `.htaccess` (mod_rewrite, mod_expires, mod_headers) | `.htaccess` in repo; deployed behavior matches every rule (EVID-0008, EVID-0006) | HIGH |
| Protocols | HTTP/2 active, HTTP/3 advertised (`alt-svc: h3`), TLS 1.3 | curl `HTTP_VERSION:2`, alt-svc header, openssl handshake (EVID-0004, EVID-0005) | HIGH |
| TLS | Let's Encrypt cert (issuer CN=YE1), SAN: apex + www, valid 2026-07-14 → 2026-10-12 | openssl x509 (EVID-0005) | HIGH |
| DNS | Apex: A 77.37.76.240 / 92.112.198.106; www: CNAME to cdn.hstgr.net | dig (EVID-0003) | HIGH |
| Analytics | GA4 (gtag.js, ID G-B8FY69RGSS) on all 14 pages, loaded directly, no consent gate | Source grep + runtime cookies `_ga`, `_ga_B8FY69RGSS` (EVID-0012, EVID-0018) | HIGH |
| Third parties | googletagmanager.com (GA4), youtube.com/img.youtube.com (embeds), player.vimeo.com, wa.me/api.whatsapp.com (links), social links | Source grep (EVID-0012) | HIGH |
| Fonts | Self-hosted (Inter, Fjalla One, Marlowe Escapade woff2) — no third-party font CDN | assets/fonts tree; asset headers (EVID-0017, EVID-0027) | HIGH |
| JS | 4 small vanilla scripts (~22 KB total): main.js (nav/lang), gallery.js (album pagination), calendar.js (add-to-calendar dialog), share.js (share dialog) | Source review (EVID-0024) | HIGH |
| CSS | main.css (44 KB) + normalize.css (6 KB), stylelint configured | Repo; package.json | HIGH |
| Rendering model | Fully server-static; gallery grid and both dialogs are client-rendered progressive features | Source review (EVID-0023, EVID-0024) | HIGH |
| i18n | Spanish only; EN switcher button present but disabled ("Próximamente en inglés") | main.js + markup (EVID-0024) | HIGH |
| Deployment | Docroot mirror of repo at commit be896db2 (14/14 pages byte-identical); repo-only files (docs/, node_modules/, package.json…) not deployed | diff deployed vs source (EVID-0010, EVID-0016) | HIGH |
