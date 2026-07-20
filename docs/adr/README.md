# Architecture Decision Records (ADR)

Registro de decisiones técnicas y estructurales del proyecto **Camino del Dharma**.

Los ADR capturan el **contexto**, la **decisión** y las **consecuencias** de elecciones que podrían cuestionarse o revertirse meses después sin recordar por qué se tomaron.

---

## Cuándo crear un ADR

Crear un ADR cuando una decisión:

- afecte la arquitectura, el despliegue, la seguridad o la estructura de contenido;
- implique elegir entre varias alternativas razonables;
- establezca una restricción que deba mantenerse en fases posteriores;
- pueda resultar difícil de entender sin conocer su contexto original.

---

## Estados

| Estado | Significado |
| ------ | ----------- |
| **Propuesta** | En discusión; aún no vincula al proyecto. |
| **Aceptada** | Decisión vigente; la implementación y la documentación deben alinearse con ella. |
| **Rechazada** | Alternativa descartada; se conserva como registro histórico. |
| **Sustituida** | Reemplazada por un ADR posterior; enlazar ambos. |
| **Obsoleta** | Ya no aplica al contexto actual; conservar por trazabilidad. |

**Regla de inmutabilidad:** un ADR **aceptado** no se edita para cambiar su sentido. Si la arquitectura cambia, se crea un **nuevo** ADR que sustituye al anterior y se actualiza el estado del ADR previo a **Sustituida**, con enlace bidireccional.

---

## Formato

Cada ADR es un archivo Markdown numerado:

```text
docs/adr/NNNN-titulo-en-kebab-case.md
```

Plantilla mínima:

```markdown
# ADR NNNN: Título de la decisión

## Estado

Aceptada | Propuesta | Rechazada | Sustituida | Obsoleta

## Fecha

YYYY-MM-DD

## Contexto

Qué problema existía, qué restricciones había y por qué era necesario decidir.

## Decisión

Qué se decidió exactamente.

## Alternativas consideradas

Qué otras opciones se evaluaron.

## Consecuencias

Beneficios, riesgos, limitaciones y trabajo futuro.

## Referencias

Documentos, issues, commits o ADR relacionados.
```

---

## Índice

| ADR | Título | Estado |
| --- | ------ | ------ |
| [0001](0001-maqueta-estatica-como-base-definitiva.md) | Maqueta estática como base definitiva | Aceptada |
| [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) | WordPress como adaptación sin rediseño | Aceptada |
| [0003](0003-eliminar-pwa-y-web-app-manifest.md) | Eliminar PWA y Web App Manifest | Aceptada |
| [0004](0004-git-como-fuente-unica-de-verdad.md) | Git como fuente única de verdad | Aceptada |
| [0005](0005-produccion-sin-edicion-manual.md) | Producción sin edición manual | Aceptada |
| [0006](0006-github-actions-para-despliegue.md) | GitHub Actions para CI/CD | Aceptada |
| [0007](0007-rsync-como-mecanismo-de-sincronizacion.md) | rsync como mecanismo de sincronización | Aceptada |
| [0008](0008-urls-estables-desde-la-maqueta.md) | URLs estables desde la maqueta | Aceptada |
| [0009](0009-css-y-tokens-invariantes-en-migracion.md) | CSS y tokens invariantes en la migración | Aceptada |
| [0010](0010-hsts-desactivado-hasta-auditoria.md) | HSTS desactivado hasta auditoría | Sustituida → [0018](0018-hsts-despliegue-escalonado.md) |
| [0011](0011-implementaciones-separadas-durante-migracion.md) | Implementaciones separadas durante migración | Sustituida → [0014](0014-monorepo-static-wordpress.md) |
| [0012](0012-wordpress-como-motor-de-contenido.md) | WordPress como motor de contenido | Aceptada |
| [0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md) | Fuentes de verdad duales y alcance del despliegue | Aceptada |
| [0014](0014-monorepo-static-wordpress.md) | Monorepo con carpeta static/ al iniciar Fase 3 | Aceptada |
| [0015](0015-despliegue-manual-temporal.md) | Despliegue manual temporal | Aceptada |
| [0016](0016-automatizacion-ci-cd-pospuesta.md) | Automatización CI/CD pospuesta | Aceptada |
| [0017](0017-repositorio-unico-durante-transicion.md) | Repositorio único durante la transición | Aceptada |
| [0018](0018-hsts-despliegue-escalonado.md) | HSTS — despliegue escalonado (transición → año) | Sustituida en lo operativo → [0020](0020-hsts-aplazado-hasta-wordpress.md) |
| [0019](0019-sin-analitica-con-cookies.md) | Sin analítica con cookies — GA4 descartado definitivamente | Aceptada |
| [0020](0020-hsts-aplazado-hasta-wordpress.md) | HSTS aplazado hasta después del corte a WordPress | Aceptada |

### Correspondencia con decisiones consolidadas

| Decisión consolidada | ADR |
| -------------------- | --- |
| WordPress como CMS | [0012](0012-wordpress-como-motor-de-contenido.md) |
| Maqueta estática como referencia definitiva | [0001](0001-maqueta-estatica-como-base-definitiva.md) |
| Convivencia temporal static/ + wordpress/ | [0014](0014-monorepo-static-wordpress.md), [0017](0017-repositorio-unico-durante-transicion.md) |
| Sin PWA ni manifest | [0003](0003-eliminar-pwa-y-web-app-manifest.md) |
| Git como fuente de verdad del código | [0004](0004-git-como-fuente-unica-de-verdad.md), [0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md) |
| Producción no se edita directamente | [0005](0005-produccion-sin-edicion-manual.md) |
| Despliegue manual temporal | [0015](0015-despliegue-manual-temporal.md) |
| CI/CD pospuesto | [0016](0016-automatizacion-ci-cd-pospuesta.md) (implementación de [0006](0006-github-actions-para-despliegue.md) diferida) |
| HSTS / transporte | [0010](0010-hsts-desactivado-hasta-auditoria.md) y [0018](0018-hsts-despliegue-escalonado.md) (históricos), [0020](0020-hsts-aplazado-hasta-wordpress.md) (vigente) |
| Privacidad / medición | [0019](0019-sin-analitica-con-cookies.md) — sin cookies de analítica; medición vía Search Console |

---

## Relación con otros documentos

- **`17-orden-implementacion`:** orden de fases y criterios de cierre; referencia este registro.
- **`docs/` numerados:** guías de implementación; deben respetar los ADR vigentes.
- **`CHANGELOG.md`:** historial de despliegues; no sustituye a los ADR.

La implementación y la documentación general del proyecto deben mantenerse alineadas con los ADR en estado **Aceptada**.
