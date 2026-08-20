# ADR 0032: Contrato de migración static → WordPress (cinco entregables)

## Estado

Aceptada

## Fecha

2026-08-19

## Contexto

La producción actual de Camino del Dharma es un **sitio estático** en Hostinger
(`https://caminodeldharma.org`). WordPress **no está iniciado** (Fase 3 pendiente). El recorte a CMS
está decidido (ADR 0012) y el theme será de bloques / Full Site Editing (ADR 0029), no un theme
clásico PHP.

La documentación previa (`docs/17-orden-implementacion.md` § Transición,
`docs/migracion-static-wordpress.md`, `docs/playbook-migracion-static-wordpress.md`) cubre
organización del monorepo, ledger de diferencias y aprendizajes de QA. No fijaba, como decisión
arquitectónica, qué hace que una migración se considere **completa**.

Ese vacío permite errores típicos: desplegar o activar el theme y asumir que las Pages existen;
tratar un template como si creara una ruta; perder CSS, JS o assets; conservar `get_permalink()`
correcto con incoming 404; mezclar fixtures con contenido editorial; dejar que un ZIP estático
sobrescriba el document root de WordPress; o dar por bueno un workflow/transferencia verde.

Este ADR no cambia ADR 0001, 0002, 0008, 0012, 0013, 0014, 0015 ni 0029. Los complementa con el
**contrato de aceptación** de la migración.

## Decisión

**Ruta de migración de este proyecto (única):**

```text
maqueta estática (HTML/CSS/JS en producción)
    →  WordPress block theme / Full Site Editing
```

No hay paso intermedio de theme clásico PHP (`front-page.php`, `page-*.php`, `get_header()`).
ADR 0029 ya lo decidió; el propietario lo confirma: Fase 3 construye FSE **directamente** desde la
maqueta. Un `functions.php` de bootstrap del block theme (encolar CSS/JS, theme supports) no es un
theme clásico y no constituye esa etapa.

Una migración static → WordPress de este proyecto tiene **cinco entregables independientes**.
Ninguno implica a los demás.

| Entregable | Qué cubre en este proyecto |
| ---------- | -------------------------- |
| **CONTENT** | Pages institucionales, posts del blog, CPT `event` (y `sangha` solo si se implementa más adelante), taxonomías, metadata, media, copy institucional desde `content-source/` |
| **PRESENTATION** | Theme de bloques (`templates/*.html`, `parts/*.html`, `patterns/`), CSS complementario, comportamiento responsive, estructura de accesibilidad |
| **ROUTING** | Slugs y permalinks alineados con ADR 0008 y `docs/11-arbol-urls-final.md`, archives/singles de CPT, taxonomías públicas, redirects legacy de `.htaccess`, 404, canonicales |
| **BEHAVIOR** | JavaScript real (`main.js`, y paridad o sustitución documentada de `gallery.js` / `share.js` / `calendar.js`), formulario de contacto (ADR 0026), menú, diálogos, calendario de `/eventos`, audio, descargas |
| **OPERATIONS** | Despliegue acotado, environments, backups, rollback, importadores, fixtures, política de indexación, QA con evidencia, ownership |

**Reglas:**

1. **La migración no está completa** porque el theme esté desplegado o activado.
2. **Un template no crea una Page.** `templates/page-comunidad.html` no implica que exista `/comunidad`
   ni un objeto Page en la base de datos. Debe existir también el contenido (ADR 0033).
3. **Una URL no está migrada** hasta que su fila en
   [`docs/matriz-migracion-static-wordpress.md`](../matriz-migracion-static-wordpress.md) tenga
   estrategia para contenido + presentación + routing + comportamiento + QA.
4. **Deploy success ≠ application success.** Un ZIP, File Manager, FTP o workflow verde solo prueba
   la operación que ejecutó (p. ej. transferencia). No demuestra navegación, routing, contenido en
   BD, templates, JS, formularios, SEO ni accesibilidad.
