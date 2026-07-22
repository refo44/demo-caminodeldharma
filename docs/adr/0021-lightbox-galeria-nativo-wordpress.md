# ADR 0021: Lightbox de la galería — nativo de WordPress, no propio

## Estado

Aceptada

## Fecha

2026-07-21

## Contexto

La galería de la maqueta estática **no tiene lightbox**: `assets/js/gallery.js` construye el grid y
la paginación, y sus únicos manejadores de clic son los de paginación. No hay visor ampliado ni
manejador sobre las imágenes.

Hasta la v1.0.19 esa ausencia tenía un costo medible. El grid servía los **originales a tamaño
completo** —unos 2 MB por página de 12 imágenes— para teselas de ~285 px. Es el hallazgo
**PERF-001** de la auditoría: *"1000px gallery thumbnails at 159px"*.

La v1.0.20 lo corrige: el grid pasa a servir miniaturas dedicadas
(`assets/images/galeria/thumbs/`, `srcset` 300w/600w, ~216 KB por página en móvil) y **los 36
originales se conservan intactos**.

Al plantear si convenía implementar un lightbox propio en la maqueta estática, aparecieron tres
datos que decidían la cuestión:

1. **WordPress ya lo trae.** Desde WP 6.4 los bloques de imagen y galería incorporan lightbox
   nativo ("Ampliar al hacer clic"). La versión final lo obtiene sin escribir JavaScript.
2. **El tema no contempla este archivo.** `12-theme-file-structure` §111 solo lista `main.js` en
   `assets/js/` del tema; `gallery.js` no forma parte de la estructura prevista. El §231 del mismo
   documento es explícito: *"CSS/JS mínimos: main.css + main.js (si aplica), sin frameworks.
   Scripts […] sin lógica compleja."* Un visor propio contradice esa arquitectura.
3. **Hay un hallazgo abierto que lo precede.** **AEO-001** (tarea **TASK-0010**, estado real
   **NOT DONE**) registra que la galería es invisible sin JavaScript: `curl /galeria` devuelve un
   solo `<img>`. Añadir más funcionalidad dependiente de JS sobre un grid que ya falla sin JS
   agrava un hallazgo con impacto SEO.

## Decisión

**No se implementa lightbox propio en la maqueta estática.** El visor ampliado llega con
WordPress, mediante el lightbox nativo del bloque de galería de Gutenberg.

La arquitectura de imágenes de la galería queda fijada así, y **es la que el lightbox necesita**:

| Uso | Fuente | Tamaño |
|---|---|---|
| Grid (teselas) | `assets/images/galeria/thumbs/` | 300w / 600w, `srcset` |
| Visor ampliado (futuro) | `assets/images/galeria/` (originales) | Tamaño completo, bajo demanda |

Los originales **no se borran** en ninguna limpieza de assets: son el material del que se generan
las miniaturas y la fuente del futuro visor. Al migrar, se suben a la biblioteca de medios de
WordPress, que genera sus propios tamaños derivados.

## Alternativas consideradas

**Lightbox propio ahora, en la maqueta estática.** Viable y no caro: `<dialog>` nativo, reutilizando
el patrón accesible que ya existe en `share.js` (`.share-dialog`, ver `14-css-architecture` §4).
Descartada porque el código se desecharía en la migración —el tema no incluye `gallery.js`— y
porque agravaría AEO-001 antes de resolverlo.

**Plugin de lightbox en WordPress.** Descartada: el núcleo ya cubre la necesidad. Un plugin añade
superficie de mantenimiento y actualizaciones para algo que Gutenberg ya resuelve.

**Dejar el grid sirviendo los originales** a la espera del lightbox. Descartada: mantenía PERF-001
abierto y penalizaba a todos los visitantes para beneficiar una función que aún no existe.

## Consecuencias

**A favor**

- PERF-001 queda cerrado sin hipotecar la funcionalidad futura.
- Cero JavaScript propio que mantener para el visor.
- El lightbox de Gutenberg llega ya resuelto en accesibilidad (foco, `Esc`, ARIA) y con
  mantenimiento del núcleo de WordPress.

**Contrapartidas aceptadas**

- **Entre la v1.0.20 y el corte a WordPress no se pueden ver las imágenes ampliadas.** Antes
  tampoco se podía —no había visor—, pero ahora el grid además entrega miniaturas, así que la
  resolución accesible al visitante es menor. Se acepta por ser una etapa temporal.
- Los originales viajan en el paquete de despliegue sin que ninguna página los solicite. Es
  almacenamiento, no ancho de banda de los visitantes.

**Si aun así se decidiera implementarlo en la maqueta estática**, el orden es:

1. **Primero TASK-0010** (pre-render / `noscript`): deja una galería que no depende de JS.
2. **Después el visor**, como mejora progresiva sobre esa base — sin JS se ven las imágenes igual;
   con JS se amplían.

Hacerlo al revés aumenta la dependencia de JavaScript, que es justo lo que AEO-001 señala.

## Referencias

- Hallazgos **PERF-001** (RESOLVED, v1.0.20) y **AEO-001** / **TASK-0010** (NOT DONE) en `.audit/`
- `12-theme-file-structure` §111 y §231 (estructura de `assets/js` del tema; criterio de JS mínimo)
- `14-css-architecture` §2 y §4 (miniaturas; patrón `.share-dialog` como referencia de diálogo accesible)
- `15-assets-strategy` (estrategia de imágenes y derivados)
- `19-accesibilidad-estandares` (criterios que debería cumplir cualquier diálogo propio)
- `CHANGELOG.md` v1.0.20 (miniaturas del grid; corrección del diagnóstico previo sobre el visor)
- ADR [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) y [0012](0012-wordpress-como-motor-de-contenido.md)
