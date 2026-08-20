# ADR 0029: Theme de bloques (Full Site Editing) en vez de PHP clásico con CSS congelado

## Estado

Aceptada

## Fecha

2026-08-01

## Contexto

ADR 0009 fijó, para la migración a WordPress, una arquitectura CSS invariante: un único
`assets/css/main.css` encolado desde el theme, tokens de marca (`--brand-*`) fijos en `:root`, y
`theme.json` limitado a alinear paleta y tipografías del editor de bloques sin sustituir `main.css`.
`docs/12-theme-file-structure.md` (§9, versión previa a este ADR) y el master prompt de
implementación de Fase 3 concretaron esa decisión como un **theme clásico**: plantillas PHP
(`front-page.php`, `page-*.php`, `single-*.php`, `archive-*.php`) con `theme.json` reducido a tokens
del editor, explícitamente **sin** Full Site Editing ("Do not convert to a full block theme... without
a new accepted ADR").

El propietario del proyecto ha revisado esa decisión: quiere que el theme se pueda modificar desde el
propio WordPress (paleta, tipografía, espaciado) sin depender de un despliegue de código cada vez que
haga falta un ajuste de presentación, y específicamente que el CSS no quede "quemado" en un archivo
que solo un desarrollador puede tocar.

Al momento de esta decisión **no existe código de theme construido todavía** (Fase 3 no ha empezado a
implementarse; el repositorio solo contiene la maqueta estática y los documentos de planificación). El
costo de cambiar de arquitectura ahora es bajo; sería alto si se tomara después de construir 13
plantillas PHP.

## Decisión

El theme `camino-del-dharma` se construye como **theme de bloques (block theme / Full Site Editing)**
de WordPress, no como theme clásico PHP.

1. **`theme.json`** (v2/v3) es la fuente de verdad de los tokens de diseño (paleta, tipografía,
   espaciado, anchos de layout) — deja de ser un complemento decorativo del editor y pasa a **generar**
   el CSS de esos tokens (`--wp--preset--*`) y a alimentar el panel **Estilos** del Editor de sitio.
   Los valores iniciales replican exactamente `02-identidad-corporativa.md` y los tokens actuales de
   `assets/css/main.css`, para no romper la paridad visual con la maqueta (ADR 0001, ADR 0002).
2. **Plantillas y partes como bloques**, no PHP: `templates/*.html` (front-page, page-comunidad,
   page-linaje, page-practica, page-eventos/archive-event, single-event, page-contacto, page-galeria,
   page-donaciones, page, 404, index) y `parts/*.html` (header, footer, navigation) sustituyen a los
   equivalentes `.php` de `docs/12-theme-file-structure.md` §1 y §5 (versión previa). El contenido
   editorial de cada página sigue viniendo de `content-source/` a través del bloque **Contenido de la
   entrada** (`<!-- wp:post-content /-->`), equivalente en bloques de `the_content()` — no cambia de
   dónde sale el copy, solo el mecanismo de plantilla.
3. **Sigue existiendo una hoja de estilos complementaria** (p. ej. `assets/css/main.css` o
   `style.css` cargado como estilo del theme) para todo lo que Global Styles no expresa: layout de
   componentes, ritmo de lectura, breakpoints, estados de foco, reglas de accesibilidad. Esa hoja
   **no** es editable desde wp-admin — igual que en cualquier theme de bloques de WordPress (Twenty
   Twenty-Four, etc.), Global Styles cubre tokens, no lógica de layout. Sigue pasando `npm run
   lint:css` y sigue prohibido `!important`, frameworks CSS e inline styles (se mantiene lo sustantivo
   de ADR 0009, solo cambia qué parte es editable).
4. **`camino-del-dharma-core` no cambia** (ADR 0024 sigue vigente): el plugin sigue siendo dueño de
   CPTs, taxonomías y roles; el theme sigue sin registrar dominio.
5. **Edición de Global Styles restringida a Administrador** — la capacidad nativa `edit_theme_options`
   ya es exclusiva de ese rol por defecto en WordPress; no se amplía a Editor ni a otros roles
   editoriales. Esto evita que un cambio de paleta o tipografía ocurra sin criterio de diseño.
6. **`static/` no se toca.** Este ADR aplica solo a la implementación WordPress de Fase 3. La maqueta
   estática sigue gobernada íntegramente por ADR 0001 y por la arquitectura de `docs/14-css-architecture.md`
   (un solo `main.css`, Stylelint, sin theme.json). Ambas implementaciones dejan de compartir literalmente
   el mismo mecanismo de CSS; ADR 0014 ya asumía implementaciones separadas durante la migración.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Mantener ADR 0009 (theme clásico, CSS congelado) | Es exactamente lo que el propietario pidió cambiar: nadie puede ajustar presentación sin un desarrollador y un despliegue. |
| Theme clásico + caja "CSS adicional" del Customizer | Campo de texto libre sin estructura; no da tokens editables, solo un parche más de CSS que se acumula y diverge del sistema de diseño. Mayor riesgo de romper accesibilidad/contraste sin revisión. |
| Panel de ajustes propio en `camino-del-dharma-core` para paleta/tipografía | Reinventa lo que el Editor de sitio ya ofrece de forma nativa; contradice el orden de preferencia de ADR 0025 (APIs nativas de WordPress antes que código propio). |
| Page builder de terceros (Elementor, Divi) para edición visual | Vetado por defecto en ADR 0025; no se reabre esa política — el Editor de sitio es núcleo de WordPress, no un plugin de terceros. |

## Consecuencias

**Beneficios:**

- El propietario (u otro Administrador) puede ajustar paleta, tipografía y espaciado desde
  **Apariencia → Editor → Estilos** sin depender de un despliegue de código.
- El theme sigue siendo "solo presentación" (ADR 0024): ahora esa presentación es configurable desde
  WordPress, que es literalmente el rol que se le pidió al CMS.
- Usa exclusivamente mecanismos núcleo de WordPress (Site Editor), sin añadir plugins ni builders de
  terceros — coherente con ADR 0025.
- El costo de este cambio es bajo porque no existe código de theme construido todavía.

**Riesgos:**

- Reescribe la arquitectura de archivos documentada en `docs/12-theme-file-structure.md` (plantillas
  PHP → plantillas de bloques) y las secciones del master prompt de Fase 3 que asumían theme clásico;
  ambos se actualizan junto con este ADR.
- Global Styles permite cambios post-lanzamiento; hace falta una captura de referencia del `theme.json`
  inicial como línea base de paridad visual para QA futura (ver Trabajo futuro).
- Requiere una versión de WordPress con soporte completo de theme de bloques (WP 6.x); ya es la
  versión objetivo del entorno Docker de ADR 0023, así que no añade una dependencia nueva, pero debe
  verificarse explícitamente al construir el theme.
- Los bloques reutilizables actuales (`parts/meditation-block.php`, etc.) deben resolverse como
  template parts o patrones sincronizados de bloques, no como includes PHP.

**Trabajo futuro:**

- Actualizar `docs/12-theme-file-structure.md` (este ADR ya lo hace en la misma sesión).
- Al construir el theme, guardar el `theme.json` inicial como línea base y documentar el procedimiento
  de comparación visual antes/después de cualquier cambio de Global Styles en producción.
- Decidir, al implementar, si algún bloque reutilizable necesita un patrón sincronizado en vez de un
  template part.

## Nota (2026-08-19)

El propietario confirma la ruta de Fase 3: **maqueta estática → FSE**, sin construir un theme
clásico PHP como puente. Eso es exactamente esta decisión; no la altera. No implementar
`front-page.php` / `page-*.php` «para migrar después a bloques».

## Referencias

- ADR [0009](0009-css-y-tokens-invariantes-en-migracion.md) — sustituido por este ADR para la
  implementación WordPress; sigue vigente sin cambios para `static/`.
- ADR [0001](0001-maqueta-estatica-como-base-definitiva.md), ADR
  [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) — sin rediseño: la paridad visual inicial no
  cambia, solo el mecanismo de edición posterior.
- ADR [0024](0024-plugin-dominio-theme-presentacion.md) — el theme sigue sin registrar dominio.
- ADR [0025](0025-politica-plugins-terceros.md) — el Editor de sitio es núcleo de WordPress (preferencia
  1 de esa decisión), no un page builder de terceros; los vetados (Elementor, Divi) siguen vetados.
- ADR [0014](0014-monorepo-static-wordpress.md) — implementaciones estático/WordPress ya separadas.
- `docs/12-theme-file-structure.md`, `docs/14-css-architecture.md`, `docs/02-identidad-corporativa.md`
- `docs/FABLE5-Fase3-WordPress-Master-Prompt-v1.md` §7.2, §9
