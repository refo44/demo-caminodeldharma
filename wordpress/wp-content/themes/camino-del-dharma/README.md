# camino-del-dharma

Block theme / Full Site Editing (ADR 0029). Scaffold en WU-04; **vistas reales en
WU-07** (v0.2.0), siempre con RED documentado antes del primer archivo:

- `templates/` — 16 plantillas de bloques (docs/12 §5–§6): front-page, `page-*`
  institucionales, `page.html`, home/single del blog, archive/single de evento,
  ficha de autor, término de álbum, 404 e index (fallback).
- `parts/header|footer.html` → patterns PHP (`patterns/header|footer.php`) con el
  markup publicado y URLs generadas (`home_url()`, logo como asset del theme).
- `inc/blocks.php` — 11 bloques dinámicos `camino-del-dharma/*` (calendario de
  eventos con paridad byte a byte contra el grid publicado, listado
  vigentes/finalizados, destacado del Inicio, tipo/meta/CTA de evento, cabecera
  de entrada ADR 0037, listados de blog, ficha de autor, galería por álbum);
  markup en `inc/class-camino-del-dharma-renderers.php`, formato español puro en
  `inc/class-camino-del-dharma-format.php` (unit-testeado).
- `theme.json` v3 — tokens (paridad protegida por `Theme_TokensTest`), fontFace
  autohospedadas (Inter, Fjalla One, subset MarloweEscapade) y lightbox nativo.
- `assets/css/main.css` — porte completo del CSS estático consumiendo presets
  (`--wp--preset/custom/style--*`; sin `:root` propio). `assets/js/main.js`
  (nav, portado) y `calendar-tooltips.js` (encolado por el bloque calendario).

Reglas:

- El theme ensambla; **no** registra CPTs, taxonomías ni dominio (ADR 0024).
- `theme.json` es la fuente de verdad de tokens; sin hex directos en CSS.
- No crear plantillas PHP clásicas (`front-page.php`, `page-*.php`…): hay un
  test que lo prohíbe (`Theme_ScaffoldTest`).
- Plantillas reales, patrones y CSS complementario llegan en WU-05+ con test
  en rojo antes. Guía: `docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038).
