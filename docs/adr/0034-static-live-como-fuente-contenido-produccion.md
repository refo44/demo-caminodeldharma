# ADR 0034: El sitio estático live es fuente de contenido de producción hasta el corte

## Estado

Aceptada

## Fecha

2026-08-19

## Contexto

`https://caminodeldharma.org` ya recibe visitas. El artefacto estático en la raíz de este repositorio
**es el sitio de producción** (ADR 0001, ADR 0015), no un prototipo desechable.

Parte del contenido que en WordPress será dinámico (eventos, entradas de blog, álbumes de galería,
fechas, carteles, JSON de imágenes, atribuciones) está **hardcodeado** en HTML, JSON embebido y
atributos `data-*`. Para esos tipos, el HTML publicado es a menudo la **única** representación
completa. `content-source/` cubre copy institucional; no sustituye el listado vivo de `/eventos` ni
`#gallery-data`.

ADR 0033 estableció que el HTML no debe convertirse en una segunda redacción institucional frente a
`content-source/`. Sin esta decisión complementaria, un agente podría:

- tratar cards/JSON como demo y no importarlos;
- reescribir a mano lo que se puede extraer;
- usar fixtures en lugar del histórico;
- dar por cerrada la migración porque el block theme «se ve bien».

Este ADR no implementa extractores ni importadores.

## Decisión

### 1. Hasta el corte, el estático live es una base de contenido de producción

```text
Until migration is complete, the live static repository is a production content source.
```

- No eliminar contenido del repo antes de extraerlo y verificarlo.
- Hardcoded ≠ dummy. No descartar por «parecer maqueta».
- No recrear a mano lo que se puede parsear de forma determinista.
- No sustituir histórico publicado por fixtures.

### 2. Clasificación obligatoria (cada ítem)

| Clase | Significado |
| ----- | ----------- |
| **REAL PRODUCTION CONTENT** | Publicado o enlazado para visitantes; debe sobrevivir a la migración |
| **HISTORICAL CONTENT** | Pasado (eventos finalizados, álbumes de años); sigue siendo producción |
| **STRUCTURAL COPY** | Microcopy de UI, nav, labels (docs 07/09); no es una entidad CMS |
| **DESIGN / DEMO** | Solo si hay evidencia de que nunca se publicó (este repo **casi no** tiene demo) |
| **OBSOLETE** | Superado con redirect o 410 documentado |
| **UNCLEAR — OWNER REVIEW REQUIRED** | Diverge entre `content-source/`, HTML y disco; no elegir en silencio |

Inventario vigente: [`docs/inventario-contenido-produccion-static.md`](../inventario-contenido-produccion-static.md).

### 3. Fuente de verdad por tipo (CURRENT STATE)

No hay una jerarquía única para todo.

| Tipo | SOURCE OF TRUTH hoy | GENERATED / PRESENTATION |
| ---- | ------------------- | ------------------------ |
| Copy institucional (comunidad, linaje, práctica, footer, donaciones) | `content-source/` cuando existe; el HTML live es lo **publicado** — si divergen: UNCLEAR | HTML/CSS |
| Eventos (10 cards en `/eventos`, 3 singles) | **HTML live** (`eventos/index.html` y fichas) | CSS/JS de cards y calendario |
| Entradas de blog (2) | **HTML live** (`blog/{slug}/`) | listado/home |
| Galería (35 ítems JSON, 3 álbumes) | **JSON embebido** en `galeria/index.html` + archivos en `assets/images/galeria/` | `gallery.js` |
| Media en disco no referenciada | Disco (`assets/`); puede ser KEEP o UNCLEAR | — |
| CSS servido | Fuente `assets/css/main.css` | `main.min.css` (generado) |
| URLs públicas | `sitemap.xml` + ADR 0008 + `.htaccess` | — |

Tras el corte: contenido editorial → WordPress (ADR 0013). El theme FSE es presentación, no almacén.

### 4. Extracción programática (cuando se implemente)

Preferir parseo read-only:

```text
repo estático (HTML / JSON embebido / data-* / assets)
  → extractor
  → payload versionado revisable
  → validate / dry-run
  → importador WP-CLI (ADR 0033)
```

No reescribir entidades a mano si el extractor puede obtenerlas. El payload no se importa a ciegas:
revisión humana + conteos.

### 5. Reconciliación de conteos

La migración **no está completa** si los conteos no cuadran, salvo mismatch **explicado** en
[`docs/conteos-reconciliacion-migracion.md`](../conteos-reconciliacion-migracion.md).

Baseline (auditoría 2026-08-19, repo `VERSION` 1.0.34): ver ese documento.

### 6. Paralelo y delta

Mientras se construye WordPress en Docker/staging, **producción sigue siendo el estático**.

El estático **sigue cambiando** (`CHANGELOG.md`: v1.0.34 pendiente de ZIP mientras v1.0.33 está
desplegada). Estrategia por defecto de este proyecto (el propietario puede sustituirla):

- Durante el desarrollo: **ledger de cambios** (opción C) en `docs/migracion-static-wordpress.md`.
- Ventana corta de **content freeze** (opción A) inmediatamente antes del corte.
- Delta import (opción B) solo si el freeze no es viable; entonces el extractor se re-ejecuta y se
  reconcilian conteos otra vez.

### 7. Patterns FSE ≠ contenido

Los `patterns/` son estructura reutilizable. Las colecciones reales (eventos, posts, galería) viven
en la BD, no hardcodeadas en `templates/*.html` ni en patterns.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Tratar el estático solo como contrato visual y reescribir eventos/blog en wp-admin | Pierde histórico, alt text, fechas y URLs; no es determinista. |
| Usar solo `content-source/` e ignorar HTML live | `content-source/` no contiene el archivo de eventos ni `#gallery-data`. |
| Congelar el estático el día uno de Fase 3 | Contradice el CHANGELOG: producción sigue parcheándose. |
| Dar la migración por cerrada cuando el theme FSE renderiza | ADR 0032: cinco entregables; este ADR añade conteos y extracción. |

## Consecuencias

**Beneficios:** el histórico publicado entra en WordPress; hay un dueño para mismatches.

**Riesgos:** HTML y `content-source/` pueden divergir (copy institucional). Esos casos son UNCLEAR,
no se resuelven en el extractor.

**Trabajo futuro:** extractores e importador en Fase 3. No en la sesión de este ADR.

## Referencias

- ADR [0001](0001-maqueta-estatica-como-base-definitiva.md), [0008](0008-urls-estables-desde-la-maqueta.md), [0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md)
- ADR [0032](0032-contrato-migracion-static-wordpress.md), [0033](0033-importador-contenido-vs-fixtures.md)
- [`docs/inventario-contenido-produccion-static.md`](../inventario-contenido-produccion-static.md)
- [`docs/conteos-reconciliacion-migracion.md`](../conteos-reconciliacion-migracion.md)
- [`docs/redirect-ledger.md`](../redirect-ledger.md)
