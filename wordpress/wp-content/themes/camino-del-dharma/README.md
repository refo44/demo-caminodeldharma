# camino-del-dharma

Block theme / Full Site Editing (ADR 0029). **Scaffold creado en WU-04** con TDD
(RED documentado antes del primer archivo): `style.css` (solo metadata),
`theme.json` v3 (baseline de tokens visuales — reproduce exactamente el `:root`
de `static/assets/css/main.css`; los tests de `tests/Unit/Theme_TokensTest.php`
comparan contra el estático extraído programáticamente), `templates/index.html`
(fallback técnico), `parts/header|footer.html` (placeholders), `functions.php`
(bootstrap: supports + encolado de `assets/css/main.css`).

Reglas:

- El theme ensambla; **no** registra CPTs, taxonomías ni dominio (ADR 0024).
- `theme.json` es la fuente de verdad de tokens; sin hex directos en CSS.
- No crear plantillas PHP clásicas (`front-page.php`, `page-*.php`…): hay un
  test que lo prohíbe (`Theme_ScaffoldTest`).
- Plantillas reales, patrones y CSS complementario llegan en WU-05+ con test
  en rojo antes. Guía: `docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038).
