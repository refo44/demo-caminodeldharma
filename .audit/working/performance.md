# Performance working notes
Home cold: 18 req, 602KB, 3 third-party (EVID-0019). CLS 0, no long tasks. Brotli on. Caching per class per .htaccess (EVID-0027). Findings: PERF-001 oversized logo(46KB@44px)/gallery thumbs, no srcset; PERF-002 no cache busting on 7-day CSS/JS. LCP/INP NOT_VERIFIED (limitations 1-2).
