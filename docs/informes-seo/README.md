# informes-seo/ — nota interna

**Este archivo no se entrega.** Es la nota de trabajo de la carpeta.

## Los tres entregables

| # | Documento | Cubre | Destinatario |
|---|---|---|---|
| 00 | [Informe de Auditoría SEO](00-informe-auditoria-seo.md) | General y ejecutivo: visibilidad, autoridad, comparación con otras comunidades, plan de acción y decisiones pendientes | Liderazgo de la comunidad |
| 02 | [Auditoría SEO Técnica](02-auditoria-seo-tecnica.md) | Estado de salud del sitio: indexación, velocidad, datos estructurados, seguridad, protocolo de medición | Equipo de publicación web |
| 24 | [Brief editorial](../24-brief-editorial-blog-y-visibilidad.md) | Qué escribir en el blog, con qué voz y en qué orden | Equipo editorial |

**No se solapan y no compiten.** El 00 responde *qué se encontró y qué hay que decidir*; el 02, *cómo está construido el sitio*; el 24, *qué escribir*. Cada uno es autosuficiente: ninguno depende de `.audit/`, de este README ni de los otros dos para leerse, y los tres se remiten entre sí donde corresponde.

La numeración conserva un hueco en el 01 (informe de rendimiento, ver abajo). No es un error de secuencia.

## Origen de los datos

`.audit/` — informes de auditoría, ledger de evidencia, hallazgos y datos crudos. Los entregables **no generan datos nuevos**: los redactan para audiencia externa, sin códigos de evidencia ni identificadores de tarea.

## Informe de rendimiento — pendiente hasta septiembre

No se emitió. Un informe de rendimiento reporta evolución de tráfico, conversiones y retorno de inversión, y hoy **ninguna de las tres es reportable**: no hay periodo anterior (el sitio se publicó el 2026-07-18), no hay analítica (decisión formalizada) y no hay transacciones (sitio no comercial).

Rellenarlo con estimaciones habría sido fabricar evidencia. El cuadro de indicadores que lo sustituye —incluidos asistentes a la meditación y contactos entrantes— está en el §9 del informe 00.

**Emitir tras la re-medición**, si para entonces hay datos que lo justifiquen.

## Corrección de fondo registrada

**Google Business Profile quedó descartado** tras confirmar que la comunidad no tiene sede ni dirección física y, por tanto, no es elegible según las directrices de Google (las entidades exclusivamente en línea quedan excluidas, y Google exige una dirección real verificable aunque se oculte al público).

Una versión preliminar lo situaba como acción prioritaria: fue una deducción a partir del SERP sin verificar el requisito. Documentado en el §7 del informe 00, con la consecuencia estratégica — las búsquedas locales dejan de contar como brecha y el foco pasa a autoridad y práctica en línea.

## Próxima emisión

**Entre el 17 de agosto y el 14 de septiembre de 2026.** Antes no: se estaría midiendo la eficacia de acciones que aún no han tenido tiempo de actuar.

1. Copiar a `NN-nombre-AAAA-MM.md` para conservar el histórico.
2. Actualizar por secciones, sin reescribir los documentos enteros.
3. Seguir el protocolo del §10 del informe 02 — misma herramienta, mismo método, misma lista de palabras clave.
4. Añadir columna de evolución a la tabla de posiciones (§9 del informe 00).
5. Evaluar si ya procede emitir el informe de rendimiento.

## Decisiones que condicionan los informes

- [ADR 0019](../adr/0019-sin-analitica-con-cookies.md) — sin analítica con cookies.
- [ADR 0020](../adr/0020-hsts-aplazado-hasta-wordpress.md) — HSTS aplazado hasta después de WordPress.
- ADR 0002 / 0018 — el sitio estático es temporal, previo a la migración a WordPress.
