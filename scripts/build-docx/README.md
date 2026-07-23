# build-docx

Genera las versiones `.docx` —con la identidad visual de Camino del Dharma— de los
cuatro entregables que hoy viven como Markdown:

| Fuente Markdown | Salida `.docx` |
|---|---|
| `docs/informes-seo/00-informe-auditoria-seo.md` | `docs/informes-seo/00-informe-auditoria-seo.docx` |
| `docs/informes-seo/02-auditoria-seo-tecnica.md` | `docs/informes-seo/02-auditoria-seo-tecnica.docx` |
| `docs/24-brief-editorial-blog-y-visibilidad.md` | `docs/24-brief-editorial-blog-y-visibilidad.docx` |
| `docs/21-manual-voz-copywriting-editorial.md` | `docs/21-manual-voz-copywriting-editorial.docx` |

El Markdown es la fuente de verdad. Los `.docx` se **regeneran** desde él; no se
editan a mano, o el siguiente `npm run build:docx` sobrescribiría los cambios.

## Uso

```bash
npm run build:docx        # regenera los cuatro
npm run build:docx -- 24  # solo los cuya ruta contenga "24"
npm run check:docx        # valida los .docx contra su Markdown
```

El manual de voz se incorporó en julio de 2026: su `.docx` era la versión 1.0 (marzo)
y le faltaban dos secciones de §11 y una de §14 que el Markdown ya recogía en la 1.1.
La 1.0 sigue en el historial, en el commit `372a5e2`.

`build:docx` necesita `docx` (npm, en devDependencies). `check:docx` solo usa la
librería estándar de Python 3.

## Qué comprueba `check:docx`

Sin LibreOffice no hay render paginado, así que la validación es estructural:

- todas las partes XML del `.docx` parsean;
- **cobertura de texto:** cada línea visible del Markdown aparece en el documento
  (no se perdió contenido al convertir);
- los anchos de columna suman el ancho declarado de cada tabla y toda fila tiene
  tantas celdas como columnas;
- cada hipervínculo tiene su relación correspondiente.

Conviene además abrir un `.docx` en Word y **actualizar el índice** (clic derecho
sobre él → «Actualizar campos»): el TOC es un campo real que solo se rellena al
abrirlo.

## Archivos

| Archivo | Rol |
|---|---|
| `brand.js` | Paleta (los 4 colores del manual), tipografías, estilos y numeración. Fuente: `docs/02-identidad-corporativa.md`. |
| `md.js` | Conversor Markdown → docx acotado a la sintaxis que usan los tres documentos. |
| `build.js` | Portada, ficha de identificación, índice y ensamblado de cada documento. |
| `check.py` | Validación estructural descrita arriba. |

## Notas de diseño

- **Estructura y estilo** siguen el `docs/21-manual-voz-copywriting-editorial.docx`
  como referencia: A4 con sus márgenes, portada con logo + nombre, ficha de datos,
  índice y pie con número de página.
- **El logo** se toma de la variante oficial sobre fondo de marca
  (`content-source/.../logo-camino-del-dharma-fondo-marca-oscuro.png`). El logo suelto
  es un trazo blanco sobre transparencia: sobre página blanca resulta invisible, y el
  manual de identidad prohíbe recolorearlo.