5. **La maqueta estática es el contrato visual y de comportamiento** (ADR 0001, ADR 0002) salvo
   diferencias ya registradas (p. ej. ADR 0021: `gallery.js` no se migra; lightbox nativo de
   Gutenberg). Perder diseño o JS en una URL que ya no es 404 **no** es migración correcta.
6. **Paridad de JS** exige comprobar que el script se carga, los selectores y el DOM esperado
   existen, los `data-*` y el estado ARIA coinciden, y los eventos funcionan. La mera presencia de
   `main.js` encolado no demuestra paridad.
7. **Las URLs públicas canónicas no llevan barra final** (ADR 0008). `get_permalink()` no basta:
   hay que probar la **ruta entrante** (HTTP) y el 404 real. WordPress no debe reintroducir barras
   finales ni `index.html` visibles.
8. **Tres operaciones distintas** (ADR 0013, ADR 0015):

   ```text
   STATIC DEPLOY  ≠  WORDPRESS CODE DEPLOY  ≠  WORDPRESS CONTENT
   ```

   Tras el corte, un flujo legacy de HTML estático no puede escribir sobre el document root de
   WordPress. El theme/plugin se despliega solo a sus directorios. No se despliegan `uploads/`,
   `wp-config.php`, core, base de datos ni plugins de terceros. `.htaccess` de la raíz del servidor
   requiere tratamiento explícito (WordPress suele reescribirlo).
9. **No hay buscador** (`docs/04-mapa-pantallas.md`). No inventar `/buscar/` ni SearchAction.
10. El detalle operativo vive en
    [`docs/contrato-migracion-static-wordpress.md`](../contrato-migracion-static-wordpress.md) y el
    checklist en [`docs/cutover-checklist-wordpress.md`](../cutover-checklist-wordpress.md).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Tratar el ledger `migracion-static-wordpress.md` como contrato de aceptación | Ese archivo registra diferencias día a día; no define completitud. |
| Considerar «theme activado en staging» como cierre de Fase 3 | Deja Pages, routing, JS y operaciones sin dueño. |
| Cinco ADRs separados (contrato, import, deploy, routing, FSE) | Import es ADR 0033. Deploy ya está en 0013/0015. Routing en 0008/0022/0030. FSE en 0029. Este ADR solo cubre el contrato de completitud. |
| Theme clásico PHP como etapa intermedia (`static → classic → FSE`) | Descartado. La ruta es **static → FSE** (ADR 0029, confirmado 2026-08-19). |

## Consecuencias

**Beneficios:**

- Criterio de aceptación único para agentes, revisores y el corte a producción.
- Impide dar por migrada una URL que solo dejó de ser 404.
- Separa evidencia de transferencia de evidencia de aplicación.

**Riesgos:**

- Más trabajo de QA y de matriz antes del corte; es deliberado.
- Docs numerados anteriores a este ADR mencionan plantillas PHP (estado histórico). La geografía
  vigente del theme es `docs/12-theme-file-structure.md` (ADR 0029).

**Trabajo futuro:**

- Completar la columna de estrategia de cada fila de la matriz al implementar Fase 3 (hoy: inventario
  y obligación; no implementación).
- No implementar importador, theme ni cutover en la sesión que crea este ADR.

## Referencias

- Contrato operativo: [`docs/contrato-migracion-static-wordpress.md`](../contrato-migracion-static-wordpress.md)
- Matriz: [`docs/matriz-migracion-static-wordpress.md`](../matriz-migracion-static-wordpress.md)
- Cutover: [`docs/cutover-checklist-wordpress.md`](../cutover-checklist-wordpress.md)
- ADR [0001](0001-maqueta-estatica-como-base-definitiva.md), [0002](0002-wordpress-como-adaptacion-sin-rediseno.md), [0008](0008-urls-estables-desde-la-maqueta.md)
- ADR [0012](0012-wordpress-como-motor-de-contenido.md), [0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md), [0015](0015-despliegue-manual-temporal.md)
- ADR [0029](0029-theme-bloques-full-site-editing.md), [0033](0033-importador-contenido-vs-fixtures.md)
- `docs/11-arbol-urls-final.md`, `docs/17-orden-implementacion.md` § Transición
