# Page types and coverage

Site size: 13 indexable URLs + 404 page. Below `max_urls` (500) → **full-site coverage**, not sampling.

| Template | Pages | Tested |
|---|---|---|
| Home | / | yes (deep: perf, a11y, runtime, images) |
| Institutional section | /comunidad, /linaje | yes (metadata, structure, links) |
| Practice | /practica, /practica/videos | yes (embeds, metadata) |
| Gallery (JS-rendered) | /galeria | yes (runtime, a11y, pagination) |
| Contact (form) | /contacto | yes (form inspection without submission) |
| Donations | /donaciones | yes (metadata, links) |
| Event listing | /eventos | yes (expired-event handling, calendar buttons) |
| Event detail | 2 pages | yes (JSON-LD, calendar dialog runtime, .ics links) |
| Blog listing + article | /blog, /blog/sangha-refugio-hiperconexion | yes (metadata, JSON-LD) |
| Error | 404 | yes (status, noindex, headers) |

Viewports exercised: 1440×900 (desktop), 390×844 (mobile), 360×800 (small mobile). Forms inspected but never submitted (`form_submission_permission: false`).
